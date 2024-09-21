
    function confirmarPrestamo(id_libro) {
        if (confirm('¿Estás seguro de que deseas prestar este libro?')) {
            // Redirigir a prestamo_libros.php con el id_libro
            window.location.href = 'prestamo_libros.php?id_libro=' + id_libro;
        }
    }
  