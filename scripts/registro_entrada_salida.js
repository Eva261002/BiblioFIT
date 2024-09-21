document.getElementById("buscar-form").addEventListener("submit", function(event) {
    event.preventDefault();
    const ru = document.getElementById("ru").value;

    fetch("procesar_registro.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({ ru: ru, accion: "buscar" })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
        } else {
            // Mostrar la información del estudiante
            document.getElementById("estudiante-info").classList.remove("hidden");
            document.getElementById("nombre-estudiante").textContent = `${data.nombre} ${data.apellido_paterno} ${data.apellido_materno}`;

            // Verificar el estado del estudiante (dentro o fuera de la biblioteca)
            if (data.estado === 0) {
                // Estudiante está dentro: mostrar botón "Registrar Salida" y ocultar el campo de "Motivo"
                document.getElementById("registrar-salida").style.display = "inline-block";
                document.getElementById("motivo").style.display = "none";
                document.getElementById("registrar-entrada").style.display = "none";
            } else {
                // Estudiante está fuera: mostrar botón "Registrar Entrada" y campo de "Motivo"
                document.getElementById("registrar-entrada").style.display = "inline-block";
                document.getElementById("motivo").style.display = "block";
                document.getElementById("registrar-salida").style.display = "none";
            }

            // Registrar Entrada
            document.getElementById("registrar-entrada").onclick = function() {
                const motivo = document.getElementById("motivo").value;
                registrarAccion(ru, "registrar_entrada", motivo);
            };

            // Registrar Salida
            document.getElementById("registrar-salida").onclick = function() {
                registrarAccion(ru, "registrar_salida");
            };
        }
    });
});

function registrarAccion(ru, accion, motivo = '') {
    fetch("procesar_registro.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({ ru: ru, accion: accion, motivo: motivo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
        } else {
            alert(data.success);
            // Actualizar la lista de estudiantes dentro
            cargarEstudiantesDentro();
            // Limpiar la información del estudiante
            document.getElementById("estudiante-info").classList.add("hidden");
        }
    });
}

function cargarEstudiantesDentro() {
    fetch("procesar_registro.php", {
        method: "GET"
    })
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById("estudiantes-dentro");
        tbody.innerHTML = "";
        data.forEach(estudiante => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td class="px-4 py-2 border">${estudiante.ci}</td>
                <td class="px-4 py-2 border">${estudiante.nombre_completo}</td>
                <td class="px-4 py-2 border">${estudiante.carrera}</td>
                <td class="px-4 py-2 border">${estudiante.hora_entrada}</td>
                <td class="px-4 py-2 border">${estudiante.motivo || '-'}</td>`;
            tbody.appendChild(tr);
        });
    });
}

// Cargar la lista de estudiantes dentro al inicio
cargarEstudiantesDentro();