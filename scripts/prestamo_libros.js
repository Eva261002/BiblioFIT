function confirmarDevolucion(id_prestamo) {
    if (confirm('¿Estás seguro de que deseas devolver este libro?')) {
        devolverLibro(id_prestamo);
    }
}

function devolverLibro(id_prestamo) {
    // Seleccionar la fila correspondiente al préstamo activo
    var filaPrestamo = $('#prestamo-' + id_prestamo);

    // Seleccionar el botón de Devolver y deshabilitarlo para prevenir múltiples clics
    var button = filaPrestamo.find('button');
    button.prop('disabled', true);
    button.text('Devolviendo...');

    $.ajax({
        url: 'devolver_libro.php',
        type: 'POST',
        data: { id_prestamo: id_prestamo, accion: 'devolver' },
        success: function(response) {
            if (response.trim() === 'success') {
                // Remover la fila del préstamo devuelto
                filaPrestamo.fadeOut(500, function() {
                    $(this).remove();
                });
            } else {
                // Mostrar el mensaje de error detallado para depuración
                alert('Error al devolver el libro: ' + response);
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
