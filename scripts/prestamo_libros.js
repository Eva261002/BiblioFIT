function confirmarDevolucion(id_prestamo) {
    console.log("Función confirmarDevolucion llamada con ID:", id_prestamo);
    
    // Mostrar el formulario de devolución
    $('#formulario-devolucion').removeClass('hidden').show();
    
    // Establecer el valor del campo oculto con el ID del préstamo
    $('#id_prestamo').val(id_prestamo);

    // Eliminar cualquier evento anterior para evitar duplicaciones
    $('#form-devolver').off('submit').on('submit', function(e) {
        e.preventDefault();

        // Obtener los datos del formulario
        var formData = $(this).serialize() + '&accion=devolver';

        // Enviar la solicitud AJAX
        $.ajax({
            url: 'devolver_libro.php', // Asegúrate de que esta URL sea correcta
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.trim() === 'success') {
                    // Ocultar el formulario
                    $('#formulario-devolucion').addClass('hidden');
                    // Recargar la página para actualizar la lista de préstamos
                    location.reload();
                } else {
                    alert('Error al devolver el libro: ' + response);
                }
            },
            error: function() {
                alert('Error al devolver el libro.');
            }
        });
    });
}