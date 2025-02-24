<?php
include('includes/db.php');

date_default_timezone_set('America/La_Paz');

// Inicializar variables
$titulo_libro = '';
$id_libro = isset($_GET['id_libro']) ? intval($_GET['id_libro']) : null;
$id_ejemplar = isset($_GET['id_ejemplar']) ? intval($_GET['id_ejemplar']) : null;

if ($id_libro !== null && $id_ejemplar !== null) {
    // Consulta para obtener el título del libro
    $sql_libro = "SELECT titulo FROM libros WHERE id_libro = $id_libro";
    $result_libro = $conn->query($sql_libro);
    
    if ($result_libro->num_rows > 0) {
        $libro = $result_libro->fetch_assoc();
        $titulo_libro = htmlspecialchars($libro['titulo']);

        // Verificar disponibilidad del ejemplar específico
        $sql_check_ejemplar = "SELECT estado FROM ejemplares WHERE id_ejemplar = $id_ejemplar AND estado = 'disponible'";
        $result_check_ejemplar = $conn->query($sql_check_ejemplar);
        
        if ($result_check_ejemplar->num_rows <= 0) {
            echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error: El ejemplar no está disponible para prestar.</div>";
            exit();
        }
    } else {
        echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error: Libro no encontrado.</div>";
        exit();
    }
}

// ... (El resto del código permanece igual, excepto la referencia a id_ejemplar para prestar)



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $id_libro = isset($_POST['id_libro']) ? intval($_POST['id_libro']) : null;
    $id_estudiante = isset($_POST['id_estudiante']) ? intval($_POST['id_estudiante']) : null;

    if ($accion === 'prestar' && $id_libro !== null && $id_estudiante !== null) {
        $fecha_prestamo = date('Y-m-d H:i:s');
        
        $conn->begin_transaction();
        try {
            // Verificar si el estudiante ya tiene un libro prestado
            $sql_check_prestamo = "SELECT * FROM prestamo WHERE id_estudiante = $id_estudiante AND fecha_devolucion IS NULL";
            $result_prestamo = $conn->query($sql_check_prestamo);

            if ($result_prestamo->num_rows > 0) {
                throw new Exception('El estudiante ya tiene un libro prestado.');
            }

            // Seleccionar un ejemplar disponible para el préstamo
            $sql_check_ejemplares = "SELECT id_ejemplar FROM ejemplares WHERE id_libro = $id_libro AND estado = 'disponible' LIMIT 1";
            $result_ejemplares = $conn->query($sql_check_ejemplares);

            if ($result_ejemplares->num_rows <= 0) {
                throw new Exception('No hay ejemplares disponibles para prestar.');
            }

            // Obtener el ID del ejemplar para el préstamo
            $ejemplar = $result_ejemplares->fetch_assoc();
            $id_ejemplar = intval($ejemplar['id_ejemplar']);

            // Insertar el préstamo en la base de datos
            $sql_prestamo = "INSERT INTO prestamo (id_libro, id_estudiante, fecha_prestamo, estado) 
                             VALUES ($id_libro, $id_estudiante, '$fecha_prestamo', 'prestado')";
            if (!$conn->query($sql_prestamo)) {
                throw new Exception('Error al registrar el préstamo.');
            }

            // Actualizar el estado del ejemplar a "prestado"
            $sql_update_ejemplares = "UPDATE ejemplares SET estado = 'prestado' WHERE id_ejemplar = $id_ejemplar";
            if (!$conn->query($sql_update_ejemplares)) {
                throw new Exception('Error al actualizar el estado del ejemplar.');
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
    <title>Préstamo de Libros - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> 
    <script src="scripts/prestamo_libros.js"></script>
 
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
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

    <!-- Contenido Principal -->
    <main class="container mx-auto px-6 py-12 flex-grow">
       

        <h1 class="text-3xl font-bold text-center mb-6">Préstamo de Libros</h1>

        <?php if ($id_libro !== null): ?>
            <!-- Mostrar el nombre del libro y ejemplares disponibles -->
            <div class="mb-6 bg-white p-6 rounded-lg shadow-md">
                <p class="text-lg"><strong>Libro a prestar:</strong> <?php echo $titulo_libro; ?></p>
                
            </div>

            <!-- Barra de búsqueda -->
            <form action="prestamo_libros.php" method="GET" class="mb-6 bg-white p-6 rounded-lg shadow-md">
                <input type="hidden" name="id_libro" value="<?php echo $id_libro; ?>">
                <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4">
                    <input type="text" name="search" placeholder="Buscar estudiante por RU o CI" 
                           class="w-full md:w-2/3 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-indigo-500">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition-colors">Buscar</button>
                </div>
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
                            echo "<button type='submit' class='bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 transition-colors'>Prestar</button>";
                        } else {
                            echo "<button type='button' class='bg-gray-500 text-white px-4 py-2 rounded-md cursor-not-allowed' disabled>No puede Prestar</button>";
                        }
                        echo "</form>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='text-red-500 text-center'>No se encontró ningún estudiante con ese RU o CI.</p>";
                }
            }
            ?>
        <?php endif; ?>

        <!-- Sección de Libros Actualmente Prestados -->
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-center mb-6">Libros Actualmente Prestados</h2>
            <?php
            // Consulta para obtener los libros actualmente prestados
            $sql_prestados = "SELECT p.id_prestamo, l.id_libro, l.titulo, e.nombre, e.apellido_paterno, e.apellido_materno, p.fecha_prestamo
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
                echo "<th class='py-3 px-6 text-center'>Acciones</th>"; // Nueva columna para Acciones
                echo "</tr>";
                echo "</thead>";
                echo "<tbody class='text-gray-600 text-sm font-light'>";
                while ($row = $result_prestados->fetch_assoc()) {
                    $nombre_estudiante = htmlspecialchars($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']);
                    echo "<tr class='border-b border-gray-200 hover:bg-gray-100' id='prestamo-" . intval($row['id_prestamo']) . "'>";
                    echo "<td class='py-3 px-6'>" . htmlspecialchars($row['titulo']) . "</td>";
                    echo "<td class='py-3 px-6'>" . $nombre_estudiante . "</td>";
                    echo "<td class='py-3 px-6'>" . htmlspecialchars($row['fecha_prestamo']) . "</td>";
                    echo "<td class='py-3 px-6 text-center'>";
                    // Botón de Devolver 
                    echo "<button onclick='confirmarDevolucion(" . intval($row['id_prestamo']) . ")' class='bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600'>Devolver</button>";
                    echo "</td>";
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
    </main>

    <!-- Pie de Página -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto text-center">
            &copy; 2024 Sistema de Biblioteca - FIT-UABJB. Todos los derechos reservados.
        </div>
    </footer>


</body>
</html>

<?php
$conn->close();
?>
