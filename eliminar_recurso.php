<?php
require_once 'includes/config.php';

// Verificar acceso al módulo
verifyModuleAccess(basename($_SERVER['PHP_SELF']));

// Validar ID
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "ID de recurso no válido";
    header("Location: catalogo_libros.php");
    exit();
}

$id_libro = intval($_GET['id']);

// Obtener título para el mensaje
$stmt = $conn->prepare("SELECT titulo FROM libros WHERE id_libro = ?");
$stmt->bind_param("i", $id_libro);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "El recurso no existe";
    header("Location: catalogo_libros.php");
    exit();
}

$titulo = $result->fetch_assoc()['titulo'];

// Eliminar (versión simple)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    $stmt = $conn->prepare("DELETE FROM libros WHERE id_libro = ?");
    $stmt->bind_param("i", $id_libro);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Recurso '".htmlspecialchars($titulo)."' eliminado exitosamente";
    } else {
        $_SESSION['error_message'] = "Error al eliminar: ".$conn->error;
    }
    
    header("Location: catalogo_libros.php");
    exit();
}

// Mostrar confirmación (igual que antes)
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Eliminación - Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        function cancelarEliminacion() {
            window.location.href = 'catalogo_libros.php';
        }

        function confirmarEliminacion(id, titulo) {
            if (confirm(`¿Está seguro que desea eliminar el recurso "${titulo}"?\nEsta acción eliminará también todos sus ejemplares asociados y no se puede deshacer.`)) {
                window.location.href = 'eliminar_recurso.php?id=' + id;
            }
            return false;
        }
        
    </script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i> Confirmar Eliminación
                </h2>
                
                <div class="mb-6">
                    <p class="text-gray-700 mb-2">¿Está seguro que desea eliminar el siguiente recurso permanentemente?</p>
                    <div class="bg-gray-50 p-4 rounded border border-gray-200">
                        <p class="text-sm text-gray-600">si este recurso tiene ejemplares asociados también serán eliminados.</p>
                    </div>
                    <p class="text-red-600 mt-4 text-sm"><i class="fas fa-exclamation-circle mr-1"></i> Esta acción no se puede deshacer.</p>
                </div>
                
                <form method="POST" class="flex justify-end space-x-4">
                    <button type="button" onclick="cancelarEliminacion()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </button>
                    <button type="submit" name="confirmar" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <i class="fas fa-trash-alt mr-2"></i> Confirmar Eliminación
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>