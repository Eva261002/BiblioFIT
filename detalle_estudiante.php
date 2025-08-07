<?php
require_once 'includes/config.php';
checkRole('admin'); 
verifyModuleAccess(basename($_SERVER['PHP_SELF']));

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar_estudiantes.php");
    exit;
}

$idEstudiante = (int)$_GET['id'];

// Obtener datos del estudiante
$sql = "SELECT * FROM estudiantes WHERE id_estudiante = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idEstudiante);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: listar_estudiantes.php");
    exit;
}

$estudiante = $result->fetch_assoc();

// Obtener historial de entradas/salidas
$sqlHistorial = "SELECT * FROM entradas_salidas 
                 WHERE id_estudiante = ? 
                 ORDER BY hora_entrada DESC 
                 LIMIT 10";
$stmtHistorial = $conn->prepare($sqlHistorial);
$stmtHistorial->bind_param("i", $idEstudiante);
$stmtHistorial->execute();
$historialResult = $stmtHistorial->get_result();
$historial = [];
$ultimoRegistro = null;

while ($row = $historialResult->fetch_assoc()) {
    $historial[] = $row;
    if ($ultimoRegistro === null) {
        $ultimoRegistro = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Encabezado -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-user-graduate mr-2 text-blue-600"></i>
                    Detalles del Estudiante
                </h1>
                <a href="listar_estudiantes.php" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
            </div>

            <!-- Información principal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">
                        <i class="fas fa-id-card mr-2"></i>Información Personal
                    </h2>
                    <div class="space-y-3">
                        <p><span class="font-semibold">CI:</span> <?= htmlspecialchars($estudiante['ci']) ?></p>
                        <p><span class="font-semibold">Nombre Completo:</span> <?= htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido_paterno'] . ' ' . $estudiante['apellido_materno']) ?></p>
                        <p><span class="font-semibold">RU:</span> <?= htmlspecialchars($estudiante['ru']) ?></p>
                        <p><span class="font-semibold">Carrera:</span> <?= htmlspecialchars($estudiante['carrera']) ?></p>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">
                        <i class="fas fa-chart-line mr-2"></i>Estadísticas
                    </h2>
                    <div class="space-y-3">
                        <p><span class="font-semibold">Visitas totales:</span> 
                            <?= count($historial) ?> registradas</p>
                        <p><span class="font-semibold">Última visita:</span> 
                            <?= !empty($historial) ? 
                                date('d/m/Y H:i', strtotime($historial[0]['hora_entrada'])) : 
                                'Sin registros' ?>
                        </p>
                        <p><span class="font-semibold">Estado actual:</span> 
                            <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                <?= (!empty($ultimoRegistro) && empty($ultimoRegistro['hora_salida'])) ? 
                                   'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                <?= (!empty($ultimoRegistro) && empty($ultimoRegistro['hora_salida'])) ? 
                                   'Dentro de la biblioteca' : 'Fuera de la biblioteca' ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Historial de visitas -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">
                    <i class="fas fa-history mr-2"></i>Últimas Visitas
                </h2>
                
                <?php if (!empty($historial)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="px-4 py-2 text-left">Entrada</th>
                                    <th class="px-4 py-2 text-left">Salida</th>
                                    <th class="px-4 py-2 text-left">Motivo</th>
                                    <th class="px-4 py-2 text-left">Duración</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($historial as $registro): ?>
                                    <tr>
                                        <td class="px-4 py-2"><?= date('d/m/Y H:i', strtotime($registro['hora_entrada'])) ?></td>
                                        <td class="px-4 py-2">
                                            <?= !empty($registro['hora_salida']) ? 
                                               date('d/m/Y H:i', strtotime($registro['hora_salida'])) : 
                                               '<span class="text-yellow-600">En curso</span>' ?>
                                        </td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($registro['motivo'] ?? '-') ?></td> 
                                        <td class="px-4 py-2">
                                            <?php if (!empty($registro['hora_salida'])): ?>
                                                <?php 
                                                    $entrada = new DateTime($registro['hora_entrada']);
                                                    $salida = new DateTime($registro['hora_salida']);
                                                    $intervalo = $entrada->diff($salida);
                                                    echo $intervalo->format('%Hh %Im');
                                                ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No hay registros de visitas para este estudiante.</p>
                <?php endif; ?>
            </div>

            <!-- Acciones -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="editar_estudiante.php?id=<?= $estudiante['id_estudiante'] ?>" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-edit mr-2"></i>Editar
                </a>
                <a href="#" onclick="confirmarEliminacion(<?= $estudiante['id_estudiante'] ?>)" 
                   class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i>Eliminar
                </a>
            </div>
        </div>
    </div>

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
                <a id="confirmDeleteBtn" href="eliminar_estudiante.php?id=<?= $estudiante['id_estudiante'] ?>" 
                   class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                    Eliminar
                </a>
            </div>
        </div>
    </div>

    <script>
        function confirmarEliminacion(id) {
            document.getElementById('confirmModal').classList.remove('hidden');
        }
    </script>
</body>
</html>

<?php
$stmt->close();
$stmtHistorial->close();
$conn->close();
?>