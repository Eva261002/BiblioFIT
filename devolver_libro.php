<?php
include('includes/db.php');

date_default_timezone_set('America/La_Paz');

// Mostrar errores detallados solo durante el desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_prestamo']) && $_POST['accion'] === 'devolver') {
    $id_prestamo = intval($_POST['id_prestamo']);

    $conn->begin_transaction();
    try {
        // 1. Obtener el préstamo activo y el id_libro asociado
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

        // 2. Encontrar un ejemplar específico que está prestado para este préstamo
        $stmt_ejemplar = $conn->prepare("SELECT id_ejemplar FROM ejemplares WHERE id_libro = ? AND estado = 'prestado' LIMIT 1");
        if (!$stmt_ejemplar) {
            throw new Exception('Error al preparar la consulta de ejemplares: ' . $conn->error);
        }
        $stmt_ejemplar->bind_param("i", $id_libro);
        $stmt_ejemplar->execute();
        $result_ejemplar = $stmt_ejemplar->get_result();

        if ($result_ejemplar->num_rows === 0) {
            throw new Exception('No se encontró un ejemplar prestado para este libro.');
        }

        $ejemplar = $result_ejemplar->fetch_assoc();
        $id_ejemplar = intval($ejemplar['id_ejemplar']);

        // 3. Actualizar el estado del ejemplar a "disponible"
        $stmt_actualizar_ejemplar = $conn->prepare("UPDATE ejemplares SET estado = 'disponible' WHERE id_ejemplar = ?");
        if (!$stmt_actualizar_ejemplar) {
            throw new Exception('Error al preparar la actualización del ejemplar: ' . $conn->error);
        }
        $stmt_actualizar_ejemplar->bind_param("i", $id_ejemplar);
        $stmt_actualizar_ejemplar->execute();

        // 4. Marcar la fecha de devolución y actualizar el estado del préstamo a "devuelto"
        $fecha_devolucion = date('Y-m-d H:i:s');
        $stmt_devolucion = $conn->prepare("UPDATE prestamo SET fecha_devolucion = ?, estado = 'devuelto' WHERE id_prestamo = ?");
        if (!$stmt_devolucion) {
            throw new Exception('Error al preparar la actualización de devolución: ' . $conn->error);
        }
        $stmt_devolucion->bind_param("si", $fecha_devolucion, $id_prestamo);
        $stmt_devolucion->execute();

        // Confirmar la transacción
        $conn->commit();
        echo 'success';
    } catch (Exception $e) {
        $conn->rollback();
        echo 'error: ' . htmlspecialchars($e->getMessage());
    }

    // Cerrar los statements si están definidos
    if (isset($stmt_prestamo) && $stmt_prestamo !== null) $stmt_prestamo->close();
    if (isset($stmt_ejemplar) && $stmt_ejemplar !== null) $stmt_ejemplar->close();
    if (isset($stmt_actualizar_ejemplar) && $stmt_actualizar_ejemplar !== null) $stmt_actualizar_ejemplar->close();
    if (isset($stmt_devolucion) && $stmt_devolucion !== null) $stmt_devolucion->close();
} else {
    echo 'error: Parámetros incorrectos.';
}

// Cerrar la conexión
$conn->close();
?>
