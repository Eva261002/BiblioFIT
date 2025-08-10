<?php
require('fpdf/fpdf.php');
include('includes/db.php');
include('includes/auth.php');

date_default_timezone_set('Etc/GMT-4');
// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reportes.php');
    exit;
}

// Validar parámetros
if (!isset($_POST['tipo_reporte']) || !isset($_POST['fecha_inicio']) || !isset($_POST['fecha_fin'])) {
    die('Parámetros incompletos');
}

$tipo_reporte = $_POST['tipo_reporte'];
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'] . ' 23:59:59';

// Crear PDF en orientación vertical para mejor flujo de lectura
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Función para convertir texto
function textoPDF($texto) {
    return iconv('UTF-8', 'ISO-8859-1', $texto);
}


// Título del reporte 
$titulos = [
    'asistencia' => 'Reporte Detallado de Asistencia',
    'prestamos' => 'Reporte Detallado de Prestamos',
    'libros' => 'Reporte Detallado de Libros Prestados'
];

$pdf->Cell(0, 10, textoPDF($titulos[$tipo_reporte] ?? 'Reporte'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, textoPDF('Periodo: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($_POST['fecha_fin']))), 0, 1, 'C');
$pdf->Ln(10);

// Procesar según tipo de reporte
switch($tipo_reporte) {
    case 'asistencia':
        generarReporteAsistencia($pdf, $conn, $fecha_inicio, $fecha_fin);
        break;
        
    case 'prestamos':
        generarReportePrestamos($pdf, $conn, $fecha_inicio, $fecha_fin);
        break;
        
    case 'libros':
        generarReporteLibros($pdf, $conn, $fecha_inicio, $fecha_fin);
        break;
        
    default:
        $pdf->Cell(0, 10, textoPDF('Tipo de reporte no válido'), 0, 1);
}

// Pie de página
$pdf->SetY(-15);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 10, textoPDF('Generado el ' . date('d/m/Y H:i')), 0, 0, 'C');

// Salida del PDF
$pdf->Output('I', 'Reporte_Detallado_' . $tipo_reporte . '_' . date('Ymd') . '.pdf');

// FUNCIONES PARA GENERAR REPORTES DETALLADOS

function generarReporteAsistencia($pdf, $conn, $fecha_inicio, $fecha_fin) {
    // 1. Resumen por carrera
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, textoPDF('Resumen por Carrera'), 0, 1);
    $pdf->Ln(5);
    
    $query_resumen = "SELECT e.carrera, COUNT(*) as visitas 
                     FROM entradas_salidas es
                     JOIN estudiantes e ON es.id_estudiante = e.id_estudiante
                     WHERE es.hora_entrada BETWEEN ? AND ?
                     GROUP BY e.carrera
                     ORDER BY visitas DESC";
    
    $stmt = $conn->prepare($query_resumen);
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $resumen = $stmt->get_result();
    
    // Tabla de resumen
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(100, 8, textoPDF('Carrera'), 1);
    $pdf->Cell(30, 8, 'Visitas', 1, 1);
    
    $pdf->SetFont('Arial', '', 10);
    while ($row = $resumen->fetch_assoc()) {
        $pdf->Cell(100, 7, textoPDF($row['carrera']), 1);
        $pdf->Cell(30, 7, $row['visitas'], 1, 1, 'C');
    }
    
    $pdf->Ln(15);
    
    // 2. Detalle por estudiante
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, textoPDF('Detalle por Estudiante'), 0, 1);
    $pdf->Ln(5);
    
    $query_detalle = "SELECT e.ru, e.nombre, e.apellido_paterno, e.apellido_materno, e.carrera,
                             COUNT(*) as total_visitas,
                             MIN(es.hora_entrada) as primera_visita,
                             MAX(es.hora_entrada) as ultima_visita
                      FROM entradas_salidas es
                      JOIN estudiantes e ON es.id_estudiante = e.id_estudiante
                      WHERE es.hora_entrada BETWEEN ? AND ?
                      GROUP BY e.id_estudiante
                      ORDER BY e.carrera, total_visitas DESC";
    
    $stmt = $conn->prepare($query_detalle);
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $detalle = $stmt->get_result();
    
    if ($detalle->num_rows > 0) {
        // Encabezados de la tabla detallada
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 7, 'RU', 1);
        $pdf->Cell(60, 7, textoPDF('Estudiante'), 1);
        $pdf->Cell(40, 7, textoPDF('Carrera'), 1);
        $pdf->Cell(20, 7, 'Visitas', 1, 0, 'C');
        $pdf->Cell(25, 7, 'Primera Visita', 1, 0, 'C');
        $pdf->Cell(25, 7, 'Ultima Visita', 1, 1, 'C');
        
        $pdf->SetFont('Arial', '', 8);
        while ($row = $detalle->fetch_assoc()) {
            $nombre = $row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'];
            $primera_visita = date('d/m H:i', strtotime($row['primera_visita']));
            $ultima_visita = date('d/m H:i', strtotime($row['ultima_visita']));
            
            $pdf->Cell(20, 6, $row['ru'], 1);
            $pdf->Cell(60, 6, textoPDF($nombre), 1);
            $pdf->Cell(40, 6, textoPDF($row['carrera']), 1);
            $pdf->Cell(20, 6, $row['total_visitas'], 1, 0, 'C');
            $pdf->Cell(25, 6, $primera_visita, 1, 0, 'C');
            $pdf->Cell(25, 6, $ultima_visita, 1, 1, 'C');
        }
    } else {
        $pdf->Cell(0, 10, textoPDF('No hay datos detallados de asistencia'), 0, 1);
    }
}

function generarReportePrestamos($pdf, $conn, $fecha_inicio, $fecha_fin) {
    // 1. Resumen por carrera
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, textoPDF('Resumen por Carrera'), 0, 1);
    $pdf->Ln(5);
    
    $query_resumen = "SELECT e.carrera, COUNT(*) as prestamos,
                             SUM(CASE WHEN p.lugar='sala' THEN 1 ELSE 0 END) as sala,
                             SUM(CASE WHEN p.lugar='domicilio' THEN 1 ELSE 0 END) as domicilio,
                             SUM(CASE WHEN p.lugar='fotocopia' THEN 1 ELSE 0 END) as fotocopia
                      FROM prestamo p
                      JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                      WHERE p.fecha_prestamo BETWEEN ? AND ?
                      GROUP BY e.carrera
                      ORDER BY prestamos DESC";
    
    $stmt = $conn->prepare($query_resumen);
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $resumen = $stmt->get_result();
    
    // Tabla de resumen
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(70, 7, textoPDF('Carrera'), 1);
    $pdf->Cell(20, 7, 'Total', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Sala', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Domicilio', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Fotocopia', 1, 0, 'C');
    $pdf->Cell(30, 7, '% Domicilio', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9);
    while ($row = $resumen->fetch_assoc()) {
        $porcentaje = $row['prestamos'] > 0 ? round(($row['domicilio']/$row['prestamos'])*100, 1) : 0;
        
        $pdf->Cell(70, 6, textoPDF($row['carrera']), 1);
        $pdf->Cell(20, 6, $row['prestamos'], 1, 0, 'C');
        $pdf->Cell(20, 6, $row['sala'], 1, 0, 'C');
        $pdf->Cell(25, 6, $row['domicilio'], 1, 0, 'C');
        $pdf->Cell(25, 6, $row['fotocopia'], 1, 0, 'C');
        $pdf->Cell(30, 6, $porcentaje . '%', 1, 1, 'C');
    }
    
    $pdf->Ln(15);
    
    // 2. Detalle de préstamos
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, textoPDF('Detalle de Préstamos'), 0, 1);
    $pdf->Ln(5);
    
    $query_detalle = "SELECT p.id_prestamo, p.fecha_prestamo, p.lugar,
                             e.ru, e.nombre, e.apellido_paterno, e.apellido_materno, e.carrera,
                             l.titulo as libro
                      FROM prestamo p
                      JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                      JOIN ejemplares ej ON p.id_ejemplar = ej.id_ejemplar
                      JOIN libros l ON ej.id_libro = l.id_libro
                      WHERE p.fecha_prestamo BETWEEN ? AND ?
                      ORDER BY p.fecha_prestamo DESC";
    
    $stmt = $conn->prepare($query_detalle);
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $detalle = $stmt->get_result();
    
    if ($detalle->num_rows > 0) {
        // Encabezados de la tabla detallada
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(20, 7, 'Fecha', 1);
        $pdf->Cell(15, 7, 'Lugar', 1);
        $pdf->Cell(20, 7, 'RU', 1);
        $pdf->Cell(50, 7, textoPDF('Estudiante'), 1);
        $pdf->Cell(85, 7, textoPDF('Libro'), 1, 1);
        
        $pdf->SetFont('Arial', '', 8);
        while ($row = $detalle->fetch_assoc()) {
            $fecha = date('d/m H:i', strtotime($row['fecha_prestamo']));
            $nombre = $row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'];
            $libro = strlen($row['libro']) > 50 ? substr($row['libro'], 0, 47) . '...' : $row['libro'];
            
            $pdf->Cell(20, 6, $fecha, 1);
            $pdf->Cell(15, 6, textoPDF($row['lugar']), 1);
            $pdf->Cell(20, 6, $row['ru'], 1);
            $pdf->Cell(50, 6, textoPDF($nombre), 1);
            $pdf->Cell(85, 6, textoPDF($libro), 1, 1);
        }
    } else {
        $pdf->Cell(0, 10, textoPDF('No hay datos detallados de préstamos'), 0, 1);
    }
}

function generarReporteLibros($pdf, $conn, $fecha_inicio, $fecha_fin) {
    // 1. Resumen de libros más prestados
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, textoPDF('Libros Más Prestados'), 0, 1);
    $pdf->Ln(5);
    
    $query_resumen = "SELECT l.titulo, l.autor, COUNT(*) as prestamos
                      FROM prestamo p
                      JOIN ejemplares ej ON p.id_ejemplar = ej.id_ejemplar
                      JOIN libros l ON ej.id_libro = l.id_libro
                      WHERE p.fecha_prestamo BETWEEN ? AND ?
                      GROUP BY l.id_libro
                      ORDER BY prestamos DESC
                      LIMIT 10";
    
    $stmt = $conn->prepare($query_resumen);
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $resumen = $stmt->get_result();
    
    // Tabla de resumen
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(120, 7, textoPDF('Título'), 1);
    $pdf->Cell(50, 7, textoPDF('Autor'), 1);
    $pdf->Cell(20, 7, 'Prestamos', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9);
    while ($row = $resumen->fetch_assoc()) {
        $titulo = strlen($row['titulo']) > 70 ? substr($row['titulo'], 0, 67) . '...' : $row['titulo'];
        $autor = strlen($row['autor']) > 30 ? substr($row['autor'], 0, 27) . '...' : $row['autor'];
        
        $pdf->Cell(120, 6, textoPDF($titulo), 1);
        $pdf->Cell(50, 6, textoPDF($autor), 1);
        $pdf->Cell(20, 6, $row['prestamos'], 1, 1, 'C');
    }
    
    $pdf->Ln(15);
    
    // 2. Detalle de préstamos por libro
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, textoPDF('Detalle de Préstamos por Libro'), 0, 1);
    $pdf->Ln(5);
    
    $query_detalle = "SELECT l.titulo, 
                             p.fecha_prestamo,
                             e.ru, e.nombre, e.apellido_paterno, e.apellido_materno, e.carrera,
                             p.lugar
                      FROM prestamo p
                      JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                      JOIN ejemplares ej ON p.id_ejemplar = ej.id_ejemplar
                      JOIN libros l ON ej.id_libro = l.id_libro
                      WHERE p.fecha_prestamo BETWEEN ? AND ?
                      ORDER BY l.titulo, p.fecha_prestamo DESC";
    
    $stmt = $conn->prepare($query_detalle);
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $detalle = $stmt->get_result();
    
    if ($detalle->num_rows > 0) {
        $libro_actual = '';
        while ($row = $detalle->fetch_assoc()) {
            // Mostrar título del libro como sección si cambió
            if ($libro_actual != $row['titulo']) {
                $libro_actual = $row['titulo'];
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 8, textoPDF('Libro: ' . $libro_actual), 0, 1);
                $pdf->Ln(2);
                
                // Encabezados de la tabla
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(25, 7, 'Fecha', 1);
                $pdf->Cell(20, 7, 'Lugar', 1);
                $pdf->Cell(20, 7, 'RU', 1);
                $pdf->Cell(70, 7, textoPDF('Estudiante'), 1);
                $pdf->Cell(55, 7, textoPDF('Carrera'), 1, 1);
            }
            
            $fecha = date('d/m H:i', strtotime($row['fecha_prestamo']));
            $nombre = $row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'];
            
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(25, 6, $fecha, 1);
            $pdf->Cell(20, 6, textoPDF($row['lugar']), 1);
            $pdf->Cell(20, 6, $row['ru'], 1);
            $pdf->Cell(70, 6, textoPDF($nombre), 1);
            $pdf->Cell(55, 6, textoPDF($row['carrera']), 1, 1);
        }
    } else {
        $pdf->Cell(0, 10, textoPDF('No hay datos detallados de préstamos por libro'), 0, 1);
    }

    
}
?>