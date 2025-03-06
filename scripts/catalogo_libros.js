function confirmarPrestamo(id_libro) {
    if (confirm('¿Estás seguro de que deseas prestar este libro?')) {
        // Redirigir a prestamo_libros.php con el id_libro
        window.location.href = 'prestamo_libros.php?id_libro=' + id_libro;
    }
}

    document.addEventListener('DOMContentLoaded', function () {
        // Seleccionar todos los íconos de información
        const infoIcons = document.querySelectorAll('.info-icon');

        infoIcons.forEach(icon => {
            icon.addEventListener('mouseenter', function () {
                // Mostrar el tooltip correspondiente
                const tooltip = this.nextElementSibling;
                tooltip.classList.remove('hidden');
                tooltip.classList.add('block');
            });

            icon.addEventListener('mouseleave', function () {
                // Ocultar el tooltip
                const tooltip = this.nextElementSibling;
                tooltip.classList.remove('block');
                tooltip.classList.add('hidden');
            });
        });
    });
