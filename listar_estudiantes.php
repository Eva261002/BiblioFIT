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
    $sql = "SELECT * FROM estudiantes WHERE nombre LIKE ? OR apellido_paterno LIKE ? OR apellido_materno LIKE ? OR ci LIKE ? OR ru LIKE ? OR carrera LIKE ?";
}

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $search_param = '%' . $search . '%';
    $stmt->bind_param("ssssss", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Estudiantes - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Heroicons para iconos -->
    <script src="https://unpkg.com/heroicons@1.0.6/dist/heroicons.min.js"></script>
    
    <style>
        
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <!-- Encabezado -->
    <header class="bg-blue-600 shadow">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <!-- Icono de Biblioteca -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0H7a1 1 0 01-1-1V5a1 1 0 011-1h3m6 0h3a1 1 0 011 1v10a1 1 0 01-1 1h-3" />
                </svg>
                <a href="index.php" class="text-white text-2xl font-bold">Sistema de Biblioteca</a>
            </div>
            <div>
                <a href="index.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Inicio</a>
                <a href="catalogo_libros.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Catálogo</a>
                <a href="reportes.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Reportes</a>
                <a href="listar_estudiantes.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Estudiantes</a>
            </div>
        </nav>
    </header>

    <!-- Sección Principal -->
    <main class="container mx-auto px-6 py-12 flex-grow">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <!-- Título y Botones -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Lista de Estudiantes</h2>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="registro_estudiantes.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                        <!-- Icono de agregar -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Registrar Estudiante
                    </a>
                </div>
            </div>

            <!-- Formulario de Búsqueda -->
            <form method="POST" class="mb-6">
                <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4">
                    <input 
                        type="text" 
                        name="search" 
                        value="<?php echo htmlspecialchars($search); ?>" 
                        class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" 
                        placeholder="Buscar por CI, nombre, apellido, RU o carrera"
                    />
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">Buscar</button>
                </div>
            </form>

            <!-- Contenedor de la tabla con barra de desplazamiento -->
<div class="overflow-x-auto">
    <div class="max-h-96 overflow-y-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="py-3 px-6 text-left">CI</th>
                    <th class="py-3 px-6 text-left">Nombre Completo</th>
                    <th class="py-3 px-6 text-left">RU</th>
                    <th class="py-3 px-6 text-left">Carrera</th>
                    <th class="py-3 px-6 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-100 transition-colors">
                            <td class="py-3 px-6"><?php echo htmlspecialchars($row['ci']); ?></td>
                            <td class="py-3 px-6">
                                <?php 
                                // Concatenar nombre completo
                                echo htmlspecialchars($row['nombre']) . ' ' . htmlspecialchars($row['apellido_paterno']) . ' ' . htmlspecialchars($row['apellido_materno']); 
                                ?>
                            </td>
                            <td class="py-3 px-6"><?php echo htmlspecialchars($row['ru']); ?></td>
                            <td class="py-3 px-6"><?php echo htmlspecialchars($row['carrera']); ?></td>
                            <td class="border px-4 py-2 text-center">
                                <div class="flex space-x-2 justify-center">
                                    <!-- Botón de Editar -->
                                    <a href="editar_estudiante.php?ci=<?php echo urlencode($row['ci']); ?>" 
                                    class="bg-yellow-500 text-white px-3 py-1 rounded-lg hover:bg-yellow-700 transition-colors flex items-center justify-center">
                                        <!-- Icono de editar -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9.354 15.354a2 2 0 010-2.828l6.364-6.364a2 2 0 012.828 0l3.536 3.536a2 2 0 010 2.828l-6.364 6.364a2 2 0 01-2.828 0l-3.536-3.536z" />
                                        </svg>
                                        Editar
                                    </a>

                                    <!-- Botón de Eliminar -->
                                    <a href="eliminar_estudiante.php?ci=<?php echo urlencode($row['ci']); ?>" 
                                    class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center"
                                    onclick="return confirm('¿Estás seguro de que quieres eliminar este estudiante?');">
                                        <!-- Icono de eliminar -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Eliminar
                                    </a>
                                </div>
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
</div>

    </main>

    <!-- Pie de Página -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto text-center">
            &copy; 2024 Sistema de Biblioteca - FIT-UABJB. Todos los derechos reservados.
        </div>
    </footer>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
