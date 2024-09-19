<?php
include('includes/db.php');

// Filtro de búsqueda
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $conn->real_escape_string($_GET['search']);
}

// Consulta actualizada
$sql = "SELECT * FROM libros WHERE titulo LIKE '%$search_query%' OR autor LIKE '%$search_query%' OR categoria LIKE '%$search_query%'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Libros</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> 
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-12">
        <!-- Botón Volver al Inicio -->
        <div class="flex justify-start mb-6">
            <a href="index.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Volver al Inicio</a>
        </div>

        <h1 class="text-3xl font-bold text-center mb-6">Catálogo de Libros</h1>

        <!-- Filtro de Búsqueda -->
        <form action="catalogo_libros.php" method="GET" class="mb-6">
            <input type="text" name="search" placeholder="Buscar por título, autor o categoría" value="<?php echo htmlspecialchars($search_query); ?>" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm w-full sm:w-1/3">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 ml-2">Buscar</button>
        </form>

           <!-- Botones de acción -->
           <div class="flex justify-end mb-6">
            <a href="registro_libros.php" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 mr-4">Agregar Libro</a>
            <a href="prestamo_libros.php" class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600">Ver Libros Prestados</a>
        </div>

        <!-- Tabla de Catálogo -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <tr>
                        <th class="py-3 px-6 text-left">Título</th>
                        <th class="py-3 px-6 text-left">Autor</th>
                        <th class="py-3 px-6 text-left">Ejemplar</th>
                        <th class="py-3 px-6 text-left">Año de Edición</th>
                        <th class="py-3 px-6 text-left">País</th>
                        <th class="py-3 px-6 text-left">Categoría</th>
                        <th class="py-3 px-6 text-left">Estado</th>
                        <th class="py-3 px-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-100" id="libro-<?php echo intval($row['id_libro']); ?>">
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['titulo']); ?></td>
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['autor']); ?></td>
                        <td class="py-3 px-6 text-left"><?php echo intval($row['ejemplar']); ?></td>
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['año_edicion']); ?></td>
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['pais']); ?></td>
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['categoria']); ?></td>
                        <td class="py-3 px-6 text-left" id="estado-<?php echo intval($row['id_libro']); ?>">
                            <?php if($row['estado'] == 'disponible'): ?>
                                <span class="bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs">Disponible</span>
                            <?php elseif($row['estado'] == 'prestado'): ?>
                                <span class="bg-red-200 text-red-600 py-1 px-3 rounded-full text-xs">Prestado</span>
                            <?php endif; ?>
                        </td>

                        <td class="py-3 px-6 text-center">
                            <!-- Botón de Prestar siempre visible -->
                            <button onclick="confirmarPrestamo(<?php echo intval($row['id_libro']); ?>)" class="bg-yellow-500 text-white px-3 py-1 rounded-md hover:bg-yellow-600 mr-2 text-sm">Prestar</button>
                            <!-- Botón de Devolver siempre visible -->
                            <button onclick="confirmarDevolucion(<?php echo intval($row['id_libro']); ?>)" class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 text-sm">Devolver</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function confirmarPrestamo(id_libro) {
        if (confirm('¿Estás seguro de que deseas prestar este libro?')) {
            window.location.href = 'prestamo_libros.php?id=' + id_libro;
        }
    }

    function confirmarDevolucion(id_libro) {
        if (confirm('¿Estás seguro de que deseas devolver este libro?')) {
            devolverLibro(id_libro);
        }
    }

    function devolverLibro(id_libro) {
        // Seleccionar el botón de Devolver y deshabilitarlo para prevenir múltiples clics
        var button = $('#libro-' + id_libro).find('button').filter(function() {
            return $(this).text() === 'Devolver';
        });
        button.prop('disabled', true);
        button.text('Devolviendo...');

        $.ajax({
            url: 'devolver_libro.php',
            type: 'POST',
            data: { id_libro: id_libro },
            success: function(response) {
                if (response.trim() === 'success') {
                    // Actualizar el estado del libro en la tabla
                    $('#estado-' + id_libro).html('<span class="bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs">Disponible</span>');
                    
                    // Incrementar el número de ejemplares disponibles
                    var ejemplarCell = $('#libro-' + id_libro).find('td:nth-child(3)');
                    var ejemplares = parseInt(ejemplarCell.text()) + 1;
                    ejemplarCell.text(ejemplares);

                    // Actualizar el estado del libro si es necesario
                    if (ejemplares > 0) {
                        // Si hay ejemplares disponibles, el estado es 'disponible'
                        $('#estado-' + id_libro).html('<span class="bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs">Disponible</span>');
                    }

                    // Rehabilitar el botón de Devolver
                    button.prop('disabled', false);
                    button.text('Devolver');
                } else {
                    alert('Error al devolver el libro.');
                    // Rehabilitar el botón de Devolver en caso de error
                    button.prop('disabled', false);
                    button.text('Devolver');
                }
            },
            error: function() {
                alert('Error al devolver el libro.');
                // Rehabilitar el botón de Devolver en caso de error
                button.prop('disabled', false);
                button.text('Devolver');
            }
        });
    }
    </script>
</body>
</html>
