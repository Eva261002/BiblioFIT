<?php
require('fpdf/fpdf.php');
include('includes/db.php'); // Incluye la conexión a la base de datos

// Recibir los datos del formulario de reportes
$tipo_reporte = $_POST['tipo_reporte'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';

// Crear el objeto PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

// Título del reporte
$pdf->Cell(0,10, iconv('UTF-8', 'ISO-8859-1', 'Reporte de Asistencia por Carrera'), 0, 1, 'C');

// Verificar el tipo de reporte
if ($tipo_reporte == 'asistencia' && $fecha_inicio && $fecha_fin) {
    // Consulta preparada para el resumen por carrera
    $stmt_resumen = $conn->prepare("SELECT e.carrera, 
                                         COUNT(*) AS total_asistencia, 
                                         DATE_FORMAT(SEC_TO_TIME(AVG(TIMESTAMPDIFF(SECOND, es.hora_entrada, es.hora_salida))), '%H:%i') AS tiempo_promedio 
                                  FROM entradas_salidas es
                                  JOIN estudiantes e ON es.id_estudiante = e.id_estudiante
                                  WHERE es.hora_entrada BETWEEN ? AND ?
                                  GROUP BY e.carrera");
    
    $stmt_resumen->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt_resumen->execute();
    $resultado_resumen = $stmt_resumen->get_result();
    $stmt_resumen->close();
    
    if ($resultado_resumen && $resultado_resumen->num_rows > 0) {
        // Tabla Resumen
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(60,10, iconv('UTF-8', 'ISO-8859-1', 'Carrera'), 1, 0, 'C');
        $pdf->Cell(60,10, 'Tiempo Promedio', 1, 0, 'C');
        $pdf->Cell(60,10, 'Total Asistencias', 1, 1, 'C');
        
        $pdf->SetFont('Arial','',12);
        while ($row = $resultado_resumen->fetch_assoc()) {
            $pdf->Cell(60,10, iconv('UTF-8', 'ISO-8859-1', $row['carrera']), 1);
            $pdf->Cell(60,10, $row['tiempo_promedio'], 1);
            $pdf->Cell(60,10, $row['total_asistencia'], 1, 1);
        }
    } else {
        $pdf->SetFont('Arial','I',12);
        $pdf->Cell(0,10, 'No se encontraron resultados para el rango de fechas seleccionado.', 0, 1, 'C');
    }
    
    // Espacio antes de la tabla detallada
    $pdf->Ln(10);
    
    // Título de la tabla detallada
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10, iconv('UTF-8', 'ISO-8859-1', 'Detalle por Carrera y Estudiante'), 0, 1, 'C');
    
    // Consulta preparada para los datos detallados por estudiante
    $stmt_detalle = $conn->prepare("SELECT e.carrera, e.nombre, e.apellido_paterno, 
                                           SEC_TO_TIME(TIMESTAMPDIFF(SECOND, es.hora_entrada, es.hora_salida)) AS tiempo_estancia 
                                    FROM entradas_salidas es
                                    JOIN estudiantes e ON es.id_estudiante = e.id_estudiante
                                    WHERE es.hora_entrada BETWEEN ? AND ?
                                    ORDER BY e.carrera, e.nombre");
    
    $stmt_detalle->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt_detalle->execute();
    $resultado_detalle = $stmt_detalle->get_result();
    $stmt_detalle->close();
    
    if ($resultado_detalle && $resultado_detalle->num_rows > 0) {
        // Tabla Detallada
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(60,10, 'Carrera', 1);
        $pdf->Cell(60,10, 'Nombre Estudiante', 1);
        $pdf->Cell(60,10, 'Tiempo en Biblioteca', 1, 1);
        
        $pdf->SetFont('Arial','',12);
        while ($row = $resultado_detalle->fetch_assoc()) {
            $nombre_completo = iconv('UTF-8', 'ISO-8859-1', $row['nombre'] . ' ' . $row['apellido_paterno']);
            $carrera = iconv('UTF-8', 'ISO-8859-1', $row['carrera']);
            $tiempo = $row['tiempo_estancia'];
            
            $pdf->Cell(60,10, $carrera, 1);
            $pdf->Cell(60,10, $nombre_completo, 1);
            $pdf->Cell(60,10, $tiempo, 1, 1);
        }
    } else {
        $pdf->SetFont('Arial','I',12);
        $pdf->Cell(0,10, 'No se encontraron datos detallados para el rango de fechas seleccionado.', 0, 1, 'C');
    }
}

$pdf->Output();
?>
