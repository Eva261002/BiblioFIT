<?php
include('includes/db.php');
require('fpdf/fpdf.php');
include('includes/auth.php');

checkRole('admin');

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
<!-- Resto del código HTML permanece igual -->

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
    <!-- Encabezado -->
    <header class="bg-blue-600 shadow">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <!-- Icono de Biblioteca -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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


    <!-- Contenido principal -->
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Generar Reportes</h2>
            
            <!-- Filtros -->
            <form method="GET" action="reportes.php" class="mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Tipo de Reporte -->
                    <div>
                        <label class="block text-gray-700 mb-2">Tipo de Reporte</label>
                        <select name="reporte" class="w-full px-4 py-2 border rounded">
                            <option value="asistencia" <?= $tipo_reporte == 'asistencia' ? 'selected' : '' ?>>Asistencia</option>
                            <option value="prestamos" <?= $tipo_reporte == 'prestamos' ? 'selected' : '' ?>>Préstamos</option>
                            <option value="libros" <?= $tipo_reporte == 'libros' ? 'selected' : '' ?>>Libros más prestados</option>
                        </select>
                    </div>
                    
                    <!-- Fechas -->
                    <div>
                        <label class="block text-gray-700 mb-2">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" value="<?= $fecha_inicio ?>" class="w-full px-4 py-2 border rounded">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Fecha Fin</label>
                        <input type="date" name="fecha_fin" value="<?= $fecha_fin ?>" class="w-full px-4 py-2 border rounded">
                    </div>
                    
                    <!-- Botón -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                            Generar Reporte
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Botón Exportar PDF -->
            <?php if($resultados && $resultados->num_rows > 0): ?>
            <form method="POST" action="generar_pdf.php" class="mb-8">
                <input type="hidden" name="tipo_reporte" value="<?= $tipo_reporte ?>">
                <input type="hidden" name="fecha_inicio" value="<?= $fecha_inicio ?>">
                <input type="hidden" name="fecha_fin" value="<?= $fecha_fin ?>">
                <button type="submit" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700">
                    Exportar a PDF
                </button>
            </form>
            <?php endif; ?>
            
            <!-- Resultados -->
            <div class="overflow-x-auto">
                <?php if($resultados && $resultados->num_rows > 0): ?>
                
                    <?php if($tipo_reporte == 'asistencia'): ?>
                    <h3 class="text-xl font-bold mb-4">Asistencia por Carrera</h3>
                    <table class="min-w-full bg-white border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-2 px-4 border">Carrera</th>
                                <th class="py-2 px-4 border">Visitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $resultados->fetch_assoc()): ?>
                            <tr>
                                <td class="py-2 px-4 border"><?= htmlspecialchars($row['carrera']) ?></td>
                                <td class="py-2 px-4 border text-center"><?= $row['visitas'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    
                    <?php elseif($tipo_reporte == 'prestamos'): ?>
                    <h3 class="text-xl font-bold mb-4">Préstamos por Carrera</h3>
                    <table class="min-w-full bg-white border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-2 px-4 border">Carrera</th>
                                <th class="py-2 px-4 border">Total</th>
                                <th class="py-2 px-4 border">Sala</th>
                                <th class="py-2 px-4 border">Domicilio</th>
                                <th class="py-2 px-4 border">Fotocopia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $resultados->fetch_assoc()): ?>
                            <tr>
                                <td class="py-2 px-4 border"><?= htmlspecialchars($row['carrera']) ?></td>
                                <td class="py-2 px-4 border text-center"><?= $row['prestamos'] ?></td>
                                <td class="py-2 px-4 border text-center"><?= $row['sala'] ?></td>
                                <td class="py-2 px-4 border text-center"><?= $row['domicilio'] ?></td>
                                <td class="py-2 px-4 border text-center"><?= $row['fotocopia'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    
                    <?php elseif($tipo_reporte == 'libros'): ?>
                    <h3 class="text-xl font-bold mb-4">Libros más prestados</h3>
                    <table class="min-w-full bg-white border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-2 px-4 border">Libro</th>
                                <th class="py-2 px-4 border">Préstamos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $resultados->fetch_assoc()): ?>
                            <tr>
                                <td class="py-2 px-4 border"><?= htmlspecialchars($row['titulo']) ?></td>
                                <td class="py-2 px-4 border text-center"><?= $row['prestamos'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <p class="text-gray-600">No hay resultados para mostrar. Seleccione filtros y genere un reporte.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto px-4 text-center">
            <p>Sistema de Biblioteca &copy; <?= date('Y') ?></p>
        </div>
    </footer>
</body>
</html>