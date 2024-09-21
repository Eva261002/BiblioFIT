
<?php




//consultar libros prestados sin devolucion---libros pendientes
//prestamo de libros por carrera
//reporte de asistencia de estudiantes(por carrera)
// reportes de prestamo por periodos ----en rango de fecha


include('includes/db.php');
require 'vendor/autoload.php'; // Asegúrate de tener Composer instalado y PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Consulta para obtener los libros más prestados
$sql_mas_prestados = "SELECT l.titulo, COUNT(p.id_prestamo) AS cantidad_prestamos 
                      FROM prestamo p 
                      JOIN libros l ON p.id_libro = l.id_libro 
                      GROUP BY p.id_libro 
                      ORDER BY cantidad_prestamos DESC 
                      LIMIT 10";
$result_mas_prestados = $conn->query($sql_mas_prestados);

// Preparar datos para Chart.js
$labels = [];
$datos = [];
while($row = $result_mas_prestados->fetch_assoc()) {
    $labels[] = $row['titulo'];
    $datos[] = $row['cantidad_prestamos'];
}

// Exportar a Excel si se solicita
if(isset($_POST['exportar_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Título');
    $sheet->setCellValue('B1', 'Número de Préstamos');

    $fila = 2;
    $result_mas_prestados->data_seek(0); // Reiniciar el puntero de resultados
    while($row = $result_mas_prestados->fetch_assoc()) {
        $sheet->setCellValue('A' . $fila, $row['titulo']);
        $sheet->setCellValue('B' . $fila, $row['cantidad_prestamos']);
        $fila++;
    }

    $writer = new Xlsx($spreadsheet);
    $nombre_archivo = 'Reporte_Libros_Mas_Prestados.xlsx';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'. $nombre_archivo .'"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes de Préstamos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>



</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-12">
        <h1 class="text-3xl font-bold text-center mb-6">Reportes de Préstamos</h1>
        
        <!-- Gráfico de Libros Más Prestados -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-2xl font-bold mb-4">Libros Más Prestados</h2>
            <canvas id="grafico-prestamos" width="400" height="200"></canvas>
        </div>
        
        <!-- Botón para Exportar a Excel -->
        <div class="flex justify-end mb-6">
            <form method="POST">
                <button type="submit" name="exportar_excel" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Exportar a Excel</button>
            </form>
        </div>
        
        <!-- Tabla de Libros Más Prestados -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">Tabla de Libros Más Prestados</h2>
            <table id="tabla-reportes" class="display">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Número de Préstamos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result_mas_prestados->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                        <td><?php echo intval($row['cantidad_prestamos']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
    $(document).ready(function() {
        $('#tabla-reportes').DataTable();
    });

    var ctx = document.getElementById('grafico-prestamos').getContext('2d');
    var graficoPrestamos = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: '# de Préstamos',
                data: <?php echo json_encode($datos); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor:'rgba(75, 192, 192, 1)',
                borderWidth:1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    </script>
</body>
</html>
