<?php
include('includes/db.php');
require('fpdf/fpdf.php');


// Inicializar variables
$tipo_reporte = $_POST['tipo_reporte'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';
$reporte_resultado = null;

// Generar el reporte basado en el tipo de reporte seleccionado
if ($tipo_reporte && $fecha_inicio && $fecha_fin) {
    switch ($tipo_reporte) {
        case 'asistencia':
            $query = "SELECT e.carrera, 
                             COUNT(*) AS total_asistencia, 
                             DATE_FORMAT(SEC_TO_TIME(AVG(TIMESTAMPDIFF(SECOND, es.hora_entrada, es.hora_salida))), '%H:%i') AS tiempo_promedio 
                      FROM entradas_salidas es
                      JOIN estudiantes e ON es.id_estudiante = e.id_estudiante
                      WHERE es.hora_entrada BETWEEN '$fecha_inicio' AND '$fecha_fin' 
                      GROUP BY e.carrera";
            break;
        
        case 'libros_prestados':
            $query = "SELECT l.titulo, l.autor, p.fecha_prestamo, p.fecha_devolucion, e.nombre, e.apellido_paterno, e.carrera 
                      FROM prestamo p 
                      JOIN estudiante l ON p.id_libro = l.id_libro 
                      JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                      WHERE p.fecha_prestamo BETWEEN '$fecha_inicio' AND '$fecha_fin'";
            break;
            case 'prestamos_libros':
                // Obtener el total de préstamos por carrera
                $query = "SELECT e.carrera, COUNT(*) AS total_prestamos 
                          FROM prestamo p 
                          JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                          WHERE p.fecha_prestamo BETWEEN '$fecha_inicio' AND '$fecha_fin' 
                          GROUP BY e.carrera";
            
                // Consulta adicional para obtener los libros más prestados
                $query_libros = "SELECT l.titulo, COUNT(*) AS total_prestamos 
                                 FROM prestamo p
                                 JOIN libros l ON p.id_libro = l.id_libro
                                 WHERE p.fecha_prestamo BETWEEN '$fecha_inicio' AND '$fecha_fin'
                                 GROUP BY l.titulo
                                 ORDER BY total_prestamos DESC
                                 LIMIT 5";
                                 $resultado_libros = $conn->query($query_libros);
                                 $libros_titulos = [];
                                 $libros_prestamos = [];
                                 
                                 while ($row = $resultado_libros->fetch_assoc()) {
                                     $libros_titulos[] = $row['titulo'];
                                     $libros_prestamos[] = $row['total_prestamos'];
                                 }
                                 
                break;
            
    }

    if (isset($query)) {
        $reporte_resultado = $conn->query($query);
    }
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generación de Reportes - Biblioteca</title>
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

    <!-- Contenedor principal -->
    <section class="container mx-auto px-4">
        <!-- Formulario de Reportes -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Generar Reporte</h2>

            <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Tipo de Reporte -->
    <div class="col-span-2 md:col-span-1">
        <label for="tipo_reporte" class="block text-gray-700">Tipo de Reporte:</label>
        <select name="tipo_reporte" id="tipo_reporte" class="w-full border border-gray-300 rounded-md px-3 py-2">
            <option value="asistencia">Asistencia por Carrera</option>
            <option value="prestamos_libros">Préstamos de libros</option>
        </select>
    </div>

    
    <!-- Rango de Fecha en una sola fila -->
    <div class="col-span-2 grid grid-cols-2 gap-4">
        <div>
            <label for="fecha_inicio" class="block text-gray-700">Fecha de Inicio:</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
        </div>
        <div>
            <label for="fecha_fin" class="block text-gray-700">Fecha de Fin:</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
        </div>
    </div>

    <!-- Botón de Generar Reporte -->
    <div class="md:col-span-2">
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">Generar Reporte</button>
    </div>
</form>

        </div>

          <!-- Descargar PDF -->
<form method="POST" action="generar_pdf.php">
    <input type="hidden" name="tipo_reporte" value="<?php echo $tipo_reporte; ?>">
    <input type="hidden" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
    <input type="hidden" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
        Descargar PDF
    </button>
</form>

        <!-- Resultados del Reporte -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">Resultados del Reporte</h3>
            <?php if ($reporte_resultado && $reporte_resultado->num_rows > 0): ?>
                <table class="min-w-full bg-white border-collapse">
                    <thead>
                        <tr>
                            <?php if ($tipo_reporte == 'asistencia'): ?>
                                <th class="py-2 border-b">Carrera</th>
                                <th class="py-2 border-b">Tiempo Promedio</th>
                                <th class="py-2 border-b">Total</th>
                            <?php elseif ($tipo_reporte == 'prestamos_carrera'): ?>
                                <th class="py-2 border-b">Carrera</th>
                                <th class="py-2 border-b">Total</th>
                            <?php elseif ($tipo_reporte == 'libros_prestados'): ?>
                                <th class="py-2 border-b">Título</th>
                                <th class="py-2 border-b">Autor</th>
                                <th class="py-2 border-b">Fecha Préstamo</th>
                                <th class="py-2 border-b">Fecha Devolución</th>
                                <th class="py-2 border-b">Nombre Estudiante</th>
                                <th class="py-2 border-b">Apellido</th>
                                <th class="py-2 border-b">Carrera</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
    <?php while ($row = $reporte_resultado->fetch_assoc()): ?>
        <tr>
            <?php if ($tipo_reporte == 'asistencia'): ?>
                <td class="py-2 border-b"><?php echo $row['carrera']; ?></td>
                <!-- Mostramos el tiempo promedio separado -->
                <td class="py-2 border-b"><?php echo $row['tiempo_promedio']; ?></td>
                </td>
                <td class="py-2 border-b"><?php echo $row['total_asistencia']; ?></td>
            <?php elseif ($tipo_reporte == 'prestamos_libros'): ?>
                <td class="py-2 border-b"><?php echo $row['carrera']; ?></td>
                <td class="py-2 border-b"><?php echo $row['total_prestamos']; ?></td>
            <?php elseif ($tipo_reporte == 'libros_prestados'): ?>
                <td class="py-2 border-b"><?php echo $row['titulo']; ?></td>
                <td class="py-2 border-b"><?php echo $row['autor']; ?></td>
                <td class="py-2 border-b"><?php echo $row['fecha_prestamo']; ?></td>
                <td class="py-2 border-b"><?php echo $row['fecha_devolucion']; ?></td>
                <td class="py-2 border-b"><?php echo $row['nombre']; ?></td>
                <td class="py-2 border-b"><?php echo $row['apellido_paterno']; ?></td>
                <td class="py-2 border-b"><?php echo $row['carrera']; ?></td>
            <?php endif; ?>
        </tr>
    <?php endwhile; ?>
    
    </table>
                <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
    <h3 class="text-xl font-semibold mb-4 text-gray-800">Libros Más Prestados</h3>
    <canvas id="chartLibrosPrestados"></canvas>
</div>
    <script>
    var ctx = document.getElementById('chartLibrosPrestados').getContext('2d');
    var chartLibrosPrestados = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($libros_titulos); ?>,
            datasets: [{
                label: 'Total de Préstamos',
                data: <?php echo json_encode($libros_prestamos); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    //validacion para rango de fecha
    document.querySelector('form').addEventListener('submit', function(event) {
    const fechaInicio = new Date(document.getElementById('fecha_inicio').value);
    const fechaFin = new Date(document.getElementById('fecha_fin').value);
    
    if (fechaFin < fechaInicio) {
        event.preventDefault();
        alert('La fecha de fin no puede ser anterior a la fecha de inicio.');
    }
});



</script>

</tbody>


            <?php else: ?>
                <p class="text-gray-600">No se encontraron resultados para el rango de fechas seleccionado.</p>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>
