<?php
include('includes/db.php');

if (isset($_POST['registrar'])) {
    $tipo_recurso = isset($_POST['tipo_recurso']) ? $_POST['tipo_recurso'] : '';
    $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
    $autor = isset($_POST['autor']) ? $_POST['autor'] : '';
    $año_edicion = isset($_POST['año_edicion']) ? (int)$_POST['año_edicion'] : 0;
    $pais = isset($_POST['pais']) ? $_POST['pais'] : '';
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    $ejemplar = isset($_POST['ejemplar']) ? (int)$_POST['ejemplar'] : 0;  // Número de ejemplares
    $n_inventario = isset($_POST['n_inventario']) ? $_POST['n_inventario'] : '';
    $sig_topog = isset($_POST['sig_topog']) ? $_POST['sig_topog'] : '';  // Nuevo campo

    // Establecer el estado automáticamente como "disponible" para todos los ejemplares
    $estado = 'disponible';

    // Verifica que los campos obligatorios tengan datos antes de ejecutar la consulta
    if ($tipo_recurso && $titulo && $n_inventario && $ejemplar > 0) {
        // Inserta el recurso en la tabla 'libros'
        $sql_recurso = "INSERT INTO libros (titulo, autor, año_edicion, pais, tipo_recurso, descripcion) 
                      VALUES ('$titulo', '$autor', $año_edicion, '$pais', '$tipo_recurso', '$descripcion')";
        
        if ($conn->query($sql_recurso) === TRUE) {
            // Obtener el ID del recurso recién insertado
            $id_libro = $conn->insert_id;

            // Insertar los ejemplares en la tabla 'ejemplares'
            for ($i = 1; $i <= $ejemplar; $i++) {
                // Generar un número de inventario único para cada ejemplar
                $n_inventario_ejemplar = $n_inventario . '-' . $i;
                $sql_ejemplar = "INSERT INTO ejemplares (id_libro, n_inventario, sig_topog, estado) 
                                 VALUES ($id_libro, '$n_inventario_ejemplar', '$sig_topog', '$estado')";
                if ($conn->query($sql_ejemplar) !== TRUE) {
                    echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error al registrar el ejemplar $i: " . $conn->error . "</div>";
                }
            }

            // Redirigir al catálogo de libros después de registrar el recurso y los ejemplares
            header("Location: catalogo_libros.php");
            exit();
        } else {
            echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error al registrar el recurso: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Por favor, completa todos los campos obligatorios.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Recursos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-12">
        <h1 class="text-3xl font-bold text-center mb-6">Registro de Nuevos Recursos</h1>

        <div class="flex justify-start mb-6">
            <a href="catalogo_libros.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Volver</a>
        </div>

        <form action="registro_recursos.php" method="POST" class="bg-white p-8 rounded-lg shadow-md max-w-lg mx-auto">
            <div class="mb-4">
                <label for="tipo_recurso" class="block text-sm font-medium text-gray-700">Tipo de Recurso</label>
                <select name="tipo_recurso" id="tipo_recurso" required class="mt-1 block w-full">
                    <option value="Libro">Libro</option>
                    <option value="Tesis/Proyecto">Tesis/Proyecto</option>
                    <option value="Revista">Revista</option>
                    <option value="Equipo">Equipo</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="titulo" class="block text-sm font-medium text-gray-700">Título</label>
                <input type="text" name="titulo" id="titulo" required class="mt-1 block w-full">
            </div>

            <div class="mb-4">
                <label for="autor" class="block text-sm font-medium text-gray-700">Autor</label>
                <input type="text" name="autor" id="autor" class="mt-1 block w-full">
            </div>

            <div class="mb-4">
                <label for="año_edicion" class="block text-sm font-medium text-gray-700">Año de Edición</label>
                <input type="number" name="año_edicion" id="año_edicion" class="mt-1 block w-full">
            </div>

            <div class="mb-4">
                <label for="pais" class="block text-sm font-medium text-gray-700">País</label>
                <input type="text" name="pais" id="pais" class="mt-1 block w-full">
            </div>

            <div class="mb-4">
                <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="4" class="mt-1 block w-full"></textarea>
            </div>

            <div class="mb-4">
                <label for="n_inventario" class="block text-sm font-medium text-gray-700">Nº de Inventario Base</label>
                <input type="text" name="n_inventario" id="n_inventario" required class="mt-1 block w-full">
            </div>

            <div class="mb-4">
                <label for="sig_topog" class="block text-sm font-medium text-gray-700">Signatura Topográfica</label>
                <input type="text" name="sig_topog" id="sig_topog" class="mt-1 block w-full">
            </div>

            <div class="mb-4">
                <label for="ejemplar" class="block text-sm font-medium text-gray-700">Número de Ejemplares</label>
                <input type="number" name="ejemplar" id="ejemplar" required class="mt-1 block w-full">
            </div>

            <button type="submit" name="registrar" class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600 w-full">Registrar Recurso</button>
        </form>
    </div>
</body>
</html>