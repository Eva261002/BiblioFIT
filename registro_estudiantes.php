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
            // Redirigir a la lista de estudiantes después de la inserción exitosa
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
        <h2 class="text-3xl font-bold mb-6 text-center">Registrar Estudiante</h2>

        <div class="flex justify-start mb-6">
            <a href="listar_estudiantes.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Volver</a>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'true'): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">
                Estudiante registrado exitosamente.
            </div>
        <?php endif; ?>

        <!-- Contenedor para centrar y reducir el tamaño del formulario -->
        <div class="max-w-md mx-auto">
            <form action="registro_estudiantes.php" method="POST" class="bg-white p-6 rounded-lg shadow-lg">
                <!-- Campos del formulario -->
                <div class="mb-4">
                    <label for="ci" class="block text-gray-700">CI:</label>
                    <input type="text" name="ci" id="ci" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>
                <div class="mb-4">
                    <label for="nombre" class="block text-gray-700">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>
                <div class="mb-4">
                    <label for="apellido_paterno" class="block text-gray-700">Apellido Paterno:</label>
                    <input type="text" name="apellido_paterno" id="apellido_paterno" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>
                <div class="mb-4">
                    <label for="apellido_materno" class="block text-gray-700">Apellido Materno:</label>
                    <input type="text" name="apellido_materno" id="apellido_materno" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>
                <div class="mb-4">
                    <label for="ru" class="block text-gray-700">RU:</label>
                    <input type="text" name="ru" id="ru" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>
                
                <div class="mb-4">
                <label for="carrera" class="block text-sm font-medium text-gray-700">carrera</label>
                <select name="carrera" id="carrera" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Selecciona una carrera</option>
                    <option value="Ingeniría en Sistemas">Ingeniría en Sistemas</option>
                    <option value="Ingeniería Civil">Ingeniería Civil</option>
                    <option value="Ingeniería Mecánica y Automotriz">Ingeniería Mecánica y Automotriz</option>
                </select>
            </div>

                <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">Registrar Estudiante</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
//consultar libros prestados sin devolucion---libros pendientes
//prestamo de libros por carrera
//reporte de asistencia de estudiantes(por carrera)
// reportes de prestamo por periodos ----en rango de fecha