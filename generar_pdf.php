<?php
require('fpdf/fpdf.php');
include('includes/db.php');

// Obtener datos del formulario
$tipo_reporte = $_POST['tipo_reporte'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';

// Crear un nuevo documento PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Título del reporte
$pdf->Cell(0, 10, 'Reporte de ' . ucfirst($tipo_reporte), 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y'), 0, 1, 'R');

// Espacio
$pdf->Ln(10);

// Generar el contenido del reporte en PDF
if ($tipo_reporte == 'asistencia') {
    $query = "SELECT e.carrera, COUNT(*) AS total_asistencia, 
              DATE_FORMAT(SEC_TO_TIME(AVG(TIMESTAMPDIFF(SECOND, es.hora_entrada, es.hora_salida))), '%H:%i') AS tiempo_promedio 
              FROM entradas_salidas es
              JOIN estudiantes e ON es.id_estudiante = e.id_estudiante
              WHERE es.hora_entrada BETWEEN '$fecha_inicio' AND '$fecha_fin' 
              GROUP BY e.carrera";
} elseif ($tipo_reporte == 'prestamos_libros') {
    $query = "SELECT e.carrera, COUNT(*) AS total_prestamos 
              FROM prestamo p 
              JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
              WHERE p.fecha_prestamo BETWEEN '$fecha_inicio' AND '$fecha_fin' 
              GROUP BY e.carrera";
}

$resultado = $conn->query($query);

// Encabezados de la tabla
if ($tipo_reporte == 'asistencia') {
    $pdf->Cell(60, 10, 'Carrera', 1);
    $pdf->Cell(60, 10, 'Tiempo Promedio', 1);
    $pdf->Cell(60, 10, 'Total Asistencia', 1);
} elseif ($tipo_reporte == 'prestamos_libros') {
    $pdf->Cell(60, 10, 'Carrera', 1);
    $pdf->Cell(60, 10, 'Total Prestamos', 1);
}
$pdf->Ln();

// Contenido de la tabla
while ($row = $resultado->fetch_assoc()) {
    if ($tipo_reporte == 'asistencia') {
        $pdf->Cell(60, 10, $row['carrera'], 1);
        $pdf->Cell(60, 10, $row['tiempo_promedio'], 1);
        $pdf->Cell(60, 10, $row['total_asistencia'], 1);
    } elseif ($tipo_reporte == 'prestamos_libros') {
        $pdf->Cell(60, 10, $row['carrera'], 1);
        $pdf->Cell(60, 10, $row['total_prestamos'], 1);
    }
    $pdf->Ln();
}

// Descargar el PDF
$pdf->Output('D', 'reporte_' . $tipo_reporte . '.pdf');
?>
