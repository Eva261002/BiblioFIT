<?php
include('includes/db.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ci = $_POST['ci'];
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $ru = $_POST['ru'];
    $carrera = $_POST['carrera'];

    $sql = "INSERT INTO estudiantes (ci, nombre, apellido_paterno, apellido_materno, ru, carrera) VALUES (?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssss", $ci, $nombre, $apellido_paterno, $apellido_materno, $ru, $carrera);
        
        if ($stmt->execute()) {
            header("Location: listar_estudiantes.php?success=true");
            exit();
        } else {
            $message = "Error al registrar estudiante: " . $conn->error;
        }
        
        $stmt->close();
    } else {
        $message = "Error al preparar la declaración: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <!-- Encabezado -->
        <h2 class="text-4xl font-extrabold text-center mb-10 text-gray-800">Registrar Estudiante</h2>

        <div class="flex justify-start mb-6">
            <a href="listar_estudiantes.php" class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                ← Volver a la lista de estudiantes
            </a>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'true'): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                Estudiante registrado exitosamente.
            </div>
        <?php endif; ?>

        <!-- Contenedor del Formulario -->
        <div class="max-w-lg mx-auto bg-white p-8 rounded-lg shadow-lg">
            <form action="registro_estudiantes.php" method="POST">
                <!-- CI -->
                <div class="mb-6">
                    <label for="ci" class="block text-lg font-medium text-gray-700 mb-2">CI:</label>
                    <input type="text" name="ci" id="ci" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>

                <!-- Nombre -->
                <div class="mb-6">
                    <label for="nombre" class="block text-lg font-medium text-gray-700 mb-2">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>

                <!-- Apellido Paterno -->
                <div class="mb-6">
                    <label for="apellido_paterno" class="block text-lg font-medium text-gray-700 mb-2">Apellido Paterno:</label>
                    <input type="text" name="apellido_paterno" id="apellido_paterno" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>

                <!-- Apellido Materno -->
                <div class="mb-6">
                    <label for="apellido_materno" class="block text-lg font-medium text-gray-700 mb-2">Apellido Materno:</label>
                    <input type="text" name="apellido_materno" id="apellido_materno" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>

                <!-- RU -->
                <div class="mb-6">
                    <label for="ru" class="block text-lg font-medium text-gray-700 mb-2">RU:</label>
                    <input type="text" name="ru" id="ru" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>

                <!-- Carrera -->
                <div class="mb-6">
                    <label for="carrera" class="block text-lg font-medium text-gray-700 mb-2">Carrera:</label>
                    <select name="carrera" id="carrera" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500" required>
                        <option value="" disabled selected>Selecciona una carrera</option>
                        <option value="Ingenería en Sistemas">Ingenería en Sistemas</option>
                        <option value="Ingeniería Civil">Ingeniería Civil</option>
                        <option value="Ingeniería Mecánica y Automotriz">Ingeniería Mecánica y Automotriz</option>
                    </select>
                </div>

                <!-- Botón de Enviar -->
                <button type="submit" class="w-full bg-green-500 text-white py-3 rounded-lg font-semibold hover:bg-green-600 transition-colors">
                    Registrar Estudiante
                </button>
            </form>
        </div>
    </div>
</body>
</html>
