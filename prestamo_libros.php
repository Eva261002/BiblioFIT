<?php
include('includes/db.php');

if (isset($_GET['id'])) {
    $id_libro = $_GET['id'];
    // Consulta para obtener el nombre del libro basado en el id_libro
    $sql_libro = "SELECT titulo FROM libros WHERE id_libro = $id_libro";
    $result_libro = $conn->query($sql_libro);
    $libro = $result_libro->fetch_assoc();
    $titulo_libro = $libro['titulo'];
}

if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $id_libro = $_POST['id_libro'];
    $id_estudiante = $_POST['id_estudiante'];

    if ($accion === 'prestar') {
        $fecha_prestamo = date('Y-m-d H:i:s');  // Incluye la fecha y hora
        $conn->begin_transaction();
        try {
            // Verificar si el libro ya está prestado
            $sql_check = "SELECT * FROM prestamo WHERE id_libro = $id_libro AND fecha_devolucion IS NULL";
            $result_check = $conn->query($sql_check);
            if ($result_check->num_rows > 0) {
                throw new Exception('El libro ya está prestado.');
            }
     
            // Insertar en la tabla de préstamos
            $sql_prestamo = "INSERT INTO prestamo (id_libro, id_estudiante, fecha_prestamo, estado) 
                             VALUES ($id_libro, $id_estudiante, '$fecha_prestamo', 'no disponible')";
            $conn->query($sql_prestamo);
    
            // Actualizar el estado del libro
            $sql_libro = "UPDATE libros SET estado = 'prestado' WHERE id_libro = $id_libro";
            $conn->query($sql_libro);
    
            // Confirmar la transacción
            $conn->commit();
            header('Location: catalogo_libros.php');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error: " . $e->getMessage() . "</div>";
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

        <!-- Mostrar el nombre del libro -->
        <div class="mb-6">
            <p><strong>Libro a prestar:</strong> <?php echo htmlspecialchars($titulo_libro); ?></p>
        </div>

        <!-- Barra de búsqueda -->
        <form action="prestamo_libros.php" method="GET" class="mb-6">
            <input type="hidden" name="id" value="<?php echo $id_libro; ?>">
            <input type="text" name="search" placeholder="Buscar estudiante por RU o CI" 
                   class="w-full p-3 border border-gray-300 rounded-md shadow-sm">
        </form>

        <?php
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
            // Consulta para obtener datos del estudiante
            $sql_estudiante = "SELECT * FROM estudiantes WHERE RU LIKE '%$search%' OR CI LIKE '%$search%'";
            $result = $conn->query($sql_estudiante);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='bg-white p-6 rounded-lg shadow-md mb-6'>";
                    echo "<p><strong>RU:</strong> " . $row['ru'] . "</p>";
                    echo "<p><strong>CI:</strong> " . $row['ci'] . "</p>";
                    echo "<p><strong>Nombre:</strong> " . $row['nombre'] . " " . $row['apellido_paterno'] . " " . $row['apellido_materno'] . "</p>";
                    echo "<p><strong>Carrera:</strong> " . $row['carrera'] . "</p>";

                    // Botón para prestar el libro
                    echo "<form action='prestamo_libros.php' method='POST' class='mt-4'>";
                    echo "<input type='hidden' name='id_libro' value='$id_libro'>";
                    echo "<input type='hidden' name='id_estudiante' value='" . $row['id_estudiante'] . "'>";
                    echo "<input type='hidden' name='accion' value='prestar'>";
                    echo "<button type='submit' class='bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600'>Prestar</button>";
                    echo "</form>";
                    echo "</div>";
                }
            } else {
                echo "<p class='text-red-500'>No se encontró ningún estudiante con ese RU o CI.</p>";
            }
        }
        ?>
    </div>
</body>
</html>
