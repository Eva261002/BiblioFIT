<?php
require_once 'includes/config.php';
 // Obtener el nombre del archivo actual
$current_module = basename($_SERVER['PHP_SELF']);
 
// Verificar acceso al módulo
verifyModuleAccess($current_module);




// 1. Lógica para procesar reportes
$tipo_reporte = $_GET['reporte'] ?? 'asistencia';
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$resultados = null;

// Consultas según tipo de reporte
switch($tipo_reporte) {
    case 'asistencia':
        $query = "SELECT e.carrera, COUNT(*) as visitas 
                 FROM entradas_salidas es
                 JOIN estudiantes e ON es.id_estudiante = e.id_estudiante
                 WHERE es.hora_entrada BETWEEN ? AND ?
                 GROUP BY e.carrera";
        break;
        
    case 'prestamos':
        $query = "SELECT e.carrera, COUNT(*) as prestamos,
                         SUM(CASE WHEN p.lugar='sala' THEN 1 ELSE 0 END) as sala,
                         SUM(CASE WHEN p.lugar='domicilio' THEN 1 ELSE 0 END) as domicilio,
                         SUM(CASE WHEN p.lugar='fotocopia' THEN 1 ELSE 0 END) as fotocopia
                  FROM prestamo p
                  JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                  WHERE p.fecha_prestamo BETWEEN ? AND ?
                  GROUP BY e.carrera";
        break;
        
    case 'libros':
        $query = "SELECT l.titulo, COUNT(*) as prestamos
                 FROM prestamo p
                 JOIN ejemplares ej ON p.id_ejemplar = ej.id_ejemplar
                 JOIN libros l ON ej.id_libro = l.id_libro
                 WHERE p.fecha_prestamo BETWEEN ? AND ?
                 GROUP BY l.titulo
                 ORDER BY prestamos DESC
                 LIMIT 10";
        break;
}

if(isset($query)) {
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Error en la preparación de la consulta: ' . $conn->error);
    }
    
    $fecha_fin_completa = $fecha_fin . ' 23:59:59';
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin_completa);
    
    if (!$stmt->execute()) {
        die('Error al ejecutar la consulta: ' . $stmt->error);
    }
    
    $resultados = $stmt->get_result();
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">



    <!-- Contenido principal -->
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Generar Reportes</h2>

            <form method="GET" action="reportes.php" class="mb-8 bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <!-- Tipo de Reporte -->
                    <div>
                        <label class="block text-gray-700 mb-2 font-medium">Tipo de Reporte</label>
                        <select name="reporte" class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="asistencia" <?= $tipo_reporte == 'asistencia' ? 'selected' : '' ?>>Asistencia</option>
                            <option value="prestamos" <?= $tipo_reporte == 'prestamos' ? 'selected' : '' ?>>Préstamos</option>
                            <option value="libros" <?= $tipo_reporte == 'libros' ? 'selected' : '' ?>>Libros más prestados</option>
                        </select>
                    </div>
                    
                    <!-- Fechas -->
                    <div>
                        <label class="block text-gray-700 mb-2 font-medium">Fecha Inicio</label>
                        <div class="relative">
                            <input type="date" name="fecha_inicio" value="<?= $fecha_inicio ?>" 
                                class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2 font-medium">Fecha Fin</label>
                        <div class="relative">
                            <input type="date" name="fecha_fin" value="<?= $fecha_fin ?>" 
                                class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botón -->
                    <div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Generar Reporte
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Reemplaza el botón de exportar PDF con este -->
            <div class="flex space-x-4 mb-8">
                <form method="POST" action="generar_pdf.php">
                    <input type="hidden" name="tipo_reporte" value="<?= $tipo_reporte ?>">
                    <input type="hidden" name="fecha_inicio" value="<?= $fecha_inicio ?>">
                    <input type="hidden" name="fecha_fin" value="<?= $fecha_fin ?>">
                    <button type="submit" class="flex items-center bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Exportar PDF
                    </button>
                </form>
                
                <form method="POST" action="generar_excel.php">
                    <input type="hidden" name="tipo_reporte" value="<?= $tipo_reporte ?>">
                    <input type="hidden" name="fecha_inicio" value="<?= $fecha_inicio ?>">
                    <input type="hidden" name="fecha_fin" value="<?= $fecha_fin ?>">
                    <button type="submit" class="flex items-center bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Exportar Excel
                    </button>
                </form>
            </div>
            
            <!-- Resultados -->

            <div class="overflow-x-auto shadow-md rounded-lg">
                <?php if($resultados && $resultados->num_rows > 0): ?>
                
                    <?php if($tipo_reporte == 'asistencia'): ?>
                    <h3 class="text-xl font-bold mb-4 p-4 bg-gray-50 rounded-t-lg">Asistencia por Carrera</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carrera</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $resultados->data_seek(0);
                            $total = 0;
                            while($row = $resultados->fetch_assoc()) {
                                $total += $row['visitas'];
                            }
                            $resultados->data_seek(0);
                            while($row = $resultados->fetch_assoc()): 
                                $porcentaje = $total > 0 ? round(($row['visitas'] / $total) * 100, 2) : 0;
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($row['carrera']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $row['visitas'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $porcentaje ?>%"></div>
                                        </div>
                                        <span class="ml-2 text-xs font-medium text-gray-500"><?= $porcentaje ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <tr class="bg-gray-50 font-semibold">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $total ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">100%</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <?php elseif($tipo_reporte == 'prestamos'): ?>
                    <h3 class="text-xl font-bold mb-4 p-4 bg-gray-50 rounded-t-lg">Préstamos por Carrera</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carrera</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sala</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domicilio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fotocopia</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $resultados->data_seek(0);
                            $total_prestamos = 0;
                            $total_sala = 0;
                            $total_domicilio = 0;
                            $total_fotocopia = 0;
                            
                            while($row = $resultados->fetch_assoc()) {
                                $total_prestamos += $row['prestamos'];
                                $total_sala += $row['sala'];
                                $total_domicilio += $row['domicilio'];
                                $total_fotocopia += $row['fotocopia'];
                            }
                            $resultados->data_seek(0);
                            ?>
                            
                            <?php while($row = $resultados->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($row['carrera']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    <?= $row['prestamos'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    <?= $row['sala'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    <?= $row['domicilio'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    <?= $row['fotocopia'] ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            
                            <tr class="bg-gray-50 font-semibold">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center"><?= $total_prestamos ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center"><?= $total_sala ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center"><?= $total_domicilio ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center"><?= $total_fotocopia ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <?php elseif($tipo_reporte == 'libros'): ?>
                    <h3 class="text-xl font-bold mb-4 p-4 bg-gray-50 rounded-t-lg">Libros más prestados</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Libro</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Préstamos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $resultados->data_seek(0);
                            $total_libros = 0;
                            while($row = $resultados->fetch_assoc()) {
                                $total_libros += $row['prestamos'];
                            }
                            $resultados->data_seek(0);
                            while($row = $resultados->fetch_assoc()): 
                                $porcentaje = $total_libros > 0 ? round(($row['prestamos'] / $total_libros) * 100, 2) : 0;
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($row['titulo']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $row['prestamos'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?= $porcentaje ?>%"></div>
                                        </div>
                                        <span class="ml-2 text-xs font-medium text-gray-500"><?= $porcentaje ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <tr class="bg-gray-50 font-semibold">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $total_libros ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">100%</td>
                            </tr>
                        </tbody>
                    </table>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <p class="text-gray-600 text-center py-8">No hay resultados para mostrar. Seleccione filtros y genere un reporte.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Graficas-->
            <?php if($resultados && $resultados->num_rows > 0): ?>
            <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-bold mb-4">Visualización Gráfica</h3>
                
                <?php if($tipo_reporte == 'asistencia'): ?>
                <div class="w-full h-96">
                    <canvas id="chartAsistencia"></canvas>
                </div>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('chartAsistencia').getContext('2d');
                    const data = {
                        labels: [<?php 
                            $resultados->data_seek(0); 
                            while($row = $resultados->fetch_assoc()) {
                                echo "'".htmlspecialchars($row['carrera'])."',";
                            }
                        ?>],
                        datasets: [{
                            label: 'Visitas por Carrera',
                            data: [<?php 
                                $resultados->data_seek(0); 
                                while($row = $resultados->fetch_assoc()) {
                                    echo $row['visitas'].",";
                                }
                            ?>],
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(255, 99, 132, 0.6)',
                                'rgba(75, 192, 192, 0.6)',
                                'rgba(255, 159, 64, 0.6)',
                                'rgba(153, 102, 255, 0.6)'
                            ],
                            borderWidth: 1
                        }]
                    };
                    
                    new Chart(ctx, {
                        type: 'bar',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
                </script>
                
                <?php elseif($tipo_reporte == 'prestamos'): ?>
                <div class="w-full h-96">
                    <canvas id="chartPrestamos"></canvas>
                </div>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('chartPrestamos').getContext('2d');
                    const data = {
                        labels: [<?php 
                            $resultados->data_seek(0); 
                            while($row = $resultados->fetch_assoc()) {
                                echo "'".htmlspecialchars($row['carrera'])."',";
                            }
                        ?>],
                        datasets: [
                            {
                                label: 'Sala',
                                data: [<?php 
                                    $resultados->data_seek(0); 
                                    while($row = $resultados->fetch_assoc()) {
                                        echo $row['sala'].",";
                                    }
                                ?>],
                                backgroundColor: 'rgba(54, 162, 235, 0.6)'
                            },
                            {
                                label: 'Domicilio',
                                data: [<?php 
                                    $resultados->data_seek(0); 
                                    while($row = $resultados->fetch_assoc()) {
                                        echo $row['domicilio'].",";
                                    }
                                ?>],
                                backgroundColor: 'rgba(255, 99, 132, 0.6)'
                            },
                            {
                                label: 'Fotocopia',
                                data: [<?php 
                                    $resultados->data_seek(0); 
                                    while($row = $resultados->fetch_assoc()) {
                                        echo $row['fotocopia'].",";
                                    }
                                ?>],
                                backgroundColor: 'rgba(75, 192, 192, 0.6)'
                            }
                        ]
                    };
                    
                    new Chart(ctx, {
                        type: 'bar',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    stacked: true,
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
                </script>
                
                <?php elseif($tipo_reporte == 'libros'): ?>
                <div class="w-full h-96">
                    <canvas id="chartLibros"></canvas>
                </div>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('chartLibros').getContext('2d');
                    const data = {
                        labels: [<?php 
                            $resultados->data_seek(0); 
                            while($row = $resultados->fetch_assoc()) {
                                echo "'".htmlspecialchars($row['titulo'])."',";
                            }
                        ?>],
                        datasets: [{
                            label: 'Préstamos',
                            data: [<?php 
                                $resultados->data_seek(0); 
                                while($row = $resultados->fetch_assoc()) {
                                    echo $row['prestamos'].",";
                                }
                            ?>],
                            backgroundColor: 'rgba(153, 102, 255, 0.6)',
                            borderWidth: 1
                        }]
                    };
                    
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                }
                            }
                        }
                    });
                });
                </script>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!--FIN Graficas-->




        </div>
    </main>

<?php include 'includes/footer.php'; ?>
</body>
</html>