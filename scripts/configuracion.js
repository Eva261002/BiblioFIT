
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar la página después de cada acción
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });
    });
});
