<?php
require_once 'includes/config.php';

$current_module = basename($_SERVER['PHP_SELF']);
verifyModuleAccess($current_module);
$message = "";
$formData = [
    'ci' => '',
    'nombre' => '',
    'apellido_paterno' => '',
    'apellido_materno' => '',
    'ru' => '',
    'carrera' => '',
    'otra_carrera' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar y validar datos
    $formData = [
        'ci' => trim($_POST['ci'] ?? ''),
        'nombre' => trim($_POST['nombre'] ?? ''),
        'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
        'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
        'ru' => trim($_POST['ru'] ?? ''),
        'carrera' => trim($_POST['carrera'] ?? ''),
        'otra_carrera' => trim($_POST['otra_carrera'] ?? '')
    ];

    // Validaciones
    $errors = [];
    
    if (empty($formData['ci'])) {
        $errors['ci'] = "El CI es obligatorio";
    }
    
    if (empty($formData['nombre'])) {
        $errors['nombre'] = "El nombre es obligatorio";
    }
    
    if (empty($formData['apellido_paterno'])) {
        $errors['apellido_paterno'] = "El apellido paterno es obligatorio";
    }
    
    if (empty($formData['ru'])) {
        $errors['ru'] = "El RU es obligatorio";
    }
    
    if (empty($formData['carrera'])) {
        $errors['carrera'] = "Debe seleccionar una carrera";
    } elseif ($formData['carrera'] === 'Otra' && empty($formData['otra_carrera'])) {
        $errors['otra_carrera'] = "Debe especificar el nombre de la carrera";
    }

    // Si no hay errores, procesar el registro
    if (empty($errors)) {
        try {
            $carrera = ($formData['carrera'] === 'Otra') ? $formData['otra_carrera'] : $formData['carrera'];
            
            $sql = "INSERT INTO estudiantes (ci, nombre, apellido_paterno, apellido_materno, ru, carrera) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", 
                $formData['ci'],
                $formData['nombre'],
                $formData['apellido_paterno'],
                $formData['apellido_materno'],
                $formData['ru'],
                $carrera
            );
            
            if ($stmt->execute()) {
                header("Location: listar_estudiantes.php?success=true");
                exit();
            } else {
                $message = "Error al registrar estudiante: " . $conn->error;
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .input-error {
            border-color: #ef4444 !important;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <!-- Encabezado -->
        <div class="text-center mb-10">
            <h2 class="text-4xl font-extrabold text-gray-800">
                <i class="fas fa-user-graduate mr-2"></i>Registrar Estudiante
            </h2>
               <a href="listar_estudiantes.php" class="text-blue-600 hover:text-blue-800 flex items-center justify-center mt-4">
                    <i class="fas fa-arrow-left mr-2"></i> Volver 
                </a>
            <p class="text-gray-600 mt-2">Complete todos los campos obligatorios</p>
        </div>



        <?php if (isset($_GET['success']) && $_GET['success'] == 'true'): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <i class="fas fa-check-circle mr-2"></i> Estudiante registrado exitosamente.
            </div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Contenedor del Formulario -->
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
            <form action="registro_estudiantes.php" method="POST" id="registroForm">
                <!-- CI -->
                <div class="mb-6">
                    <label for="ci" class="block text-lg font-medium text-gray-700 mb-2">
                        CI <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="ci" id="ci" 
                           value="<?= htmlspecialchars($formData['ci']) ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 <?= isset($errors['ci']) ? 'input-error' : '' ?>"
                           required>
                    <?php if (isset($errors['ci'])): ?>
                        <p class="error-message"><?= htmlspecialchars($errors['ci']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Nombre -->
                <div class="mb-6">
                    <label for="nombre" class="block text-lg font-medium text-gray-700 mb-2">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre" id="nombre" 
                           value="<?= htmlspecialchars($formData['nombre']) ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 <?= isset($errors['nombre']) ? 'input-error' : '' ?>"
                           required>
                    <?php if (isset($errors['nombre'])): ?>
                        <p class="error-message"><?= htmlspecialchars($errors['nombre']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Apellido Paterno -->
                <div class="mb-6">
                    <label for="apellido_paterno" class="block text-lg font-medium text-gray-700 mb-2">
                        Apellido Paterno <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="apellido_paterno" id="apellido_paterno" 
                           value="<?= htmlspecialchars($formData['apellido_paterno']) ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 <?= isset($errors['apellido_paterno']) ? 'input-error' : '' ?>"
                           required>
                    <?php if (isset($errors['apellido_paterno'])): ?>
                        <p class="error-message"><?= htmlspecialchars($errors['apellido_paterno']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Apellido Materno -->
                <div class="mb-6">
                    <label for="apellido_materno" class="block text-lg font-medium text-gray-700 mb-2">
                        Apellido Materno
                    </label>
                    <input type="text" name="apellido_materno" id="apellido_materno" 
                           value="<?= htmlspecialchars($formData['apellido_materno']) ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500">
                </div>

                <!-- RU -->
                <div class="mb-6">
                    <label for="ru" class="block text-lg font-medium text-gray-700 mb-2">
                        RU <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="ru" id="ru" 
                           value="<?= htmlspecialchars($formData['ru']) ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 <?= isset($errors['ru']) ? 'input-error' : '' ?>"
                           required>
                    <?php if (isset($errors['ru'])): ?>
                        <p class="error-message"><?= htmlspecialchars($errors['ru']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Carrera -->
                <div class="mb-6">
                    <label for="carrera" class="block text-lg font-medium text-gray-700 mb-2">
                        Carrera <span class="text-red-500">*</span>
                    </label>
                    <select name="carrera" id="carrera" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 <?= isset($errors['carrera']) ? 'input-error' : '' ?>"
                            required>
                        <option value="" disabled selected>Selecciona una carrera</option>
                        <option value="Ingeniería en Sistemas" <?= $formData['carrera'] === 'Ingeniería en Sistemas' ? 'selected' : '' ?>>Ingeniería en Sistemas</option>
                        <option value="Ingeniería Civil" <?= $formData['carrera'] === 'Ingeniería Civil' ? 'selected' : '' ?>>Ingeniería Civil</option>
                        <option value="Ingeniería Mecánica y Automotriz" <?= $formData['carrera'] === 'Ingeniería Mecánica y Automotriz' ? 'selected' : '' ?>>Ingeniería Mecánica y Automotriz</option>
                        <option value="Otra" <?= $formData['carrera'] === 'Otra' ? 'selected' : '' ?>>Otra (especificar)</option>
                    </select>
                    <?php if (isset($errors['carrera'])): ?>
                        <p class="error-message"><?= htmlspecialchars($errors['carrera']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Otra Carrera (se muestra solo cuando se selecciona "Otra") -->
                <div id="otraCarreraContainer" class="mb-6 hidden">
                    <label for="otra_carrera" class="block text-lg font-medium text-gray-700 mb-2">
                        Especificar Carrera <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="otra_carrera" id="otra_carrera" 
                           value="<?= htmlspecialchars($formData['otra_carrera']) ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 <?= isset($errors['otra_carrera']) ? 'input-error' : '' ?>">
                    <?php if (isset($errors['otra_carrera'])): ?>
                        <p class="error-message"><?= htmlspecialchars($errors['otra_carrera']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Botón de Enviar -->
                <button type="submit" class="w-full bg-green-500 text-white py-3 rounded-lg font-semibold hover:bg-green-600 transition-colors flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i> Registrar Estudiante
                </button>
            </form>
        </div>
    </div>

    <script>
        // Mostrar/ocultar campo "Otra carrera" según selección
        document.getElementById('carrera').addEventListener('change', function() {
            const otraCarreraContainer = document.getElementById('otraCarreraContainer');
            if (this.value === 'Otra') {
                otraCarreraContainer.classList.remove('hidden');
                document.getElementById('otra_carrera').required = true;
            } else {
                otraCarreraContainer.classList.add('hidden');
                document.getElementById('otra_carrera').required = false;
            }
        });

        // Validación inicial al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('carrera').value === 'Otra') {
                document.getElementById('otraCarreraContainer').classList.remove('hidden');
                document.getElementById('otra_carrera').required = true;
            }
        });

        // Validación del formulario antes de enviar
        document.getElementById('registroForm').addEventListener('submit', function(e) {
            let valid = true;
            
            // Validar campos obligatorios
            const requiredFields = ['ci', 'nombre', 'apellido_paterno', 'ru', 'carrera'];
            requiredFields.forEach(field => {
                const element = document.getElementById(field);
                if (!element.value.trim()) {
                    element.classList.add('input-error');
                    valid = false;
                }
            });
            
            // Validar campo "Otra carrera" si es necesario
            if (document.getElementById('carrera').value === 'Otra' && 
                !document.getElementById('otra_carrera').value.trim()) {
                document.getElementById('otra_carrera').classList.add('input-error');
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
                alert('Por favor complete todos los campos obligatorios.');
            }
        });
    </script>
</body>
</html>