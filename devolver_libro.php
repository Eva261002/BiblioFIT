<?php
include('includes/db.php');

// Establecer la zona horaria correcta
date_default_timezone_set('America/La_Paz'); // Cambia esto por la zona horaria que necesites

// **Solo para desarrollo:** Mostrar errores detallados (Elimina o comenta estas líneas en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si se ha recibido 'id_prestamo' y 'accion'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_prestamo']) && $_POST['accion'] === 'devolver') {
    $id_prestamo = intval($_POST['id_prestamo']);

    // Inicializar variables de declaración
    $stmt_prestamo = null;
    $stmt_devolucion = null;
    $stmt_ejemplares = null;
    $stmt_estado = null;
    $stmt_adjust = null;
    $stmt_libro = null;

    // Inicia una transacción
    $conn->begin_transaction();
    try {
        // 1. Obtener el préstamo activo
        $stmt_prestamo = $conn->prepare("SELECT id_libro FROM prestamo WHERE id_prestamo = ? AND fecha_devolucion IS NULL");
        if (!$stmt_prestamo) {
            throw new Exception('Error al preparar la consulta de préstamo: ' . $conn->error);
        }
        $stmt_prestamo->bind_param("i", $id_prestamo);
        $stmt_prestamo->execute();
        $result_prestamo = $stmt_prestamo->get_result();

        if ($result_prestamo->num_rows === 0) {
            throw new Exception('Préstamo no encontrado o ya devuelto.');
        }

        $prestamo = $result_prestamo->fetch_assoc();
        $id_libro = intval($prestamo['id_libro']);

        // 2. Marcar la fecha de devolución y cambiar el estado del préstamo
        $fecha_devolucion = date('Y-m-d H:i:s');
        $stmt_devolucion = $conn->prepare("UPDATE prestamo SET fecha_devolucion = ?, estado = 'disponible' WHERE id_prestamo = ? AND fecha_devolucion IS NULL");
        if (!$stmt_devolucion) {
            throw new Exception('Error al preparar la actualización de devolución: ' . $conn->error);
        }
        $stmt_devolucion->bind_param("si", $fecha_devolucion, $id_prestamo);
        $stmt_devolucion->execute();

        if ($stmt_devolucion->affected_rows === 0) {
            throw new Exception('No se pudo actualizar la devolución. Asegúrate de que el préstamo no haya sido devuelto anteriormente.');
        }

        // 3. Incrementar el número de ejemplares disponibles
        $stmt_ejemplares = $conn->prepare("UPDATE libros SET ejemplar = ejemplar + 1 WHERE id_libro = ?");
        if (!$stmt_ejemplares) {
            throw new Exception('Error al preparar la actualización de ejemplares: ' . $conn->error);
        }
        $stmt_ejemplares->bind_param("i", $id_libro);
        $stmt_ejemplares->execute();

        // 4. Obtener el nuevo número de ejemplares y total_ejemplar del libro
        $stmt_estado = $conn->prepare("SELECT ejemplar, ejemplar FROM libros WHERE id_libro = ?");
        if (!$stmt_estado) {
            throw new Exception('Error al preparar la consulta del estado del libro: ' . $conn->error);
        }
        $stmt_estado->bind_param("i", $id_libro);
        $stmt_estado->execute();
        $result_estado = $stmt_estado->get_result();

        if ($result_estado->num_rows === 0) {
            throw new Exception('Error al obtener el estado del libro.');
        }

        $estado = $result_estado->fetch_assoc();
        $ejemplar = intval($estado['ejemplar']);
        $ejemplar = intval($estado['ejemplar']);

        // 5. Asegurarse de que 'ejemplar' no exceda 'total_ejemplar'
        if ($ejemplar > $ejemplar) {
            // Ajustar 'ejemplar' al 'total_ejemplar'
            $ejemplar = $ejemplar;
            $stmt_adjust = $conn->prepare("UPDATE libros SET ejemplar = ? WHERE id_libro = ?");
            if (!$stmt_adjust) {
                throw new Exception('Error al preparar la actualización de ajuste de ejemplares: ' . $conn->error);
            }
            $stmt_adjust->bind_param("ii", $ejemplar, $id_libro);
            $stmt_adjust->execute();
        }

        // 6. Actualizar el estado del libro
        $nuevo_estado = ($ejemplar > 0) ? 'disponible' : 'prestado';
        $stmt_libro = $conn->prepare("UPDATE libros SET estado = ? WHERE id_libro = ?");
        if (!$stmt_libro) {
            throw new Exception('Error al preparar la actualización del estado del libro: ' . $conn->error);
        }
        $stmt_libro->bind_param("si", $nuevo_estado, $id_libro);
        $stmt_libro->execute();

        // Confirmar la transacción
        $conn->commit();
        echo 'success';
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollback();
        // **Durante el desarrollo**, puedes enviar el mensaje de error para depuración
        // **En producción**, deberías enviar un mensaje genérico y registrar el error
        echo 'error: ' . htmlspecialchars($e->getMessage());
    }

    // Cerrar los statements si están definidos y no son nulos
    if ($stmt_prestamo !== null) $stmt_prestamo->close();
    if ($stmt_devolucion !== null) $stmt_devolucion->close();
    if ($stmt_ejemplares !== null) $stmt_ejemplares->close();
    if ($stmt_estado !== null) $stmt_estado->close();
    if ($stmt_adjust !== null) $stmt_adjust->close();
    if ($stmt_libro !== null) $stmt_libro->close();
} else {
    echo 'error: Parámetros incorrectos.';
}

// Cerrar la conexión
$conn->close();
?>
