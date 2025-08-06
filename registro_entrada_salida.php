<?php
require_once 'includes/config.php'; 

// Obtener el nombre del archivo actual
$current_module = basename($_SERVER['PHP_SELF']);

// Verificar acceso al módulo
verifyModuleAccess($current_module);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Entrada y Salida</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="scripts/registro_entrada_salida.js"></script>

</head>
<body class="bg-gray-100">

    <div class="container mx-auto p-8">

        
        <h2 class="text-3xl font-bold mb-6 text-center">Registro de Entrada y Salida</h2>

        <!-- Buscar Estudiante -->
        <form id="buscar-form" class="mb-6">
            <div class="flex items-center justify-center">
                <input type="text" id="ru" name="ru" class="w-full max-w-xs px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" placeholder="Ingresa RU" required />
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 ml-4 rounded-lg hover:bg-blue-700 transition-colors">Buscar Estudiante</button>
            </div>
        </form>

        <!-- Información del Estudiante -->
        <div id="estudiante-info" class="hidden bg-white p-4 rounded-lg shadow-md">
            <p id="nombre-estudiante" class="text-xl font-semibold mb-2"></p>
            <textarea id="motivo" class="w-full px-4 py-2 border rounded-lg mb-4" rows="1" placeholder="Ingresa el motivo (opcional)" style="resize: vertical; height: auto;"></textarea>
            <div class="flex justify-between">
                <button id="registrar-entrada" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">Registrar Entrada</button>
                <button id="registrar-salida" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">Registrar Salida</button>
            </div>
        </div>

        <!-- Lista de Estudiantes Dentro -->
        <h3 class="text-2xl font-semibold mb-4 mt-8">Estudiantes Actualmente Dentro</h3>
        <div id="lista-estudiantes" class="bg-white p-4 rounded-lg shadow-md">
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse">
                    <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <tr>
                            <th class="px-4 py-2 border">CI</th>
                            <th class="px-4 py-2 border">Nombre Completo</th>
                            <th class="px-4 py-2 border">Carrera</th>
                            <th class="px-4 py-2 border">Hora de Entrada</th>
                            <th class="px-4 py-2 border">Motivo</th>
                        </tr>
                    </thead>
                    <tbody id="estudiantes-dentro" class="text-gray-600 text-sm font-light"></tbody>
                </table>
            </div>
        </div>
    </div>
<script>
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
</script>


<?php include 'includes/footer.php'; ?>
    
   
</body>
</html>
