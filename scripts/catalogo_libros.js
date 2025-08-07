
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


