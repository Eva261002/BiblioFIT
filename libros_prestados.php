<?php
include('includes/db.php');

// Consulta para obtener todos los préstamos activos
$sql_prestamos_activos = "SELECT prestamo.id_prestamo, libros.titulo, estudiantes.nombre, estudiantes.apellido_paterno, estudiantes.apellido_materno, prestamo.fecha_prestamo 
                          FROM prestamo 
                          JOIN libros ON prestamo.id_libro = libros.id_libro 
                          JOIN estudiantes ON prestamo.id_estudiante = estudiantes.id_estudiante 
                          WHERE prestamo.fecha_devolucion IS NULL";
$result_prestamos = $conn->query($sql_prestamos_activos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libros Prestados</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-12">
        <!-- Botón Volver al Catálogo -->
        <div class="flex justify-start mb-6">
            <a href="catalogo_libros.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Volver al Catálogo</a>
        </div>

        <h1 class="text-3xl font-bold text-center mb-6">Libros Prestados Actualmente</h1>

        <?php
        if ($result_prestamos->num_rows > 0) {
            echo "<div class='overflow-x-auto bg-white rounded-lg shadow'>";
            echo "<table class='min-w-full bg-white'>";
            echo "<thead class='bg-gray-200 text-gray-600 uppercase text-sm leading-normal'>";
            echo "<tr>";
            echo "<th class='py-3 px-6 text-left'>ID Préstamo</th>";
            echo "<th class='py-3 px-6 text-left'>Título del Libro</th>";
            echo "<th class='py-3 px-6 text-left'>Estudiante</th>";
            echo "<th class='py-3 px-6 text-left'>Fecha de Préstamo</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody class='text-gray-600 text-sm font-light'>";

            while ($prestamo = $result_prestamos->fetch_assoc()) {
                echo "<tr class='border-b border-gray-200 hover:bg-gray-100'>";
                echo "<td class='py-3 px-6 text-left'>" . intval($prestamo['id_prestamo']) . "</td>";
                echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($prestamo['titulo']) . "</td>";
                echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($prestamo['nombre'] . " " . $prestamo['apellido_paterno'] . " " . $prestamo['apellido_materno']) . "</td>";
                echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($prestamo['fecha_prestamo']) . "</td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        } else {
            echo "<p>No hay libros actualmente prestados.</p>";
        }
        ?>
    </div>
</body>
</html>
