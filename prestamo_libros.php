<?php
include('includes/db.php');

// Establecer la zona horaria correcta
date_default_timezone_set('America/La_Paz'); // Cambia esto por la zona horaria que necesites

// Inicializar variables
$titulo_libro = '';
$ejemplares_disponibles = 0;
$id_libro = null;

// Verificar si se ha proporcionado un id_libro
if (isset($_GET['id'])) {
    $id_libro = intval($_GET['id']);
    // Consulta para obtener el nombre y ejemplares del libro basado en el id_libro
    $sql_libro = "SELECT titulo, ejemplar FROM libros WHERE id_libro = $id_libro";
    $result_libro = $conn->query($sql_libro);
    if ($result_libro->num_rows > 0) {
        $libro = $result_libro->fetch_assoc();
        $titulo_libro = htmlspecialchars($libro['titulo']);
        $ejemplares_disponibles = intval($libro['ejemplar']);
    } else {
        // Manejar el caso en que el libro no exista
        echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error: Libro no encontrado.</div>";
        exit();
    }
}

if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $id_libro = isset($_POST['id_libro']) ? intval($_POST['id_libro']) : null;
    $id_estudiante = isset($_POST['id_estudiante']) ? intval($_POST['id_estudiante']) : null;

    if ($accion === 'prestar' && $id_libro !== null && $id_estudiante !== null) {
        $fecha_prestamo = date('Y-m-d H:i:s');  // Incluye la fecha y hora
        $conn->begin_transaction();
        try {
            // Verificar si el estudiante ya tiene un libro prestado
            $sql_check_prestamo = "SELECT * FROM prestamo WHERE id_estudiante = $id_estudiante AND fecha_devolucion IS NULL";
            $result_prestamo = $conn->query($sql_check_prestamo);

            if ($result_prestamo->num_rows > 0) {
                throw new Exception('El estudiante ya tiene un libro prestado.');
            }

            // Verificar si hay ejemplares disponibles
            $sql_check_ejemplares = "SELECT ejemplar FROM libros WHERE id_libro = $id_libro";
            $result_ejemplares = $conn->query($sql_check_ejemplares);
            $ejemplares = $result_ejemplares->fetch_assoc()['ejemplar'];

            if ($ejemplares <= 0) {
                throw new Exception('No hay ejemplares disponibles para prestar.');
            }

            // Insertar en la tabla de préstamos
            $sql_prestamo = "INSERT INTO prestamo (id_libro, id_estudiante, fecha_prestamo, estado) 
                             VALUES ($id_libro, $id_estudiante, '$fecha_prestamo', 'no disponible')";
            if (!$conn->query($sql_prestamo)) {
                throw new Exception('Error al registrar el préstamo.');
            }

            // Disminuir el número de ejemplares disponibles
            $sql_update_ejemplares = "UPDATE libros SET ejemplar = ejemplar - 1 WHERE id_libro = $id_libro";
            if (!$conn->query($sql_update_ejemplares)) {
                throw new Exception('Error al actualizar el número de ejemplares.');
            }

            // Actualizar el estado del libro si no hay ejemplares disponibles
            $nuevo_estado = ($ejemplares - 1 == 0) ? 'prestado' : 'disponible';
            $sql_libro = "UPDATE libros SET estado = '$nuevo_estado' WHERE id_libro = $id_libro";
            if (!$conn->query($sql_libro)) {
                throw new Exception('Error al actualizar el estado del libro.');
            }

            // Confirmar la transacción
            $conn->commit();
            header('Location: catalogo_libros.php');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamo de Libros</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-12">
        <h1 class="text-3xl font-bold text-center mb-6">Préstamo de Libros</h1>

        <?php if ($id_libro !== null): ?>
            <!-- Mostrar el nombre del libro y ejemplares disponibles -->
            <div class="mb-6">
                <p><strong>Libro a prestar:</strong> <?php echo $titulo_libro; ?></p>
                <p><strong>Ejemplares disponibles:</strong> <?php echo $ejemplares_disponibles; ?></p>
            </div>

            <!-- Barra de búsqueda -->
            <form action="prestamo_libros.php" method="GET" class="mb-6">
                <input type="hidden" name="id" value="<?php echo $id_libro; ?>">
                <input type="text" name="search" placeholder="Buscar estudiante por RU o CI" 
                       class="w-full p-3 border border-gray-300 rounded-md shadow-sm">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 mt-2">Buscar</button>
            </form>

            <?php
            if (isset($_GET['search'])) {
                $search = $conn->real_escape_string($_GET['search']);
                // Consulta para obtener datos del estudiante
                $sql_estudiante = "SELECT * FROM estudiantes WHERE RU LIKE '%$search%' OR CI LIKE '%$search%'";
                $result = $conn->query($sql_estudiante);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $student_id = intval($row['id_estudiante']);
                        // Verificar si el estudiante ya tiene un libro prestado
                        $sql_check_prestamo = "SELECT * FROM prestamo WHERE id_estudiante = $student_id AND fecha_devolucion IS NULL";
                        $result_prestamo = $conn->query($sql_check_prestamo);
                        $has_prestamo = ($result_prestamo->num_rows > 0);

                        echo "<div class='bg-white p-6 rounded-lg shadow-md mb-6'>";
                        echo "<p><strong>RU:</strong> " . htmlspecialchars($row['ru']) . "</p>";
                        echo "<p><strong>CI:</strong> " . htmlspecialchars($row['ci']) . "</p>";
                        echo "<p><strong>Nombre:</strong> " . htmlspecialchars($row['nombre']) . " " . htmlspecialchars($row['apellido_paterno']) . " " . htmlspecialchars($row['apellido_materno']) . "</p>";
                        echo "<p><strong>Carrera:</strong> " . htmlspecialchars($row['carrera']) . "</p>";

                        if ($has_prestamo) {
                            // Obtener detalles del préstamo activo
                            $sql_prestamo_detalle = "SELECT prestamo.fecha_prestamo, libros.titulo 
                                                     FROM prestamo 
                                                     JOIN libros ON prestamo.id_libro = libros.id_libro 
                                                     WHERE prestamo.id_estudiante = $student_id AND prestamo.fecha_devolucion IS NULL";
                            $result_prestamo_detalle = $conn->query($sql_prestamo_detalle);
                            if ($result_prestamo_detalle->num_rows > 0) {
                                $prestamo = $result_prestamo_detalle->fetch_assoc();
                                echo "<p class='text-red-500'><strong>Este estudiante ya tiene un libro prestado:</strong></p>";
                                echo "<p><strong>Título:</strong> " . htmlspecialchars($prestamo['titulo']) . "</p>";
                                echo "<p><strong>Fecha de Préstamo:</strong> " . htmlspecialchars($prestamo['fecha_prestamo']) . "</p>";
                            }
                        }

                        // Botón para prestar el libro
                        echo "<form action='prestamo_libros.php' method='POST' class='mt-4'>";
                        echo "<input type='hidden' name='id_libro' value='$id_libro'>";
                        echo "<input type='hidden' name='id_estudiante' value='" . intval($row['id_estudiante']) . "'>";
                        echo "<input type='hidden' name='accion' value='prestar'>";
                        if (!$has_prestamo) {
                            echo "<button type='submit' class='bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600'>Prestar</button>";
                        } else {
                            echo "<button type='button' class='bg-gray-500 text-white px-4 py-2 rounded-md cursor-not-allowed' disabled>No puede Prestar</button>";
                        }
                        echo "</form>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='text-red-500'>No se encontró ningún estudiante con ese RU o CI.</p>";
                }
            }
            ?>
        <?php endif; ?>

        <!-- Sección de Libros Actualmente Prestados -->
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-center mb-6">Libros Actualmente Prestados</h2>
            <?php
            // Consulta para obtener los libros actualmente prestados
            $sql_prestados = "SELECT p.id_prestamo, l.titulo, e.nombre, e.apellido_paterno, e.apellido_materno, p.fecha_prestamo
                              FROM prestamo p
                              JOIN libros l ON p.id_libro = l.id_libro
                              JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                              WHERE p.fecha_devolucion IS NULL";
            $result_prestados = $conn->query($sql_prestados);

            if ($result_prestados->num_rows > 0) {
                echo "<div class='overflow-x-auto bg-white rounded-lg shadow-md'>";
                echo "<table class='min-w-full bg-white'>";
                echo "<thead class='bg-gray-200 text-gray-600 uppercase text-sm leading-normal'>";
                echo "<tr>";
                echo "<th class='py-3 px-6 text-left'>Título</th>";
                echo "<th class='py-3 px-6 text-left'>Estudiante</th>";
                echo "<th class='py-3 px-6 text-left'>Fecha de Préstamo</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody class='text-gray-600 text-sm font-light'>";
                while ($row = $result_prestados->fetch_assoc()) {
                    $nombre_estudiante = htmlspecialchars($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']);
                    echo "<tr class='border-b border-gray-200 hover:bg-gray-100'>";
                    echo "<td class='py-3 px-6'>" . htmlspecialchars($row['titulo']) . "</td>";
                    echo "<td class='py-3 px-6'>" . $nombre_estudiante . "</td>";
                    echo "<td class='py-3 px-6'>" . htmlspecialchars($row['fecha_prestamo']) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
            } else {
                echo "<p class='text-center text-gray-500'>No hay libros actualmente prestados.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
