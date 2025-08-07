<?php
require_once 'includes/config.php';


// Verificar si se recibió un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: catalogo_libros.php');
    exit();
}

$id_libro = intval($_GET['id']);

// Obtener los datos del recurso
$sql = "SELECT libros.*, ejemplares.* 
        FROM libros 
        JOIN ejemplares ON libros.id_libro = ejemplares.id_libro
        WHERE libros.id_libro = $id_libro";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    header('Location: catalogo_libros.php');
    exit();
}

$recurso = $result->fetch_assoc();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar los datos
    $titulo = $conn->real_escape_string(trim($_POST['titulo']));
    $autor = $conn->real_escape_string(trim($_POST['autor']));
    $año_edicion = $conn->real_escape_string(trim($_POST['año_edicion']));
    $pais = $conn->real_escape_string(trim($_POST['pais']));
    $tipo_recurso = $conn->real_escape_string(trim($_POST['tipo_recurso']));
    $n_inventario = $conn->real_escape_string(trim($_POST['n_inventario']));
    $sig_topog = $conn->real_escape_string(trim($_POST['sig_topog']));
    $estado = $conn->real_escape_string(trim($_POST['estado']));

    // Actualizar en la base de datos
    $conn->begin_transaction();
    
    try {
        // Actualizar tabla libros
        $sql_libros = "UPDATE libros SET 
                      titulo = '$titulo',
                      autor = '$autor',
                      año_edicion = '$año_edicion',
                      pais = '$pais',
                      tipo_recurso = '$tipo_recurso'
                      WHERE id_libro = $id_libro";
        
        // Actualizar tabla ejemplares
        $sql_ejemplares = "UPDATE ejemplares SET
                          n_inventario = '$n_inventario',
                          sig_topog = '$sig_topog',
                          estado = '$estado'
                          WHERE id_libro = $id_libro";
        
        $conn->query($sql_libros);
        $conn->query($sql_ejemplares);
        
        $conn->commit();
        $_SESSION['success_message'] = 'Recurso actualizado correctamente';
        header('Location: catalogo_libros.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = 'Error al actualizar el recurso: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Recurso - Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-book mr-2"></i> Editar Recurso
                </h1>
                <a href="catalogo_libros.php" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al catálogo
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md p-6">
                <form action="editar_recurso.php?id=<?= $id_libro ?>" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Información del Libro -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold border-b pb-2">
                                <i class="fas fa-book-open mr-2"></i> Información del Recurso
                            </h2>
                            
                            <div>
                                <label for="titulo" class="block text-sm font-medium text-gray-700">Título *</label>
                                <input type="text" id="titulo" name="titulo" required
                                       value="<?= htmlspecialchars($recurso['titulo']) ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="autor" class="block text-sm font-medium text-gray-700">Autor *</label>
                                <input type="text" id="autor" name="autor" required
                                       value="<?= htmlspecialchars($recurso['autor']) ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="año_edicion" class="block text-sm font-medium text-gray-700">Año de Edición</label>
                                <input type="number" id="año_edicion" name="año_edicion"
                                       value="<?= htmlspecialchars($recurso['año_edicion']) ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="pais" class="block text-sm font-medium text-gray-700">País</label>
                                <input type="text" id="pais" name="pais"
                                       value="<?= htmlspecialchars($recurso['pais']) ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                                
                            <div>
                                <label for="tipo_recurso" class="block text-sm font-medium text-gray-700">Tipo de Recurso *</label>
                                <select id="tipo_recurso" name="tipo_recurso" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Seleccione...</option>
                                    <option value="Libro" <?= $recurso['tipo_recurso'] === 'Libro' ? 'selected' : '' ?>>Libro</option>
                                    <option value="Tesis/Proyecto" <?= $recurso['tipo_recurso'] === 'Tesis/Proyecto' ? 'selected' : '' ?>>Tesis/Proyecto</option>
                                    <option value="Revista" <?= $recurso['tipo_recurso'] === 'Revista' ? 'selected' : '' ?>>Revista</option>
                                    <option value="Equipo de Cómputo" <?= $recurso['tipo_recurso'] === 'Equipo de Cómputo' ? 'selected' : '' ?>>Equipo de Cómputo</option>
                                    <option value="Otro" <?= $recurso['tipo_recurso'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Información del Ejemplar -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold border-b pb-2">
                                <i class="fas fa-barcode mr-2"></i> Información del Ejemplar
                            </h2>
                            
                            <div>
                                <label for="n_inventario" class="block text-sm font-medium text-gray-700">Número de Inventario *</label>
                                <input type="text" id="n_inventario" name="n_inventario" required
                                       value="<?= htmlspecialchars($recurso['n_inventario']) ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="sig_topog" class="block text-sm font-medium text-gray-700">Signatura Topográfica</label>
                                <input type="text" id="sig_topog" name="sig_topog"
                                       value="<?= htmlspecialchars($recurso['sig_topog']) ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700">Estado *</label>
                                <select id="estado" name="estado" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="disponible" <?= $recurso['estado'] === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                                    <option value="prestado" <?= $recurso['estado'] === 'prestado' ? 'selected' : '' ?>>Prestado</option>
                                    <option value="dañado" <?= $recurso['estado'] === 'dañado' ? 'selected' : '' ?>>Dañado</option>
                                    <option value="perdido" <?= $recurso['estado'] === 'perdido' ? 'selected' : '' ?>>Perdido</option>
                                </select>
                            </div>
                            
                            <div class="pt-6">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</body>
<?php include('includes/footer.php'); ?>
</html>