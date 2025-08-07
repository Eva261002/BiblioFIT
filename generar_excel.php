<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Obtener parámetros del formulario
$tipo_reporte = $_POST['tipo_reporte'] ?? 'asistencia';
$fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');

// Definir consultas detalladas según el tipo de reporte
switch($tipo_reporte) {
    case 'asistencia':
        $query = "SELECT 
                    e.ci as 'CI',
                    e.nombre as 'Nombre',
                    e.apellido_paterno as 'Apellido Paterno',
                    e.apellido_materno as 'Apellido Materno',
                    e.carrera as 'Carrera',
                    e.ru as 'RU',
                    es.hora_entrada as 'Hora Entrada',
                    es.hora_salida as 'Hora Salida',
                    TIMESTAMPDIFF(MINUTE, es.hora_entrada, es.hora_salida) as 'Minutos en Biblioteca'
                 FROM entradas_salidas es
                 JOIN estudiantes e ON es.id_estudiante = e.id_estudiante
                 WHERE es.hora_entrada BETWEEN ? AND ?
                 ORDER BY es.hora_entrada DESC";
        $filename = "detalle_asistencia";
        break;
        
    case 'prestamos':
        $query = "SELECT 
                    p.id_prestamo as 'ID Préstamo',
                    e.ci as 'CI Estudiante',
                    CONCAT(e.nombre, ' ', e.apellido_paterno) as 'Estudiante',
                    e.carrera as 'Carrera',
                    l.titulo as 'Libro',
                    ej.n_inventario as 'N° Inventario',
                    ej.sig_topog as 'Signatura Topográfica',
                    p.fecha_prestamo as 'Fecha Préstamo',
                    p.fecha_devolucion as 'Fecha Devolución',
                    p.lugar as 'Lugar',

                    DATEDIFF(p.fecha_devolucion, p.fecha_prestamo) as 'Días Prestado'
                 FROM prestamo p
                 JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                 JOIN ejemplares ej ON p.id_ejemplar = ej.id_ejemplar
                 JOIN libros l ON ej.id_libro = l.id_libro
                 WHERE p.fecha_prestamo BETWEEN ? AND ?
                 ORDER BY p.fecha_prestamo DESC";
        $filename = "detalle_prestamos";
        break;
        
    case 'libros':
        $query = "SELECT 
                    l.titulo as 'Libro',
                    l.autor as 'Autor',
                    l.año_edicion as 'Año Edición',
                    l.tipo_recurso as 'Tipo Recurso',
                    COUNT(p.id_prestamo) as 'Total Préstamos',
                    GROUP_CONCAT(DISTINCT e.carrera ORDER BY e.carrera SEPARATOR ', ') as 'Carreras que lo pidieron',
                    MAX(p.fecha_prestamo) as 'Último Préstamo'
                 FROM prestamo p
                 JOIN ejemplares ej ON p.id_ejemplar = ej.id_ejemplar
                 JOIN libros l ON ej.id_libro = l.id_libro
                 JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                 WHERE p.fecha_prestamo BETWEEN ? AND ?
                 GROUP BY l.id_libro
                 ORDER BY COUNT(p.id_prestamo) DESC";
        $filename = "detalle_libros";
        break;
}

// Ejecutar consulta
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Error en la preparación de la consulta: ' . $conn->error);
}

$fecha_fin_completa = $fecha_fin . ' 23:59:59';
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin_completa);
$stmt->execute();
$resultados = $stmt->get_result();

// Configurar headers para descarga
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Crear contenido HTML que Excel interpretará
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
      <head>
      <!--[if gte mso 9]>
      <xml>
        <x:ExcelWorkbook>
          <x:ExcelWorksheets>
            <x:ExcelWorksheet>
              <x:Name>' . htmlspecialchars($tipo_reporte) . '</x:Name>
              <x:WorksheetOptions>
                <x:DisplayGridlines/>
              </x:WorksheetOptions>
            </x:ExcelWorksheet>
          </x:ExcelWorksheets>
        </x:ExcelWorkbook>
      </xml>
      <![endif]-->
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <style>
        .text { mso-number-format:\@; }
        .number { mso-number-format:0; }
        .date { mso-number-format:"dd\/mm\/yyyy hh:mm:ss"; }
        .header { background:#004684; color:#FFF; font-weight:bold; }
      </style>
      </head>
      <body>';

// Tabla con los resultados
echo '<table border="1" cellspacing="0" cellpadding="3">';

// Título del reporte
echo '<tr><th colspan="' . $resultados->field_count . '" class="header" style="font-size:14pt;">Reporte Detallado de ' . ucfirst($tipo_reporte) . '</th></tr>';
echo '<tr><th colspan="' . $resultados->field_count . '">Período: ' . $fecha_inicio . ' al ' . $fecha_fin . '</th></tr>';

// Encabezados de columnas
echo '<tr>';
while ($field = $resultados->fetch_field()) {
    echo '<th class="header">' . htmlspecialchars($field->name) . '</th>';
}
echo '</tr>';

// Datos detallados
while ($row = $resultados->fetch_assoc()) {
    echo '<tr>';
    foreach ($row as $key => $value) {
        // Determinar el tipo de formato según el campo
        $class = 'text';
        if (is_numeric($value) && !in_array(strtolower($key), ['ci', 'ru', 'n° inventario'])) {
            $class = 'number';
        } elseif (strtotime($value) !== false && preg_match('/fecha|hora|entrada|salida|préstamo|devolución/i', $key)) {
            $class = 'date';
        }
        echo '<td class="' . $class . '">' . htmlspecialchars($value) . '</td>';
    }
    echo '</tr>';
}

echo '</table>';

// Agregar resumen al final
if($resultados->num_rows > 0) {
    echo '<div style="margin-top:20px; font-weight:bold;">Total registros: ' . $resultados->num_rows . '</div>';
}

echo '</body></html>';
exit;