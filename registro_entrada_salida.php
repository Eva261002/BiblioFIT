
<?php
require_once 'includes/config.php'; 
$current_module = basename($_SERVER['PHP_SELF']);
verifyModuleAccess($current_module);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Entrada y Salida</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="scripts/prestamo_libros.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h2 class="text-3xl font-bold mb-6 text-center">Registro de Entrada y Salida</h2>

        <!-- Buscar Estudiante -->
        <form id="buscar-form" class="mb-6">
            <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                <div class="w-full md:w-auto">
                    <select id="tipo-busqueda" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500">
                        <option value="ru">Buscar por RU</option>
                        <option value="ci">Buscar por CI</option>
                    </select>
                </div>
                <div class="w-full md:w-auto">
                    <input type="text" id="valor-busqueda" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-indigo-500" placeholder="Ingresa RU o CI" required />
                </div>
                <button type="submit" class="w-full md:w-auto bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Buscar Estudiante
                </button>
            </div>
        </form>

        <!-- Información del Estudiante -->
        <div id="estudiante-info" class="hidden bg-white p-4 rounded-lg shadow-md mb-8">
            <p id="nombre-estudiante" class="text-xl font-semibold mb-2"></p>
            <textarea id="motivo" class="w-full px-4 py-2 border rounded-lg mb-4" rows="1" placeholder="Ingresa el motivo (opcional)" style="resize: vertical; height: auto;"></textarea>
            <div class="flex justify-between">
                <button id="registrar-entrada" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>Registrar Entrada
                </button>
                <button id="registrar-salida" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>Registrar Salida
                </button>
            </div>
        </div>

        <!-- Lista de Estudiantes Dentro -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-semibold">Estudiantes Actualmente Dentro</h3>
                <button onclick="cargarEstudiantesDentro()" class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 transition-colors">
                    <i class="fas fa-sync-alt mr-1"></i>Actualizar
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse">
                    <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <tr>
                            <th class="px-4 py-2 border">CI</th>
                            <th class="px-4 py-2 border">Nombre Completo</th>
                            <th class="px-4 py-2 border">Carrera</th>
                            <th class="px-4 py-2 border">Hora de Entrada</th>
                            <th class="px-4 py-2 border">Motivo</th>
                            <th class="px-4 py-2 border">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="estudiantes-dentro" class="text-gray-600 text-sm font-light"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-xl font-bold mb-4" id="modalTitle">Confirmar Salida</h3>
            <p id="modalMessage">¿Estás seguro que deseas registrar la salida de este estudiante?</p>
            <div class="flex justify-end mt-6 space-x-3">
                <button onclick="document.getElementById('confirmModal').classList.add('hidden')" 
                        class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100 transition-colors">
                    Cancelar
                </button>
                <button id="confirmAction" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <script>
    // Variable global para almacenar el ID del estudiante seleccionado
    let currentStudentId = null;

    document.getElementById("buscar-form").addEventListener("submit", function(event) {
        event.preventDefault();
        const tipoBusqueda = document.getElementById("tipo-busqueda").value;
        const valorBusqueda = document.getElementById("valor-busqueda").value;

        fetch("procesar_registro.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({ 
                tipo_busqueda: tipoBusqueda,
                valor_busqueda: valorBusqueda,
                accion: "buscar" 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                currentStudentId = data.id_estudiante;
                document.getElementById("estudiante-info").classList.remove("hidden");
                document.getElementById("nombre-estudiante").textContent = `${data.nombre} ${data.apellido_paterno} ${data.apellido_materno}`;

                if (data.estado === 0) {
                    document.getElementById("registrar-salida").style.display = "inline-block";
                    document.getElementById("motivo").style.display = "none";
                    document.getElementById("registrar-entrada").style.display = "none";
                } else {
                    document.getElementById("registrar-entrada").style.display = "inline-block";
                    document.getElementById("motivo").style.display = "block";
                    document.getElementById("registrar-salida").style.display = "none";
                }

                document.getElementById("registrar-entrada").onclick = function() {
                    const motivo = document.getElementById("motivo").value;
                    registrarAccion(data.id_estudiante, "registrar_entrada", motivo);
                };

                document.getElementById("registrar-salida").onclick = function() {
                    showConfirmModal(data.id_estudiante, "registrar_salida");
                };
            }
        });
    });

    function showConfirmModal(studentId, action) {
        currentStudentId = studentId;
        const modal = document.getElementById("confirmModal");
        document.getElementById("modalTitle").textContent = "Confirmar Salida";
        document.getElementById("modalMessage").textContent = "¿Estás seguro que deseas registrar la salida de este estudiante?";
        
        document.getElementById("confirmAction").onclick = function() {
            registrarAccion(studentId, action);
            modal.classList.add("hidden");
        };
        
        modal.classList.remove("hidden");
    }

    function registrarAccion(idEstudiante, accion, motivo = '') {
        fetch("procesar_registro.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({ 
                id_estudiante: idEstudiante, 
                accion: accion, 
                motivo: motivo 
            })
        })
        .then(data => {
            if (data.error) {
                showNotification(data.error); // O mantener alert para errores
            } else {
                if (accion === 'registrar_entrada') {
                    showNotification("Entrada registrada exitosamente");
                }
                cargarEstudiantesDentro();
                document.getElementById("estudiante-info").classList.add("hidden");
                document.getElementById("valor-busqueda").value = "";
            }
        });
        
    }

    function showNotification(message) {
    const notification = document.getElementById("notification");
    const messageElement = document.getElementById("notification-message");
    
    messageElement.textContent = message;
    notification.classList.remove("hidden");
    notification.classList.add("flex", "items-center");
    
    setTimeout(() => {
        notification.classList.add("hidden");
    }, 3000);
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
                    <td class="px-4 py-2 border">${estudiante.motivo || '-'}</td>
                    <td class="px-4 py-2 border text-center">
                        <button onclick="showConfirmModal(${estudiante.id_estudiante}, 'registrar_salida')" 
                                class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition-colors">
                            <i class="fas fa-sign-out-alt mr-1"></i>Salida
                        </button>
                    </td>`;
                tbody.appendChild(tr);
            });
        });
    }
    
    // Cargar la lista de estudiantes dentro al inicio
    cargarEstudiantesDentro();
    </script>

<!-- Alerta de confirmacion -->
<div id="notification" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg hidden">
    <i class="fas fa-check-circle mr-2"></i>
    <span id="notification-message"></span>
</div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>