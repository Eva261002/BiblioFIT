<?php
require_once 'includes/config.php';


// Verificar acceso al módulo
$current_module = basename($_SERVER['PHP_SELF']);
verifyModuleAccess($current_module);

// Inicializar variables para persistencia de datos
$formData = [
    'tipo_recurso' => '',
    'titulo' => '',
    'autor' => '',
    'año_edicion' => '',
    'pais' => '',
    'descripcion' => '',
    'n_inventario' => '',
    'sig_topog' => '',
    'ejemplar' => 1
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar datos
    $formData = [
        'tipo_recurso' => trim($_POST['tipo_recurso'] ?? ''),
        'titulo' => trim($_POST['titulo'] ?? ''),
        'autor' => trim($_POST['autor'] ?? ''),
        'año_edicion' => isset($_POST['año_edicion']) ? (int)$_POST['año_edicion'] : 0,
        'pais' => trim($_POST['pais'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'n_inventario' => trim($_POST['n_inventario'] ?? ''),
        'sig_topog' => trim($_POST['sig_topog'] ?? ''),
        'ejemplar' => isset($_POST['ejemplar']) ? (int)$_POST['ejemplar'] : 0
    ];

    // Validaciones
    if (empty($formData['tipo_recurso'])) {
        $errors['tipo_recurso'] = "Seleccione un tipo de recurso";
    }
    
    if (empty($formData['titulo'])) {
        $errors['titulo'] = "El título es obligatorio";
    } elseif (strlen($formData['titulo']) > 255) {
        $errors['titulo'] = "El título no puede exceder 255 caracteres";
    }
    
    if (empty($formData['n_inventario'])) {
        $errors['n_inventario'] = "El número de inventario es obligatorio";
    }
    
    if ($formData['ejemplar'] <= 0) {
        $errors['ejemplar'] = "Debe registrar al menos un ejemplar";
    }

    // Si no hay errores, procesar el formulario
    if (empty($errors)) {
        try {
            $conn->begin_transaction();
            
            // Insertar el recurso
            $stmt = $conn->prepare("INSERT INTO libros 
                (titulo, autor, año_edicion, pais, tipo_recurso, descripcion) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisss", 
                $formData['titulo'], 
                $formData['autor'],
                $formData['año_edicion'],
                $formData['pais'],
                $formData['tipo_recurso'],
                $formData['descripcion']);
            $stmt->execute();
            
            $id_libro = $conn->insert_id;
            
            // Insertar ejemplares
            $stmt = $conn->prepare("INSERT INTO ejemplares 
                (id_libro, n_inventario, sig_topog, estado) 
                VALUES (?, ?, ?, 'disponible')");
            
            for ($i = 1; $i <= $formData['ejemplar']; $i++) {
                $n_inventario_ejemplar = $formData['n_inventario'] . '-' . $i;
                $stmt->bind_param("iss", $id_libro, $n_inventario_ejemplar, $formData['sig_topog']);
                $stmt->execute();
            }
            
            $conn->commit();
            
            // Redirigir con mensaje de éxito
            $_SESSION['success_message'] = "Recurso registrado exitosamente con {$formData['ejemplar']} ejemplar(es)";
            header("Location: catalogo_libros.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $errors['general'] = "Error al registrar el recurso: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nuevo Recurso - Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-book-medical mr-2"></i> Registrar Nuevo Recurso
                </h1>
                <a href="catalogo_libros.php" class="text-blue-600 hover:text-blue-800 flex items-center">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al catálogo
                </a>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md p-6">
                <form action="registro_recursos.php" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Información del Libro -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold border-b pb-2">
                                <i class="fas fa-book-open mr-2"></i> Información del Recurso
                            </h2>
                            
                            <div>
                                <label for="tipo_recurso" class="block text-sm font-medium text-gray-700">Tipo de Recurso *</label>
                                <select id="tipo_recurso" name="tipo_recurso" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 <?= !empty($errors['tipo_recurso']) ? 'border-red-500' : '' ?>">
                                    <option value="">Seleccione...</option>
                                    <option value="Libro" <?= $formData['tipo_recurso'] === 'Libro' ? 'selected' : '' ?>>Libro</option>
                                    <option value="Tesis/Proyecto" <?= $formData['tipo_recurso'] === 'Tesis/Proyecto' ? 'selected' : '' ?>>Tesis/Proyecto</option>
                                    <option value="Revista" <?= $formData['tipo_recurso'] === 'Revista' ? 'selected' : '' ?>>Revista</option>
                                    <option value="Equipo de Cómputo" <?= $formData['tipo_recurso'] === 'Equipo de Cómputo' ? 'selected' : '' ?>>Equipo de Cómputo</option>
                                    <option value="Otro" <?= $formData['tipo_recurso'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
                                </select>
                                <?php if (!empty($errors['tipo_recurso'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['tipo_recurso']) ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <label for="titulo" class="block text-sm font-medium text-gray-700">Título *</label>
                                <input type="text" id="titulo" name="titulo" required
                                       value="<?= htmlspecialchars($formData['titulo']) ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 <?= !empty($errors['titulo']) ? 'border-red-500' : '' ?>">
                                <?php if (!empty($errors['titulo'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['titulo']) ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <label for="autor" class="block text-sm font-medium text-gray-700">Autor</label>
                                <input type="text" id="autor" name="autor"
                                       value="<?= htmlspecialchars($formData['autor']) ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="año_edicion" class="block text-sm font-medium text-gray-700">Año de Edición</label>
                                    <input type="number" id="año_edicion" name="año_edicion" min="1900" max="<?= date('Y') + 1 ?>"
                                           value="<?= $formData['año_edicion'] ? htmlspecialchars($formData['año_edicion']) : '' ?>" 
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="pais" class="block text-sm font-medium text-gray-700">País</label>
                                    <input type="text" id="pais" name="pais"
                                           value="<?= htmlspecialchars($formData['pais']) ?>" 
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            
                            <div>
                                <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="3"
                                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($formData['descripcion']) ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Información del Ejemplar -->
                        <div class="space-y-4">
                            <h2 class="text-lg font-semibold border-b pb-2">
                                <i class="fas fa-barcode mr-2"></i> Información del Ejemplar
                            </h2>
                            
                            <div>
                                <label for="n_inventario" class="block text-sm font-medium text-gray-700">Número de Inventario Base *</label>
                                <input type="text" id="n_inventario" name="n_inventario" required
                                       value="<?= htmlspecialchars($formData['n_inventario']) ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 <?= !empty($errors['n_inventario']) ? 'border-red-500' : '' ?>">
                                <?php if (!empty($errors['n_inventario'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['n_inventario']) ?></p>
                                <?php endif; ?>
                                <p class="mt-1 text-xs text-gray-500">Ejemplo: LIB-2023-001 (se agregará -1, -2, etc. para cada ejemplar)</p>
                            </div>
                            
                            <div>
                                <label for="sig_topog" class="block text-sm font-medium text-gray-700">Signatura Topográfica</label>
                                <input type="text" id="sig_topog" name="sig_topog"
                                       value="<?= htmlspecialchars($formData['sig_topog']) ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="ejemplar" class="block text-sm font-medium text-gray-700">Número de Ejemplares *</label>
                                <input type="number" id="ejemplar" name="ejemplar" min="1" max="100" required
                                       value="<?= $formData['ejemplar'] ? htmlspecialchars($formData['ejemplar']) : 1 ?>" 
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 <?= !empty($errors['ejemplar']) ? 'border-red-500' : '' ?>">
                                <?php if (!empty($errors['ejemplar'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['ejemplar']) ?></p>
                                <?php endif; ?>
                                <p class="mt-1 text-xs text-gray-500">Se crearán múltiples ejemplares con el mismo número base</p>
                            </div>
                            
                            <div class="pt-6">
                                <button type="submit" name="registrar" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <i class="fas fa-save mr-2"></i> Registrar Recurso
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Validación básica del lado del cliente
        document.querySelector('form').addEventListener('submit', function(e) {
            let valid = true;
            const requiredFields = ['tipo_recurso', 'titulo', 'n_inventario', 'ejemplar'];
            
            requiredFields.forEach(field => {
                const element = document.getElementById(field);
                if (!element.value.trim()) {
                    element.classList.add('border-red-500');
                    valid = false;
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Por favor complete todos los campos obligatorios.');
            }
        });
    </script>
</body>
</html>