<?php
require_once 'includes/config.php';
verifyModuleAccess(basename($_SERVER['PHP_SELF']));

// Obtener parámetros de búsqueda
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchParam = !empty($search) ? '%' . $search . '%' : '';

// Consulta SQL
$sql = "SELECT * FROM estudiantes";
if (!empty($search)) {
    $sql .= " WHERE nombre LIKE ? OR apellido_paterno LIKE ? OR apellido_materno LIKE ? OR ci LIKE ? OR ru LIKE ? OR carrera LIKE ?";
}

$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $stmt->bind_param("ssssss", $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam);
}
$stmt->execute();
$result = $stmt->get_result();

// Configurar headers para descarga Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="estudiantes_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Crear contenido Excel
echo "<table border='1'>";
echo "<tr>
        <th>CI</th>
        <th>Nombre</th>
        <th>Apellido Paterno</th>
        <th>Apellido Materno</th>
        <th>RU</th>
        <th>Carrera</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . htmlspecialchars($row['ci']) . "</td>
            <td>" . htmlspecialchars($row['nombre']) . "</td>
            <td>" . htmlspecialchars($row['apellido_paterno']) . "</td>
            <td>" . htmlspecialchars($row['apellido_materno']) . "</td>
            <td>" . htmlspecialchars($row['ru']) . "</td>
            <td>" . htmlspecialchars($row['carrera']) . "</td>
          </tr>";
}

echo "</table>";
exit;
?>