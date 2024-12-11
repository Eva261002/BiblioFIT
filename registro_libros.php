<?php
include('includes/db.php');

if (isset($_POST['registrar'])) {
    $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
    $autor = isset($_POST['autor']) ? $_POST['autor'] : '';
    $año_edicion = isset($_POST['año_edicion']) ? (int)$_POST['año_edicion'] : 0;
    $pais = isset($_POST['pais']) ? $_POST['pais'] : '';
    $categoria = isset($_POST['categoria']) ? $_POST['categoria'] : '';
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    $ejemplar = isset($_POST['ejemplar']) ? (int)$_POST['ejemplar'] : 0;  // Número de ejemplares
    $n_inventario = isset($_POST['n_inventario']) ? $_POST['n_inventario'] : '';

    // Establecer el estado automáticamente como "disponible" para todos los ejemplares
    $estado = 'disponible';

    // Verifica que los campos obligatorios tengan datos antes de ejecutar la consulta
    if ($titulo && $autor && $año_edicion && $pais && $categoria && $n_inventario && $ejemplar > 0) {
        // Inserta el libro en la tabla 'libros'
        $sql_libro = "INSERT INTO libros (titulo, autor, año_edicion, pais, categoria, descripcion) 
                      VALUES ('$titulo', '$autor', $año_edicion, '$pais', '$categoria', '$descripcion')";
        
        if ($conn->query($sql_libro) === TRUE) {
            // Obtener el ID del libro recién insertado
            $id_libro = $conn->insert_id;

            // Insertar los ejemplares en la tabla 'ejemplares'
            for ($i = 1; $i <= $ejemplar; $i++) {
                // Generar un número de inventario único para cada ejemplar
                $n_inventario_ejemplar = $n_inventario . '-' . $i;
                $sql_ejemplar = "INSERT INTO ejemplares (id_libro, n_inventario, estado) 
                                 VALUES ($id_libro, '$n_inventario_ejemplar', '$estado')";
                if ($conn->query($sql_ejemplar) !== TRUE) {
                    echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error al registrar el ejemplar $i: " . $conn->error . "</div>";
                }
            }

            // Redirigir al catálogo de libros después de registrar el libro y los ejemplares
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
        <label for="titulo" class="block text-sm font-medium text-gray-700">Título del Libro</label>
        <input type="text" name="titulo" id="titulo" required class="mt-1 block w-full">
    </div>
    
    <div class="mb-4">
        <label for="autor" class="block text-sm font-medium text-gray-700">Autor</label>
        <input type="text" name="autor" id="autor" required class="mt-1 block w-full">
    </div>

    <div class="mb-4">
        <label for="año_edicion" class="block text-sm font-medium text-gray-700">Año de Edición</label>
        <input type="number" name="año_edicion" id="año_edicion" required class="mt-1 block w-full">
    </div>

    <div class="mb-4">
        <label for="pais" class="block text-sm font-medium text-gray-700">País</label>
        <input type="text" name="pais" id="pais" required class="mt-1 block w-full">
    </div>

    <div class="mb-4">
        <label for="categoria" class="block text-sm font-medium text-gray-700">Categoría</label>
        <input type="text" name="categoria" id="categoria" required class="mt-1 block w-full">
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
        <label for="ejemplar" class="block text-sm font-medium text-gray-700">Número de Ejemplares</label>
        <input type="number" name="ejemplar" id="ejemplar" required class="mt-1 block w-full">
    </div>

    <button type="submit" name="registrar" class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600 w-full">Registrar Libro</button>
</form>

    </div>
</body>
</html>
