<?php
include('includes/db.php');

// Establecer la zona horaria correcta
date_default_timezone_set('America/La_Paz'); // Cambia esto por la zona horaria que necesites

if (isset($_POST['id_libro'])) {
    $id_libro = $_POST['id_libro'];

    // Inicia una transacción
    $conn->begin_transaction();
    try {
        // Marcar la fecha de devolución y cambiar el estado del préstamo
        $fecha_devolucion = date('Y-m-d H:i:s');
        $sql_devolucion = "UPDATE prestamo SET fecha_devolucion = '$fecha_devolucion', estado = 'disponible' WHERE id_libro = $id_libro AND fecha_devolucion IS NULL";
        $conn->query($sql_devolucion);

        // Actualizar el estado del libro
        $sql_libro = "UPDATE libros SET estado = 'disponible' WHERE id_libro = $id_libro";
        $conn->query($sql_libro);

        // Confirmar la transacción
        $conn->commit();
        echo 'success';
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollback();
        echo 'error';
    }
}
?>
