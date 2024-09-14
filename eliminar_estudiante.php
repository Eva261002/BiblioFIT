<?php
include('includes/db.php');

$ci = $_GET['ci'] ?? '';

if (!empty($ci)) {
    $sql = "DELETE FROM estudiantes WHERE ci = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $ci);
        
        if ($stmt->execute()) {
            header("Location: listar_estudiantes.php?message=Estudiante eliminado exitosamente");
        } else {
            echo "Error al eliminar estudiante: " . $conn->error;
        }
        
        $stmt->close();
    } else {
        echo "Error al preparar la declaración: " . $conn->error;
    }
}

$conn->close();
?>
