<?php
require_once 'includes/config.php';


verifyModuleAccess(basename($_SERVER['PHP_SELF']));

// Verificar si se recibió el ID del estudiante
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID de estudiante no válido";
    header("Location: listar_estudiantes.php");
    exit;
}

$idEstudiante = (int)$_GET['id'];
$message = '';
$errors = [];

// Obtener datos actuales del estudiante
$sql = "SELECT * FROM estudiantes WHERE id_estudiante = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idEstudiante);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Estudiante no encontrado";
    header("Location: listar_estudiantes.php");
    exit;
}

$student = $result->fetch_assoc();

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar y validar datos
    $ci = trim($_POST['ci'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
    $apellido_materno = trim($_POST['apellido_materno'] ?? '');
    $ru = trim($_POST['ru'] ?? '');
    $carrera = trim($_POST['carrera'] ?? '');

    // Validaciones
    if (empty($ci)) {
        $errors['ci'] = "El CI es obligatorio";
    } elseif (!preg_match('/^[0-9]+$/', $ci)) {
        $errors['ci'] = "El CI debe contener solo números";
    }

    if (empty($nombre)) {
        $errors['nombre'] = "El nombre es obligatorio";
    }

    if (empty($apellido_paterno)) {
        $errors['apellido_paterno'] = "El apellido paterno es obligatorio";
    }

    if (empty($ru)) {
        $errors['ru'] = "El RU es obligatorio";
    }

    // Verificar si el CI ya existe (excepto para este estudiante)
    if (empty($errors['ci'])) {
        $sqlCheck = "SELECT id_estudiante FROM estudiantes WHERE ci = ? AND id_estudiante != ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("si", $ci, $idEstudiante);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        
        if ($resultCheck->num_rows > 0) {
            $errors['ci'] = "Este CI ya está registrado para otro estudiante";
        }
    }

    // Si no hay errores, actualizar
    if (empty($errors)) {
        $sql = "UPDATE estudiantes SET 
                ci = ?,
                nombre = ?, 
                apellido_paterno = ?, 
                apellido_materno = ?, 
                ru = ?, 
                carrera = ? 
                WHERE id_estudiante = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", 
            $ci,
            $nombre,
            $apellido_paterno,
            $apellido_materno,
            $ru,
            $carrera,
            $idEstudiante
        );
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Estudiante actualizado correctamente";
            header("Location: listar_estudiantes.php");
            exit();
        } else {
            $message = "Error al actualizar estudiante: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Estudiante - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .error-message { color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem; }
        .input-error { border-color: #ef4444 !important; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <!-- Encabezado -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-user-edit mr-2 text-blue-600"></i>Editar Estudiante
                </h1>
                <a href="listar_estudiantes.php" class="text-blue-600 hover:text-blue-800 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Volver 
                </a>
            </div>

            <!-- Mensajes de error -->
            <?php if (!empty($message)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <form action="editar_estudiante.php?id=<?= $idEstudiante ?>" method="POST">
                    <!-- CI (Documento de Identidad) -->
                    <div class="mb-6">
                        <label for="ci" class="block text-lg font-medium text-gray-700 mb-2">
                            CI <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="ci" id="ci" 
                               value="<?= htmlspecialchars($_POST['ci'] ?? $student['ci']) ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 <?= isset($errors['ci']) ? 'input-error' : '' ?>"
                               required
                               pattern="[0-9]+"
                               title="El CI debe contener solo números">
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
                               value="<?= htmlspecialchars($_POST['nombre'] ?? $student['nombre']) ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 <?= isset($errors['nombre']) ? 'input-error' : '' ?>"
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
                               value="<?= htmlspecialchars($_POST['apellido_paterno'] ?? $student['apellido_paterno']) ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 <?= isset($errors['apellido_paterno']) ? 'input-error' : '' ?>"
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
                               value="<?= htmlspecialchars($_POST['apellido_materno'] ?? $student['apellido_materno']) ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- RU -->
                    <div class="mb-6">
                        <label for="ru" class="block text-lg font-medium text-gray-700 mb-2">
                            RU <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="ru" id="ru" 
                               value="<?= htmlspecialchars($_POST['ru'] ?? $student['ru']) ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 <?= isset($errors['ru']) ? 'input-error' : '' ?>"
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
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <option value="" disabled>Seleccione una carrera</option>
                            <option value="Ingeniería en Sistemas" <?= ($_POST['carrera'] ?? $student['carrera']) === 'Ingeniería en Sistemas' ? 'selected' : '' ?>>Ingeniería en Sistemas</option>
                            <option value="Ingeniería Civil" <?= ($_POST['carrera'] ?? $student['carrera']) === 'Ingeniería Civil' ? 'selected' : '' ?>>Ingeniería Civil</option>
                            <option value="Ingeniería Mecánica y Automotriz" <?= ($_POST['carrera'] ?? $student['carrera']) === 'Ingeniería Mecánica y Automotriz' ? 'selected' : '' ?>>Ingeniería Mecánica y Automotriz</option>
                            <option value="Otra" <?= !in_array(($_POST['carrera'] ?? $student['carrera']), ['Ingeniería en Sistemas', 'Ingeniería Civil', 'Ingeniería Mecánica y Automotriz']) ? 'selected' : '' ?>>Otra</option>
                        </select>
                    </div>

                    <!-- Otra Carrera (se muestra solo cuando se selecciona "Otra") -->
                    <div id="otraCarreraContainer" class="mb-6 <?= !in_array(($_POST['carrera'] ?? $student['carrera']), ['Ingeniería en Sistemas', 'Ingeniería Civil', 'Ingeniería Mecánica y Automotriz']) ? '' : 'hidden' ?>">
                        <label for="otra_carrera" class="block text-lg font-medium text-gray-700 mb-2">
                            Especificar Carrera <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="otra_carrera" id="otra_carrera" 
                               value="<?= !in_array(($_POST['carrera'] ?? $student['carrera']), ['Ingeniería en Sistemas', 'Ingeniería Civil', 'Ingeniería Mecánica y Automotriz']) ? htmlspecialchars($_POST['carrera'] ?? $student['carrera']) : '' ?>" 
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Botón de Enviar -->
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i> Actualizar Estudiante
                    </button>
                </form>
            </div>
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
    </script>
</body>
</html>

<?php
// Cerrar conexión si está abierta
if (isset($stmt) && $stmt) $stmt->close();
if (isset($conn) && $conn) $conn->close();
?>