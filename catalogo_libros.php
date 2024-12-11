<?php
include('includes/db.php');

// Filtro de búsqueda
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $conn->real_escape_string($_GET['search']);
}

// Consulta actualizada: ahora incluimos la tabla `ejemplares`
$sql = "
SELECT libros.id_libro, libros.titulo, libros.autor, libros.año_edicion, libros.pais, libros.categoria, 
       ejemplares.id_ejemplar, ejemplares.n_inventario, ejemplares.estado
FROM libros
JOIN ejemplares ON libros.id_libro = ejemplares.id_libro
WHERE titulo LIKE '%$search_query%' OR autor LIKE '%$search_query%' OR categoria LIKE '%$search_query%'
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Libros</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="scripts/catalogo_libros.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Encabezado -->
    <header class="bg-blue-600 shadow">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <!-- Icono de Biblioteca -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <!-- Contenido del SVG -->
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0H7a1 1 0 01-1-1v-2" />
                </svg>
                <a href="index.php" class="text-white text-2xl font-bold">Sistema de Biblioteca</a>
            </div>
            <div>
                <a href="index.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Inicio</a>
                <a href="catalogo_libros.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Catálogo</a>
                <a href="reportes.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">Reportes</a>
                <a href="listar_estudiantes.php" class="bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-800 transition">Estudiantes</a>
            </div>
        </nav>
    </header>

    <div class="container mx-auto py-12">
        <h1 class="text-3xl font-bold text-center mb-6">Catálogo de Libros</h1>

        <!-- Filtro de Búsqueda -->
        <form action="catalogo_libros.php" method="GET" class="mb-6">
            <input type="text" name="search" placeholder="Buscar por título, autor o categoría" value="<?php echo htmlspecialchars($search_query); ?>" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm w-full sm:w-1/3">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 ml-2">Buscar</button>
        </form>

        <!-- Botones de acción -->
        <div class="flex justify-end mb-6">
            <a href="registro_libros.php" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 mr-4">Agregar Libro</a>
            <a href="prestamo_libros.php" class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600">Ver Libros Prestados</a>
        </div>

        <!-- Tabla de Catálogo -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <div class="max-h-96 overflow-y-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <tr>
                            <th class="py-3 px-6 text-left">Título</th>
                            <th class="py-3 px-6 text-left">Autor</th>
                            <th class="py-3 px-6 text-left">Ejemplar (Inventario)</th>
                            <th class="py-3 px-6 text-left">Año de Edición</th>
                            <th class="py-3 px-6 text-left">País</th>
                            <th class="py-3 px-6 text-left">Categoría</th>
                            <th class="py-3 px-6 text-left">Estado</th>
                            <th class="py-3 px-6 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-100" id="libro-<?php echo intval($row['id_libro']); ?>">
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['titulo']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['autor']); ?></td>
                            <td class="py-3 px-6 text-left">
                                <?php echo htmlspecialchars($row['n_inventario']); ?> <!-- Número de inventario único del ejemplar -->
                            </td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['año_edicion']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['pais']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['categoria']); ?></td>
                            <td class="py-3 px-6 text-left" id="estado-<?php echo intval($row['id_ejemplar']); ?>">
                                <?php if($row['estado'] == 'disponible'): ?>
                                    <span class="bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs">Disponible</span>
                                <?php elseif($row['estado'] == 'prestado'): ?>
                                    <span class="bg-red-200 text-red-600 py-1 px-3 rounded-full text-xs">Prestado</span>
                                <?php elseif($row['estado'] == 'dañado'): ?>
                                    <span class="bg-yellow-200 text-yellow-600 py-1 px-3 rounded-full text-xs">Dañado</span>
                                <?php elseif($row['estado'] == 'perdido'): ?>
                                    <span class="bg-gray-200 text-gray-600 py-1 px-3 rounded-full text-xs">Perdido</span>
                                <?php endif; ?>
                            </td>


<td class="py-3 px-6 text-center">
    <a href="prestamo_libros.php?id_libro=<?php echo intval($row['id_libro']); ?>&id_ejemplar=<?php echo intval($row['id_ejemplar']); ?>" class="bg-yellow-500 text-white px-3 py-1 rounded-md hover:bg-yellow-600 mr-2 text-sm">Prestar</a>
</td>

                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
 <!-- Pie de Página -->
 <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto text-center">
            &copy; 2024 Sistema de Biblioteca - FIT-UABJB. Todos los derechos reservados.
        </div>
    </footer>
    
</body>
</html>
