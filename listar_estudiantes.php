<?php
include('includes/db.php');


// Inicializar la variable de búsqueda
$search = "";

// Verificar si se ha enviado una solicitud de búsqueda
if (isset($_POST['search'])) {
    $search = $_POST['search'];
}

// Consulta SQL con filtro de búsqueda
$sql = "SELECT * FROM estudiantes";
if (!empty($search)) {
    $search = '%' . $conn->real_escape_string($search) . '%';
    $sql .= " WHERE nombre LIKE ? OR apellido_paterno LIKE ? OR apellido_materno LIKE ? OR ci LIKE ? OR ru LIKE ? OR carrera LIKE ?";
}

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $stmt->bind_param("ssssss", $search, $search, $search, $search, $search, $search);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Estudiantes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <!-- Botón para volver a la página principal -->
        <div class="flex justify-start mb-6">
            <a href="index.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Volver al Inicio</a>
        </div>

        <h2 class="text-3xl font-bold mb-6 text-center">Lista de Estudiantes</h2>

        <!-- Formulario de búsqueda y botón de registro -->
        <form method="POST" class="mb-6 flex items-center space-x-4">
            <input 
                type="text" 
                name="search" 
                value="<?php echo htmlspecialchars($search); ?>" 
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" 
                placeholder="Buscar por CI, nombre, apellido, RU o carrera"
            />
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Buscar</button>
            <a href="registro_estudiantes.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Registrar</a>
        </form>

        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2">CI</th>
                    <th class="py-2">Nombre Completo</th>
                    <th class="py-2">RU</th>
                    <th class="py-2">Carrera</th>
                    <th class="py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['ci']); ?></td>
                            <td class="border px-4 py-2">
                                <?php 
                                // Concatenar nombre completo
                                echo htmlspecialchars($row['nombre']) . ' ' . htmlspecialchars($row['apellido_paterno']) . ' ' . htmlspecialchars($row['apellido_materno']); 
                                ?>
                            </td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['ru']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['carrera']); ?></td>
                            <td class="border px-4 py-2 text-center">
                                <a href="editar_estudiante.php?ci=<?php echo urlencode($row['ci']); ?>" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors">Editar</a>
                                <a href="eliminar_estudiante.php?ci=<?php echo urlencode($row['ci']); ?>" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors ml-2" onclick="return confirm('¿Estás seguro de que quieres eliminar este estudiante?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4">No hay estudiantes registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
 