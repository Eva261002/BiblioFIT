<?php
include('includes/db.php');

date_default_timezone_set('America/La_Paz'); // Cambia esto por la zona horaria que necesites

if (isset($_POST['id_libro'])) {
    $id_libro = $_POST['id_libro'];

    // Inicia una transacción
    $conn->begin_transaction();
    try {
        // Verificar el número de préstamos activos
        $sql_check_prestamos = "SELECT COUNT(*) AS prestamos_activos FROM prestamo WHERE id_libro = $id_libro AND fecha_devolucion IS NULL";
        $result = $conn->query($sql_check_prestamos);
        $row = $result->fetch_assoc();

        if ($row['prestamos_activos'] > 0) {
            // Marcar la fecha de devolución y cambiar el estado del préstamo
            $fecha_devolucion = date('Y-m-d H:i:s');
            $sql_devolucion = "UPDATE prestamo SET fecha_devolucion = '$fecha_devolucion', estado = 'disponible' WHERE id_libro = $id_libro AND fecha_devolucion IS NULL LIMIT 1";
            $conn->query($sql_devolucion);

            // Incrementar el número de ejemplares disponibles
            $sql_update_ejemplares = "UPDATE libros SET ejemplar = ejemplar + 1 WHERE id_libro = $id_libro";
            $conn->query($sql_update_ejemplares);

            // Actualizar el estado del libro si es necesario
            $sql_libro = "UPDATE libros SET estado = 'disponible' WHERE id_libro = $id_libro";
            $conn->query($sql_libro);

            // Confirmar la transacción
            $conn->commit();
            echo 'success';
        } else {
            echo 'error'; // No hay préstamos activos
        }
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollback();
        echo 'error';
    }
}
?>
