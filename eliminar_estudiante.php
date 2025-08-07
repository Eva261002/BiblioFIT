<?php
require_once 'includes/config.php';
verifyModuleAccess(basename($_SERVER['PHP_SELF']));

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID de estudiante no válido";
    header("Location: listar_estudiantes.php");
    exit;
}

$idEstudiante = (int)$_GET['id'];

try {
    // Iniciar transacción
    $conn->begin_transaction();
    
    // 1. Primero eliminar registros relacionados en entradas_salidas
    $sqlDeleteEntradas = "DELETE FROM entradas_salidas WHERE id_estudiante = ?";
    $stmtEntradas = $conn->prepare($sqlDeleteEntradas);
    $stmtEntradas->bind_param("i", $idEstudiante);
    $stmtEntradas->execute();
    
    // 2. Luego eliminar al estudiante
    $sqlDeleteEstudiante = "DELETE FROM estudiantes WHERE id_estudiante = ?";
    $stmtEstudiante = $conn->prepare($sqlDeleteEstudiante);
    $stmtEstudiante->bind_param("i", $idEstudiante);
    $stmtEstudiante->execute();
    
    // Confirmar transacción
    $conn->commit();
    
    $_SESSION['success_message'] = "Estudiante y sus registros asociados eliminados correctamente";
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    $_SESSION['error_message'] = "Error al eliminar: " . $e->getMessage();
} finally {
    // Cerrar conexiones
    if (isset($stmtEntradas)) $stmtEntradas->close();
    if (isset($stmtEstudiante)) $stmtEstudiante->close();
    $conn->close();
    
    header("Location: listar_estudiantes.php");
    exit;
}
?>