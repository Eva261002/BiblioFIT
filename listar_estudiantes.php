<?php
require_once 'includes/config.php';

if (isset($_SESSION['success_message'])) {
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            <i class="fas fa-check-circle mr-2"></i> ' . htmlspecialchars($_SESSION['success_message']) . '
          </div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <i class="fas fa-exclamation-circle mr-2"></i> ' . htmlspecialchars($_SESSION['error_message']) . '
          </div>';
    unset($_SESSION['error_message']);
}

$current_module = basename($_SERVER['PHP_SELF']);
verifyModuleAccess($current_module);

// Paginación
$registrosPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// Búsqueda
$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$searchParam = !empty($search) ? '%' . $search . '%' : '';

// Consulta base
$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM estudiantes";
$where = [];
$params = [];
$types = '';

// Filtro de búsqueda
if (!empty($search)) {
    $where[] = "(nombre LIKE ? OR apellido_paterno LIKE ? OR apellido_materno LIKE ? OR ci LIKE ? OR ru LIKE ? OR carrera LIKE ?)";
    $types = str_repeat('s', 6);
    $params = array_fill(0, 6, $searchParam);
}

// Construir consulta completa
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

// Ordenación
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'nombre';
$direccion = isset($_GET['dir']) && strtoupper($_GET['dir']) === 'DESC' ? 'DESC' : 'ASC';
$sql .= " ORDER BY $orden $direccion LIMIT ? OFFSET ?";

$types .= 'ii';
$params[] = $registrosPorPagina;
$params[] = $offset;

// Preparar y ejecutar consulta
$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Obtener total de registros
    $totalRegistros = $conn->query("SELECT FOUND_ROWS()")->fetch_row()[0];
    $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
} else {
    die("Error al preparar la consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Estudiantes - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .sortable:hover {
            background-color: #f3f4f6;
            cursor: pointer;
        }
        .sort-arrow {
            display: inline-block;
            margin-left: 5px;
        }
        .table-container {
            max-height: calc(100vh - 300px);
            overflow-y: auto;
        }
        th {
            position: sticky;
            top: 0;
            background-color: #2563eb;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <main class="container mx-auto px-4 py-8 flex-grow">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <!-- Encabezado y botones -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-users mr-2 text-blue-600"></i>Lista de Estudiantes
                    </h1>
                    <?php if (!empty($search)): ?>
                        <p class="text-gray-600 mt-1">Resultados para: "<?= htmlspecialchars($search) ?>"</p>
                    <?php endif; ?>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <a href="registro_estudiantes.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i> Nuevo Estudiante
                    </a>
                    
                    <!-- Botón para exportar a Excel -->
                    <a href="exportar_estudiantes.php?search=<?= urlencode($search) ?>" 
                       class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center">
                        <i class="fas fa-file-excel mr-2"></i> Exportar
                    </a>
                </div>
            </div>

            <!-- Formulario de Búsqueda -->
            <form method="POST" class="mb-6">
                <div class="flex flex-col md:flex-row gap-3">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           class="flex-grow px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Buscar por CI, nombre, apellidos, RU o carrera">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-2"></i> Buscar
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="listar_estudiantes.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors flex items-center justify-center">
                            <i class="fas fa-times mr-2"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Tabla de estudiantes -->
            <div class="table-container rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left sortable" 
                                onclick="sortTable('ci')">
                                CI
                                <?php if ($orden === 'ci'): ?>
                                    <span class="sort-arrow"><?= $direccion === 'ASC' ? '↑' : '↓' ?></span>
                                <?php endif; ?>
                            </th>
                            <th class="py-3 px-4 text-left sortable" 
                                onclick="sortTable('nombre')">
                                Nombre
                                <?php if ($orden === 'nombre'): ?>
                                    <span class="sort-arrow"><?= $direccion === 'ASC' ? '↑' : '↓' ?></span>
                                <?php endif; ?>
                            </th>
                            <th class="py-3 px-4 text-left sortable" 
                                onclick="sortTable('ru')">
                                RU
                                <?php if ($orden === 'ru'): ?>
                                    <span class="sort-arrow"><?= $direccion === 'ASC' ? '↑' : '↓' ?></span>
                                <?php endif; ?>
                            </th>
                            <th class="py-3 px-4 text-left sortable" 
                                onclick="sortTable('carrera')">
                                Carrera
                                <?php if ($orden === 'carrera'): ?>
                                    <span class="sort-arrow"><?= $direccion === 'ASC' ? '↑' : '↓' ?></span>
                                <?php endif; ?>
                            </th>
                            <th class="py-3 px-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-4"><?= htmlspecialchars($row['ci']) ?></td>
                                    <td class="py-3 px-4">
                                        <?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']) ?>
                                    </td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($row['ru']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($row['carrera']) ?></td>
                                    <td class="py-3 px-4">
                                        <div class="flex justify-center space-x-2">
                                            <!-- Botón Editar -->
                                            <a href="editar_estudiante.php?id=<?= $row['id_estudiante'] ?>" 
                                               class="text-blue-600 hover:text-blue-800 transition-colors"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Botón Eliminar  -->
                                            <a href="#" 
                                               onclick="confirmarEliminacion(<?= $row['id_estudiante'] ?>)" 
                                               class="text-red-600 hover:text-red-800 transition-colors"
                                               title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                            
                                            <!-- Botón Ver Detalles -->
                                            <a href="detalle_estudiante.php?id=<?= $row['id_estudiante'] ?>" 
                                               class="text-green-600 hover:text-green-800 transition-colors"
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-4 px-4 text-center text-gray-500">
                                    <i class="fas fa-info-circle mr-2"></i> No se encontraron estudiantes
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($totalPaginas > 1): ?>
                <div class="flex justify-between items-center mt-4">
                    <div class="text-sm text-gray-600">
                        Mostrando <?= ($offset + 1) ?> a <?= min($offset + $registrosPorPagina, $totalRegistros) ?> de <?= $totalRegistros ?> registros
                    </div>
                    <div class="flex space-x-1">
                        <?php if ($paginaActual > 1): ?>
                            <a href="?pagina=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>&orden=<?= $orden ?>&dir=<?= $direccion ?>" 
                               class="px-3 py-1 border rounded hover:bg-gray-100">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                            <a href="?pagina=<?= $paginaActual - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>&orden=<?= $orden ?>&dir=<?= $direccion ?>" 
                               class="px-3 py-1 border rounded hover:bg-gray-100">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php 
                        $inicio = max(1, $paginaActual - 2);
                        $fin = min($totalPaginas, $paginaActual + 2);
                        
                        for ($i = $inicio; $i <= $fin; $i++): ?>
                            <a href="?pagina=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>&orden=<?= $orden ?>&dir=<?= $direccion ?>" 
                               class="px-3 py-1 border rounded <?= $i == $paginaActual ? 'bg-blue-600 text-white' : 'hover:bg-gray-100' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($paginaActual < $totalPaginas): ?>
                            <a href="?pagina=<?= $paginaActual + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>&orden=<?= $orden ?>&dir=<?= $direccion ?>" 
                               class="px-3 py-1 border rounded hover:bg-gray-100">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <a href="?pagina=<?= $totalPaginas ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>&orden=<?= $orden ?>&dir=<?= $direccion ?>" 
                               class="px-3 py-1 border rounded hover:bg-gray-100">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal de confirmación -->
    <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-xl font-bold mb-4">Confirmar Eliminación</h3>
            <p>¿Estás seguro de que deseas eliminar este estudiante?</p>
            <div class="flex justify-end mt-6 space-x-3">
                <button onclick="document.getElementById('confirmModal').classList.add('hidden')" 
                        class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100 transition-colors">
                    Cancelar
                </button>
                <a id="confirmDeleteBtn" href="#" 
                   class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                    Eliminar
                </a>
            </div>
        </div>
    </div>

    <script>
        // Ordenar tabla
        function sortTable(column) {
            const url = new URL(window.location.href);
            const currentOrder = url.searchParams.get('orden');
            const currentDir = url.searchParams.get('dir');
            
            let newDir = 'ASC';
            if (currentOrder === column) {
                newDir = currentDir === 'ASC' ? 'DESC' : 'ASC';
            }
            
            url.searchParams.set('orden', column);
            url.searchParams.set('dir', newDir);
            window.location.href = url.toString();
        }
        
        // Confirmar eliminación
        function confirmarEliminacion(id) {
            const modal = document.getElementById('confirmModal');
            const deleteBtn = document.getElementById('confirmDeleteBtn');
            
            deleteBtn.href = `eliminar_estudiante.php?id=${id}`;
            modal.classList.remove('hidden');
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('confirmModal');
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>