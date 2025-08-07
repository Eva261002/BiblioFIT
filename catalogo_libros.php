<?php
require_once 'includes/config.php';
$current_module = basename($_SERVER['PHP_SELF']);
verifyModuleAccess($current_module);

// Parámetros de paginación
$registrosPorPagina = 10; // Número de registros por página
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// Parámetros de ordenación
$orden = isset($_GET['orden']) ? $_GET['orden'] : '';
$direccion = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) 
            ? strtoupper($_GET['dir']) 
            : 'ASC';

// Filtro de búsqueda
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Consulta para el total de registros
$sqlTotal = "SELECT COUNT(*) as total 
             FROM libros
             JOIN ejemplares ON libros.id_libro = ejemplares.id_libro
             WHERE (libros.titulo LIKE '%$search_query%' OR libros.autor LIKE '%$search_query%' OR libros.tipo_recurso LIKE '%$search_query%')";

$resultTotal = $conn->query($sqlTotal);
$totalRegistros = $resultTotal->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Consulta principal con paginación y ordenación
$sql = "SELECT libros.id_libro, libros.titulo, libros.autor, libros.año_edicion, libros.pais, libros.tipo_recurso, 
               ejemplares.id_ejemplar, ejemplares.n_inventario, ejemplares.sig_topog, ejemplares.estado
        FROM libros
        JOIN ejemplares ON libros.id_libro = ejemplares.id_libro
        WHERE (libros.titulo LIKE '%$search_query%' OR libros.autor LIKE '%$search_query%' OR libros.tipo_recurso LIKE '%$search_query%')";

if ($orden && in_array($orden, ['titulo', 'autor', 'n_inventario', 'tipo_recurso', 'estado'])) {
    $sql .= " ORDER BY $orden $direccion";
}

$sql .= " LIMIT $offset, $registrosPorPagina";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Recursos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="scripts/catalogo_libros.js"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Barra de búsqueda y botones -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <form action="catalogo_libros.php" method="GET" class="w-full md:w-1/2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" placeholder="Buscar por título, autor o categoría..." 
                           value="<?= htmlspecialchars($search_query) ?>" 
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-md w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="absolute right-0 top-0 h-full px-4 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
           
            
            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <a href="registro_recursos.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Agregar
                </a>
                <a href="prestamo_libros.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition flex items-center gap-2">
                    <i class="fas fa-book-open"></i> Préstamos
                </a>
            </div>
        </div>

        <!-- Tabla de Catálogo -->
        <div class="table-container rounded-lg border border-gray-200 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="py-3 px-4 text-left sortable" onclick="sortTable('titulo')">
                            Título
                            <?php if ($orden === 'titulo'): ?>
                                <span class="sort-arrow"><?= $direccion === 'ASC' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </th>
                        <th class="py-3 px-4 text-left sortable" onclick="sortTable('autor')">
                            Autor
                            <?php if ($orden === 'autor'): ?>
                                <span class="sort-arrow"><?= $direccion === 'ASC' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </th>
                        <th class="py-3 px-4 text-left sortable" onclick="sortTable('n_inventario')">
                            Inventario
                            <?php if ($orden === 'n_inventario'): ?>
                                <span class="sort-arrow"><?= $direccion === 'ASC' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </th>
                        <th class="py-3 px-4 text-left sortable" onclick="sortTable('tipo_recurso')">
                            Categoría
                            <?php if ($orden === 'tipo_recurso'): ?>
                                <span class="sort-arrow"><?= $direccion === 'ASC' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </th>
                        <th class="py-3 px-4 text-left sortable" onclick="sortTable('estado')">
                            Estado
                            <?php if ($orden === 'estado'): ?>
                                <span class="sort-arrow"><?= $direccion === 'ASC' ? '↑' : '↓' ?></span>
                            <?php endif; ?>
                        </th>
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors" id="libro-<?= intval($row['id_libro']) ?>">
                                <td class="py-3 px-4 relative">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 text-blue-600">
                                            <i class="fas fa-book text-sm"></i>
                                        </div>
                                        <div>
                                            <div><?= htmlspecialchars($row['titulo']) ?></div>
                                            <span class="info-icon cursor-pointer text-blue-500 hover:text-blue-700 text-xs flex items-center mt-1">
                                                <i class="fas fa-info-circle mr-1"></i> Detalles
                                            </span>
                                            <!-- Tooltip -->
                                            <div class="info-details absolute hidden z-50 w-64 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg p-4 left-4">
                                                <div class="space-y-2 text-sm">
                                                    <div>
                                                        <span class="font-semibold text-gray-700">Nº Inventario:</span>
                                                        <span class="text-gray-600"><?= htmlspecialchars($row['n_inventario']) ?></span>
                                                    </div>
                                                    <div>
                                                        <span class="font-semibold text-gray-700">Signatura:</span>
                                                        <span class="text-gray-600"><?= htmlspecialchars($row['sig_topog']) ?></span>
                                                    </div>
                                                    <div>
                                                        <span class="font-semibold text-gray-700">Año:</span>
                                                        <span class="text-gray-600"><?= htmlspecialchars($row['año_edicion']) ?></span>
                                                    </div>
                                                    <div>
                                                        <span class="font-semibold text-gray-700">País:</span>
                                                        <span class="text-gray-600"><?= htmlspecialchars($row['pais']) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4"><?= htmlspecialchars($row['autor']) ?></td>
                                <td class="py-3 px-4 font-mono text-sm"><?= htmlspecialchars($row['n_inventario']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                        <?= htmlspecialchars($row['tipo_recurso']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4" id="estado-<?= intval($row['id_ejemplar']) ?>">
                                    <?php if($row['estado'] == 'disponible'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 flex items-center w-fit">
                                            <i class="fas fa-check-circle mr-1"></i> Disponible
                                        </span>
                                    <?php elseif($row['estado'] == 'prestado'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 flex items-center w-fit">
                                            <i class="fas fa-exclamation-circle mr-1"></i> Prestado
                                        </span>
                                    <?php elseif($row['estado'] == 'dañado'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 flex items-center w-fit">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Dañado
                                        </span>
                                    <?php elseif($row['estado'] == 'perdido'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 flex items-center w-fit">
                                            <i class="fas fa-times-circle mr-1"></i> Perdido
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-3">
                                        <?php if($row['estado'] == 'disponible'): ?>
                                            <a href="#" onclick="return confirmarPrestamo(<?= intval($row['id_libro']) ?>, '<?= addslashes($row['titulo']) ?>')" 
                                            class="text-blue-600 hover:text-blue-800 transition-colors"
                                            title="Solicitar préstamo">
                                                <i class="fas fa-book-reader"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 cursor-not-allowed" title="No disponible">
                                                <i class="fas fa-hand-holding"></i>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <a href="#" 
                                        onclick="return confirmarEdicion(<?= intval($row['id_libro']) ?>, '<?= addslashes($row['titulo']) ?>')" 
                                        class="text-green-600 hover:text-green-800 transition-colors"
                                        title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <a href="#" onclick="return confirmarEliminacion(<?= intval($row['id_libro']) ?>, '<?= addslashes($row['titulo']) ?>')" 
                                        class="text-red-600 hover:text-red-800 transition-colors"
                                        title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="py-4 px-4 text-center text-gray-500">
                                <i class="fas fa-info-circle mr-2"></i> No se encontraron recursos
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    <?php if ($totalPaginas > 1): ?>
        <div class="flex flex-col sm:flex-row justify-between items-center mt-6 bg-white p-4 rounded-lg border border-gray-200">
            <div class="text-sm text-gray-600 mb-2 sm:mb-0">
                Mostrando <?= ($offset + 1) ?> a <?= min($offset + $registrosPorPagina, $totalRegistros) ?> de <?= $totalRegistros ?> registros
            </div>
            <div class="flex items-center space-x-1">
                <?php if ($paginaActual > 1): ?>
                    <a href="?pagina=1<?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&orden=<?= $orden ?>&dir=<?= $direccion ?>" 
                    class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                    title="Primera página">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?pagina=<?= ($paginaActual - 1) ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&orden=<?= $orden ?>&dir=<?= $direccion ?>" 
                    class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                    title="Página anterior">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php 
                // Mostrar números de página (5 en total, centrados en la actual)
                $inicio = max(1, min($paginaActual - 2, $totalPaginas - 4));
                $fin = min($totalPaginas, max($paginaActual + 2, 5));
                
                for ($i = $inicio; $i <= $fin; $i++): ?>
                    <a href="?pagina=<?= $i ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&orden=<?= $orden ?>&dir=<?= $direccion ?>" 
                    class="px-3 py-1 border border-gray-300 rounded-md <?= $i == $paginaActual ? 'bg-blue-600 text-white hover:bg-blue-700' : 'hover:bg-gray-50' ?> transition-colors min-w-[2.5rem] text-center">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($paginaActual < $totalPaginas): ?>
                    <a href="?pagina=<?= ($paginaActual + 1) ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&orden=<?= $orden ?>&dir=<?= $direccion ?>" 
                    class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                    title="Página siguiente">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?pagina=<?= $totalPaginas ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>&orden=<?= $orden ?>&dir=<?= $direccion ?>" 
                    class="px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                    title="Última página">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <script>
    function showModal(title, message, actionUrl) {
                document.getElementById('modalTitle').textContent = title;
                document.getElementById('modalMessage').textContent = message;
                const confirmBtn = document.getElementById('confirmAction');
                
                // Actualizar la acción del botón
                confirmBtn.onclick = function() {
                    window.location.href = actionUrl;
                };
                
                // Mostrar modal
                document.getElementById('confirmationModal').classList.remove('hidden');
            }

            function closeModal() {
                document.getElementById('confirmationModal').classList.add('hidden');
            }

            // Funciones actualizadas para préstamo y eliminación
            function confirmarPrestamo(id_libro, titulo) {
                showModal(
                    'Confirmar Préstamo',
                    `¿Deseas solicitar el préstamo del libro "${titulo}"?`,
                    `prestamo_libros.php?id_libro=${id_libro}`
                );
                return false;
            }
            // Función para confirmar edición
            function confirmarEdicion(id, titulo) {
                showModal(
                    'Editar Recurso',
                    `¿Deseas editar el recurso "${titulo}"?`,
                    `editar_recurso.php?id=${id}`
                );
                return false;
            }
            function confirmarEliminacion(id, titulo) {
                showModal(
                    'Confirmar Eliminación',
                    `¿Deseas eliminar permanentemente el recurso "${titulo}"?\n\nEsta acción también borrará todos sus ejemplares asociados.`,
                    `eliminar_recurso.php?id=${id}`
                );
                return false;
            }

            // Función para ordenar la tabla
            function sortTable(column) {
                const url = new URL(window.location.href);
                const currentOrder = url.searchParams.get('orden');
                const currentDirection = url.searchParams.get('direccion');
                
                // Si ya estamos ordenando por esta columna, invertimos la dirección
                if (currentOrder === column) {
                    url.searchParams.set('direccion', currentDirection === 'ASC' ? 'DESC' : 'ASC');
                } else {
                    // Si es una nueva columna, ordenamos ASC por defecto
                    url.searchParams.set('orden', column);
                    url.searchParams.set('direccion', 'ASC');
                }
                
                // Mantener el parámetro de búsqueda si existe
                if (window.location.search.includes('search=')) {
                    url.searchParams.set('search', new URLSearchParams(window.location.search).get('search'));
                }
                
                window.location.href = url.toString();
            }

            // Tooltips con comportamiento hover (como en tu versión original)
            document.addEventListener('DOMContentLoaded', function () {
                const infoIcons = document.querySelectorAll('.info-icon');

                infoIcons.forEach(icon => {
                    icon.addEventListener('mouseenter', function () {
                        const tooltip = this.nextElementSibling;
                        tooltip.classList.remove('hidden');
                        tooltip.classList.add('block');
                    });

                    icon.addEventListener('mouseleave', function () {
                        const tooltip = this.nextElementSibling;
                        tooltip.classList.remove('block');
                        tooltip.classList.add('hidden');
                    });
                });
            });

    </script>
    <!-- Modal de Confirmación -->
    <div id="confirmationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-xl font-bold text-gray-800"></h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p id="modalMessage" class="mb-6 text-gray-700"></p>
            <div class="flex justify-end space-x-4">
                <button onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Cancelar
                </button>
                <button id="confirmAction" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
    <?php include('includes/footer.php'); ?>
</body>
</html>