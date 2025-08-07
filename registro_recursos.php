<?php
require_once 'includes/config.php'; 
 
// Obtener el nombre del archivo actual
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

if (isset($_POST['registrar'])) {
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
            
            // Insertar el recurso usando consultas preparadas
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
    <title>Registro de Recursos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h1 class="text-2xl font-bold text-center mb-6 text-purple-700">
                    <i class="fas fa-book-medical mr-2"></i>Registro de Nuevos Recursos
                </h1>
                
                <?php if (!empty($errors['general'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p><?= htmlspecialchars($errors['general']) ?></p>
                    </div>
                <?php endif; ?>
                
                <form action="registro_recursos.php" method="POST" class="space-y-4">
                    <!-- Tipo de Recurso -->
                    <div>
                        <label for="tipo_recurso" class="block text-sm font-medium text-gray-700 mb-1">
                            Tipo de Recurso <span class="text-red-500">*</span>
                        </label>
                        <select name="tipo_recurso" id="tipo_recurso" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 <?= !empty($errors['tipo_recurso']) ? 'border-red-500' : '' ?>">
                            <option value="">Seleccione un tipo</option>
                            <option value="Libro" <?= $formData['tipo_recurso'] === 'Libro' ? 'selected' : '' ?>>Libro</option>
                            <option value="Tesis/Proyecto" <?= $formData['tipo_recurso'] === 'Tesis/Proyecto' ? 'selected' : '' ?>>Tesis/Proyecto</option>
                            <option value="Revista" <?= $formData['tipo_recurso'] === 'Revista' ? 'selected' : '' ?>>Revista</option>
                            <option value="Equipo" <?= $formData['tipo_recurso'] === 'Equipo' ? 'selected' : '' ?>>Equipo</option>
                        </select>
                        <?php if (!empty($errors['tipo_recurso'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['tipo_recurso']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Título -->
                    <div>
                        <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                            Título <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="titulo" id="titulo" required 
                            value="<?= htmlspecialchars($formData['titulo']) ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 <?= !empty($errors['titulo']) ? 'border-red-500' : '' ?>">
                        <?php if (!empty($errors['titulo'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['titulo']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Autor y Año -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="autor" class="block text-sm font-medium text-gray-700 mb-1">Autor</label>
                            <input type="text" name="autor" id="autor" 
                                value="<?= htmlspecialchars($formData['autor']) ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        </div>
                        <div>
                            <label for="año_edicion" class="block text-sm font-medium text-gray-700 mb-1">Año de Edición</label>
                            <input type="number" name="año_edicion" id="año_edicion" min="1900" max="<?= date('Y') + 1 ?>"
                                value="<?= $formData['año_edicion'] ? htmlspecialchars($formData['año_edicion']) : '' ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        </div>
                    </div>
                    
                    <!-- País y Descripción -->
                    <div>
                        <label for="pais" class="block text-sm font-medium text-gray-700 mb-1">País</label>
                        <input type="text" name="pais" id="pais" 
                            value="<?= htmlspecialchars($formData['pais']) ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    
                    <div>
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"><?= htmlspecialchars($formData['descripcion']) ?></textarea>
                    </div>
                    
                    <!-- Número de Inventario y Signatura -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="n_inventario" class="block text-sm font-medium text-gray-700 mb-1">
                                Nº de Inventario Base <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="n_inventario" id="n_inventario" required
                                value="<?= htmlspecialchars($formData['n_inventario']) ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 <?= !empty($errors['n_inventario']) ? 'border-red-500' : '' ?>">
                            <?php if (!empty($errors['n_inventario'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['n_inventario']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="sig_topog" class="block text-sm font-medium text-gray-700 mb-1">Signatura Topográfica</label>
                            <input type="text" name="sig_topog" id="sig_topog"
                                value="<?= htmlspecialchars($formData['sig_topog']) ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        </div>
                    </div>
                    
                    <!-- Número de Ejemplares -->
                    <div>
                        <label for="ejemplar" class="block text-sm font-medium text-gray-700 mb-1">
                            Número de Ejemplares <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="ejemplar" id="ejemplar" min="1" max="100" required
                            value="<?= $formData['ejemplar'] ? htmlspecialchars($formData['ejemplar']) : 1 ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 <?= !empty($errors['ejemplar']) ? 'border-red-500' : '' ?>">
                        <?php if (!empty($errors['ejemplar'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['ejemplar']) ?></p>
                        <?php endif; ?>
                        <p class="mt-1 text-sm text-gray-500">Se generarán números de inventario consecutivos (ej. INV-001-1, INV-001-2, etc.)</p>
                    </div>
                    
                    <!-- Botón de envío -->
                    <div class="pt-4">
                        <button type="submit" name="registrar" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i> Registrar Recurso
                        </button>
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