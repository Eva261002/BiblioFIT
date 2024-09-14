<?php
include('includes/db.php');

$message = "";
$ci = $_GET['ci'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $ru = $_POST['ru'];
    $carrera = $_POST['carrera'];

    $sql = "UPDATE estudiantes SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, ru = ?, carrera = ? WHERE ci = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssss", $nombre, $apellido_paterno, $apellido_materno, $ru, $carrera, $ci);
        
        if ($stmt->execute()) {
            // Redirigir a la lista de estudiantes después de la actualización exitosa
            header("Location: listar_estudiantes.php");
            exit();
        } else {
            $message = "Error al actualizar estudiante: " . $conn->error;
        }
        
        $stmt->close();
    } else {
        $message = "Error al preparar la declaración: " . $conn->error;
    }
}

// Consultar datos del estudiante
$sql = "SELECT * FROM estudiantes WHERE ci = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ci);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h2 class="text-3xl font-bold mb-6 text-center">Editar Estudiante</h2>

        <?php if (!empty($message)): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para editar estudiante -->
        <div class="max-w-md mx-auto">
            <form action="editar_estudiante.php?ci=<?php echo htmlspecialchars($ci); ?>" method="POST" class="bg-white p-6 rounded-lg shadow-lg">
                <div class="mb-4">
                    <label for="nombre" class="block text-gray-700">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($student['nombre']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>
                <div class="mb-4">
                    <label for="apellido_paterno" class="block text-gray-700">Apellido Paterno:</label>
                    <input type="text" name="apellido_paterno" id="apellido_paterno" value="<?php echo htmlspecialchars($student['apellido_paterno']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>
                <div class="mb-4">
                    <label for="apellido_materno" class="block text-gray-700">Apellido Materno:</label>
                    <input type="text" name="apellido_materno" id="apellido_materno" value="<?php echo htmlspecialchars($student['apellido_materno']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>
                <div class="mb-4">
                    <label for="ru" class="block text-gray-700">RU:</label>
                    <input type="text" name="ru" id="ru" value="<?php echo htmlspecialchars($student['ru']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>
                <div class="mb-4">
                    <label for="carrera" class="block text-gray-700">Carrera:</label>
                    <input type="text" name="carrera" id="carrera" value="<?php echo htmlspecialchars($student['carrera']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" required>
                </div>
                <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">Actualizar Estudiante</button>
            </form>
        </div>
    </div>
</body>
</html>
