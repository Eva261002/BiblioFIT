<?php
include('includes/db.php');

if (isset($_POST['registrar'])) {
    $n_inventario = isset($_POST['n_inventario']) ? $_POST['n_inventario'] : '';
    $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
    $autor = isset($_POST['autor']) ? $_POST['autor'] : '';
    $ejemplar = isset($_POST['ejemplar']) ? (int)$_POST['ejemplar'] : 0;
    $año_edicion = isset($_POST['año_edicion']) ? (int)$_POST['año_edicion'] : 0;
    $pais = isset($_POST['pais']) ? $_POST['pais'] : '';
    $categoria = isset($_POST['categoria']) ? $_POST['categoria'] : '';
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';

    // Establecer el estado automáticamente como "disponible"
    $estado = 'disponible';

    // Verifica que los campos obligatorios tengan datos antes de ejecutar la consulta
    if ($n_inventario && $titulo && $autor && $ejemplar && $año_edicion && $pais) {
        $sql = "INSERT INTO libros (n_inventario, titulo, autor, ejemplar, año_edicion, pais, categoria, descripcion, estado) 
                VALUES ('$n_inventario', '$titulo', '$autor', $ejemplar, $año_edicion, '$pais', '$categoria', '$descripcion', '$estado')";
        
        if ($conn->query($sql) === TRUE) {
            // Redirigir al catálogo de libros después de registrar el libro
            header("Location: catalogo_libros.php");
            exit();
        } else {
            echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error al registrar el libro: " . $conn->error . "</div>";
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
    <title>Registro de Libros</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-12">
        <h1 class="text-3xl font-bold text-center mb-6">Registro de Nuevos Libros</h1>

        <div class="flex justify-start mb-6">
            <a href="catalogo_libros.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Volver</a>
        </div>

        <form action="registro_libros.php" method="POST" class="bg-white p-8 rounded-lg shadow-md max-w-lg mx-auto">
            <div class="mb-4">
                <label for="n_inventario" class="block text-sm font-medium text-gray-700">Nº de Inventario</label>
                <input type="text" name="n_inventario" id="n_inventario" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            
            <div class="mb-4">
                <label for="titulo" class="block text-sm font-medium text-gray-700">Título del Libro</label>
                <input type="text" name="titulo" id="titulo" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="autor" class="block text-sm font-medium text-gray-700">Autor</label>
                <input type="text" name="autor" id="autor" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="ejemplar" class="block text-sm font-medium text-gray-700">Ejemplar</label>
                <input type="number" name="ejemplar" id="ejemplar" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="año_edicion" class="block text-sm font-medium text-gray-700">Año de Edición</label>
                <input type="number" name="año_edicion" id="año_edicion" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="pais" class="block text-sm font-medium text-gray-700">País</label>
                <input type="text" name="pais" id="pais" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="categoria" class="block text-sm font-medium text-gray-700">Categoría</label>
                <select name="categoria" id="categoria" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Selecciona una categoría</option>
                    <option value="Programacion">Programacion</option>
                    <option value="Suelos">Suelos</option>
                    <option value="Documento">Documento</option>
                    <option value="Proyecto">Proyecto</option>
                    <option value="Tecnología">Tecnología</option>
                    <option value="Educación">Educación</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
            </div>

            <button type="submit" name="registrar" class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600 w-full">Registrar Libro</button>
        </form>
    </div>
</body>
</html>








 








