
    function validarRegistro() {
        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirm_password").value;

        // Validación de correo
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert("Por favor, ingresa un correo electrónico válido.");
            return false;
        }

        // Validación de contraseña
        if (password.length < 8 || !/[A-Z]/.test(password) || !/[0-9]/.test(password) || !/[!@#$%^&*.,;:_\-+=]/.test(password)) {
            alert("La contraseña debe tener al menos 8 caracteres, una letra mayúscula, un número y un carácter especial.");
            return false;
        }

        // Verificación de coincidencia de contraseñas
        if (password !== confirmPassword) {
            alert("Las contraseñas no coinciden.");
            return false;
        }

        return true;
    }



    