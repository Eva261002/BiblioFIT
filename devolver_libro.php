<?php
include('includes/db.php');

date_default_timezone_set('America/La_Paz');

// Verificar si se ha enviado el formulario date_default_timezone_set('Etc/GMT-4');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'devolver') {
    $id_prestamo = intval($_POST['id_prestamo']);
    $recibido_por = isset($_POST['recibido_por']) ? $conn->real_escape_string($_POST['recibido_por']) : '';
    $estado_recurso = isset($_POST['estado_recurso']) ? $conn->real_escape_string($_POST['estado_recurso']) : '';

    // Validar que los campos no estén vacíos
    if (empty($recibido_por) || empty($estado_recurso)) {
        echo 'error: Todos los campos son obligatorios.';
        exit();
    }

    // Validar que el estado del recurso sea uno de los permitidos
    $estados_permitidos = ['bueno', 'dañado', 'perdido'];
    if (!in_array($estado_recurso, $estados_permitidos)) {
        echo 'error: Estado del recurso no válido.';
        exit();
    }

    $conn->begin_transaction();
    try {
        // 1. Obtener el id_ejemplar del préstamo
        $sql_prestamo = "SELECT id_ejemplar FROM prestamo WHERE id_prestamo = $id_prestamo";
        $result_prestamo = $conn->query($sql_prestamo);

        if ($result_prestamo->num_rows === 0) {
            throw new Exception('Préstamo no encontrado.');
        }

        $prestamo = $result_prestamo->fetch_assoc();
        $id_ejemplar = intval($prestamo['id_ejemplar']);

        // 2. Insertar la devolución en la tabla `devoluciones`
        $sql_devolucion = "INSERT INTO devoluciones (id_prestamo, recibido_por, estado_recurso) 
                           VALUES ($id_prestamo, '$recibido_por', '$estado_recurso')";
        if (!$conn->query($sql_devolucion)) {
            throw new Exception('Error al registrar la devolución.');
        }

        // 3. Actualizar el estado del ejemplar según el estado del recurso
        $nuevo_estado = ($estado_recurso === 'bueno') ? 'disponible' : $estado_recurso;
        $sql_update_ejemplar = "UPDATE ejemplares SET estado = '$nuevo_estado' WHERE id_ejemplar = $id_ejemplar";
        if (!$conn->query($sql_update_ejemplar)) {
            throw new Exception('Error al actualizar el estado del ejemplar.');
        }

        // 4. Marcar el préstamo como devuelto
        $sql_update_prestamo = "UPDATE prestamo SET estado = 'devuelto', fecha_devolucion = NOW() WHERE id_prestamo = $id_prestamo";
        if (!$conn->query($sql_update_prestamo)) {
            throw new Exception('Error al actualizar el préstamo.');
        }

        // Confirmar la transacción
        $conn->commit();
        
        // Redirigir de vuelta a prestamo_libros.php
        header('Location: prestamo_libros.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo 'error: ' . htmlspecialchars($e->getMessage());
    }
} else {
    // Mostrar el formulario de devolución
    $id_prestamo = isset($_GET['id_prestamo']) ? intval($_GET['id_prestamo']) : null;

    if ($id_prestamo === null) {
        echo 'error: ID de préstamo no proporcionado.';
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devolver Libro - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <!-- Encabezado -->
    <header class="bg-blue-600 shadow">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <!-- Icono de Biblioteca -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
    <main class="container mx-auto px-6 py-12 flex-grow">
        <h1 class="text-3xl font-bold text-center mb-6">Devolver Libro</h1>

        <form action="devolver_libro.php" method="POST" class="bg-white p-6 rounded-lg shadow-md">
            <input type="hidden" name="id_prestamo" value="<?php echo $id_prestamo; ?>">
            <input type="hidden" name="accion" value="devolver">
            <div class="mb-4">
                <label for="recibido_por" class="block text-sm font-medium text-gray-700">Recibido por:</label>
                <input type="text" name="recibido_por" id="recibido_por" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
                <label for="estado_recurso" class="block text-sm font-medium text-gray-700">Estado del Recurso:</label>
                <select name="estado_recurso" id="estado_recurso" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md">
                    <option value="bueno">Bueno</option>
                    <option value="dañado">Dañado</option> 
                    <option value="perdido">Perdido</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Confirmar Devolución</button>
        </form>
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