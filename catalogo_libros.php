<?php
require_once 'includes/config.php';
 // Obtener el nombre del archivo actual
$current_module = basename($_SERVER['PHP_SELF']);

// Verificar acceso al módulo
verifyModuleAccess($current_module);


// Filtro de búsqueda
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $conn->real_escape_string($_GET['search']);
}
 
// Consulta para obtener los recursos (libros, tesis, revistas, etc.)
$sql = "
    SELECT libros.id_libro, libros.titulo, libros.autor, libros.año_edicion, libros.pais, libros.tipo_recurso, 
           ejemplares.id_ejemplar, ejemplares.n_inventario, ejemplares.sig_topog, ejemplares.estado
    FROM libros
    JOIN ejemplares ON libros.id_libro = ejemplares.id_libro
    WHERE (libros.titulo LIKE '%$search_query%' OR libros.autor LIKE '%$search_query%' OR libros.tipo_recurso LIKE '%$search_query%')
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Recursos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="scripts/catalogo_libros.js"></script>
</head>
<body class="bg-gray-100">



    <div class="container mx-auto py-12">
        <h1 class="text-3xl font-bold text-center mb-6">Catálogo de Recursos</h1>

        <!-- Filtro de Búsqueda -->
        <form action="catalogo_libros.php" method="GET" class="mb-6">
            <input type="text" name="search" placeholder="Buscar por título, autor o categoría" value="<?php echo htmlspecialchars($search_query); ?>" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm w-full sm:w-1/3">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 ml-2">Buscar</button>
        </form>

        <!-- Botones de acción -->
        <div class="flex justify-end mb-6">
            <a href="registro_recursos.php" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 mr-4">Agregar Recurso</a>
            <a href="prestamo_libros.php" class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600">Ver Recursos Prestados</a>
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
                            <th class="py-3 px-6 text-left">Categoría</th>
                            <th class="py-3 px-6 text-left">Estado</th>
                            <th class="py-3 px-6 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-100" id="libro-<?php echo intval($row['id_libro']); ?>">
                                    <td class="py-3 px-6 text-left relative">
                                        <?php echo htmlspecialchars($row['titulo']); ?>
                                        <span class="info-icon cursor-pointer text-blue-500 hover:text-blue-700">ℹ️</span>
                                        <!-- Tooltip -->
                                        <div class="info-details absolute hidden bg-white border border-gray-200 shadow-lg rounded-lg p-4 z-50">
                                            <p><strong>Nº Inventario:</strong> <?php echo htmlspecialchars($row['n_inventario']); ?></p>
                                            <p><strong>Signatura Topográfica:</strong> <?php echo htmlspecialchars($row['sig_topog']); ?></p>
                                            <p><strong>Año de Edición:</strong> <?php echo htmlspecialchars($row['año_edicion']); ?></p>
                                            <p><strong>País:</strong> <?php echo htmlspecialchars($row['pais']); ?></p>
                                        </div>
                                    </td>
                                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['autor']); ?></td>
                                    <td class="py-3 px-6 text-left">
                                        <?php echo htmlspecialchars($row['n_inventario']); ?>
                                    </td>
                                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['tipo_recurso']); ?></td>
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
                                        <?php if($row['estado'] == 'disponible'): ?>
                                            <a href="prestamo_libros.php?id_libro=<?php echo intval($row['id_libro']); ?>&id_ejemplar=<?php echo intval($row['id_ejemplar']); ?>" class="bg-yellow-500 text-white px-3 py-1 rounded-md hover:bg-yellow-600 mr-2 text-sm">Prestar</a>
                                        <?php else: ?>
                                            <span class="text-gray-500">No disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-3 px-6 text-center">No se encontraron recursos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Seleccionar todos los íconos de información
            const infoIcons = document.querySelectorAll('.info-icon');

            infoIcons.forEach(icon => {
                icon.addEventListener('mouseenter', function () {
                    // Mostrar el tooltip correspondiente
                    const tooltip = this.nextElementSibling;
                    tooltip.classList.remove('hidden');
                    tooltip.classList.add('block');
                });

                icon.addEventListener('mouseleave', function () {
                    // Ocultar el tooltip
                    const tooltip = this.nextElementSibling;
                    tooltip.classList.remove('block');
                    tooltip.classList.add('hidden');
                });
            });
        });
    </script>
</body>
</html>