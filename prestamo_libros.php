<?php
include('includes/db.php');
include ('includes/header.php'); 


date_default_timezone_set('America/La_Paz');

// Determinar el modo de la página (prestar o devolver)
$modo = isset($_GET['modo']) ? $_GET['modo'] : 'prestar';

// Inicializar variables
$titulo_libro = '';
$id_libro = isset($_GET['id_libro']) ? intval($_GET['id_libro']) : null;
$id_ejemplar = isset($_GET['id_ejemplar']) ? intval($_GET['id_ejemplar']) : null;

// Si estamos en modo "prestar", validar el libro y el ejemplar
if ($modo === 'prestar' && $id_libro !== null && $id_ejemplar !== null) {
    // Consulta para obtener el título del libro
    $sql_libro = "SELECT titulo FROM libros WHERE id_libro = $id_libro";
    $result_libro = $conn->query($sql_libro);
    
    if ($result_libro->num_rows > 0) {
        $libro = $result_libro->fetch_assoc();
        $titulo_libro = htmlspecialchars($libro['titulo']);

        // Verificar disponibilidad del ejemplar específico
        $sql_check_ejemplar = "SELECT estado FROM ejemplares WHERE id_ejemplar = $id_ejemplar";
        $result_check_ejemplar = $conn->query($sql_check_ejemplar);
        
        if ($result_check_ejemplar->num_rows > 0) {
            $ejemplar = $result_check_ejemplar->fetch_assoc();
            $estado_ejemplar = $ejemplar['estado'];

            // Verificar si el ejemplar está disponible
            if ($estado_ejemplar !== 'disponible') {
                echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error: El ejemplar no está disponible para prestar.</div>";
                exit();
            }
        } else {
            echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error: Ejemplar no encontrado.</div>";
            exit();
        }
    } else {
        echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error: Rcurso no encontrado.</div>";
        exit();
    }
}

// Procesar préstamos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $id_ejemplar = isset($_POST['id_ejemplar']) ? intval($_POST['id_ejemplar']) : null;
    $id_estudiante = isset($_POST['id_estudiante']) ? intval($_POST['id_estudiante']) : null;
    $lugar = isset($_POST['lugar']) ? $_POST['lugar'] : 'sala';
    $telefono = isset($_POST['telefono']) ? $_POST['telefono'] : null;

    // Validar que el lugar sea uno de los permitidos
    $lugaresPermitidos = ['sala', 'domicilio', 'fotocopia'];
    if (!in_array($lugar, $lugaresPermitidos)) {
        die("Lugar de préstamo no válido");
    }

    // Validar teléfono si es necesario
    if (in_array($lugar, ['domicilio', 'fotocopia'])) {
        if (empty($telefono)) {
            die("El número de celular es requerido para este tipo de préstamo");
        }
        if (!preg_match('/^[0-9]{8}$/', $telefono)) {
            die("El número de celular debe tener 8 dígitos");
        }
    }

    $fecha_prestamo = date('Y-m-d H:i:s');

    $conn->begin_transaction();
    try {
        // 1. Verificar si el estudiante ya tiene un libro prestado
        $sql_check_prestamo = "SELECT * FROM prestamo WHERE id_estudiante = $id_estudiante AND estado = 'en curso'";
        $result_prestamo = $conn->query($sql_check_prestamo);

        if ($result_prestamo->num_rows > 0) {
            throw new Exception('El estudiante ya tiene un recurso prestado.');
        }

        // 2. Verificar si el ejemplar está disponible
        $sql_check_ejemplar = "SELECT estado FROM ejemplares WHERE id_ejemplar = $id_ejemplar";
        $result_ejemplar = $conn->query($sql_check_ejemplar);

        if ($result_ejemplar->num_rows === 0) {
            throw new Exception('Ejemplar no encontrado.');
        }

        $ejemplar = $result_ejemplar->fetch_assoc();
        $estado_ejemplar = $ejemplar['estado'];

        if ($estado_ejemplar !== 'disponible') {
            throw new Exception('El ejemplar no está disponible para prestar.');
        }

        // 3. Actualizar teléfono del estudiante si es necesario
        if (in_array($lugar, ['domicilio', 'fotocopia'])) {
            $sql_update_telefono = "UPDATE estudiantes SET celular = '$telefono' WHERE id_estudiante = $id_estudiante";
            if (!$conn->query($sql_update_telefono)) {
                throw new Exception('Error al actualizar el teléfono del estudiante.');
            }
        }

        // 4. Insertar el préstamo
        $sql_prestamo = "INSERT INTO prestamo (id_ejemplar, id_estudiante, fecha_prestamo, estado, lugar) 
                        VALUES ($id_ejemplar, $id_estudiante, '$fecha_prestamo', 'en curso', '$lugar')";
        if (!$conn->query($sql_prestamo)) {
            throw new Exception('Error al registrar el préstamo.');
        }

        // 5. Actualizar el estado del ejemplar
        $sql_update_ejemplar = "UPDATE ejemplares SET estado = 'prestado' WHERE id_ejemplar = $id_ejemplar";
        if (!$conn->query($sql_update_ejemplar)) {
            throw new Exception('Error al actualizar el estado del ejemplar.');
        }

        $conn->commit();
        header('Location: catalogo_libros.php?prestamo=exitoso');
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='bg-red-200 text-red-700 p-4 rounded'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
} // Este cierra el if inicial del POST
?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $modo === 'prestar' ? 'Préstamo' : 'Devolución'; ?> de Libros - Sistema de Biblioteca</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> 
        <script src="scripts/prestamo_libros.js"></script>
    </head>
    <body class="bg-gray-100 flex flex-col min-h-screen">


        <main class="container mx-auto px-6 py-12 flex-grow">
            <h1 class="text-3xl font-bold text-center mb-6"><?php echo $modo === 'prestar' ? 'Préstamo' : 'Devolución'; ?> de Libros</h1>

            <?php if ($modo === 'prestar' && $id_libro !== null): ?>
                <!-- Mostrar el nombre del libro y ejemplares disponibles -->
                <div class="mb-6 bg-white p-6 rounded-lg shadow-md">
                    <p class="text-lg"><strong>Libro a prestar:</strong> <?php echo $titulo_libro; ?></p>
                </div>

                <!-- Barra de búsqueda de estudiantes succ-->
                <form action="prestamo_libros.php" method="GET" class="mb-6 bg-white p-6 rounded-lg shadow-md">
                    <input type="hidden" name="id_libro" value="<?php echo $id_libro; ?>">
                    <input type="hidden" name="id_ejemplar" value="<?php echo $id_ejemplar; ?>">
                    <input type="hidden" name="modo" value="prestar">
                    <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4">
                        <input type="text" name="search" placeholder="Buscar estudiante por RU o CI" 
                            class="w-full md:w-2/3 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-indigo-500">
                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition-colors">Buscar</button>
                    </div>
                </form>

                <?php
                if (isset($_GET['search'])) {
                    $search = $conn->real_escape_string($_GET['search']);
                    // Consulta para obtener datos del estudiante
                    $sql_estudiante = "SELECT * FROM estudiantes WHERE RU LIKE '%$search%' OR CI LIKE '%$search%'";
                    $result = $conn->query($sql_estudiante);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $student_id = intval($row['id_estudiante']);
                            // Verificar si el estudiante ya tiene un libro prestado
                            $sql_check_prestamo = "SELECT * FROM prestamo WHERE id_estudiante = $student_id AND estado = 'en curso'";
                            $result_prestamo = $conn->query($sql_check_prestamo);
                            $has_prestamo = ($result_prestamo->num_rows > 0);

                            echo "<div class='bg-white p-6 rounded-lg shadow-md mb-6'>";
                            echo "<p><strong>RU:</strong> " . htmlspecialchars($row['ru']) . "</p>";
                            echo "<p><strong>CI:</strong> " . htmlspecialchars($row['ci']) . "</p>";
                            echo "<p><strong>Nombre:</strong> " . htmlspecialchars($row['nombre']) . " " . htmlspecialchars($row['apellido_paterno']) . " " . htmlspecialchars($row['apellido_materno']) . "</p>";
                            echo "<p><strong>Carrera:</strong> " . htmlspecialchars($row['carrera']) . "</p>";

                            if ($has_prestamo) {
                                // Obtener detalles del préstamo activo
                                $sql_prestamo_detalle = "
                                    SELECT prestamo.fecha_prestamo, libros.titulo 
                                    FROM prestamo 
                                    JOIN ejemplares ON prestamo.id_ejemplar = ejemplares.id_ejemplar 
                                    JOIN libros ON ejemplares.id_libro = libros.id_libro 
                                    WHERE prestamo.id_estudiante = $student_id AND prestamo.estado = 'en curso'
                                ";
                                $result_prestamo_detalle = $conn->query($sql_prestamo_detalle);
                                if ($result_prestamo_detalle->num_rows > 0) {
                                    $prestamo = $result_prestamo_detalle->fetch_assoc();
                                    echo "<p class='text-red-500'><strong>Este estudiante ya tiene un libro prestado:</strong></p>";
                                    echo "<p><strong>Título:</strong> " . htmlspecialchars($prestamo['titulo']) . "</p>";
                                    echo "<p><strong>Fecha de Préstamo:</strong> " . htmlspecialchars($prestamo['fecha_prestamo']) . "</p>";
                                }
                            }
                            // Botón para prestar el libro
                            echo "<form action='prestamo_libros.php' method='POST' class='mt-4'>";
                            echo "<input type='hidden' name='id_libro' value='$id_libro'>";
                            echo "<input type='hidden' name='id_ejemplar' value='$id_ejemplar'>";
                            echo "<input type='hidden' name='id_estudiante' value='" . intval($row['id_estudiante']) . "'>";
                            echo "<input type='hidden' name='accion' value='prestar'>";
// Reemplaza el botón de préstamo actual con este:
                            if (!$has_prestamo) {
                                echo "<button type='button' onclick='mostrarModalPrestamo(" . intval($row['id_estudiante']) . ")' 
                                    class='bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 transition-colors'>
                                    Prestar
                                    </button>";
                            } else {
                                echo "<button type='button' class='bg-gray-500 text-white px-4 py-2 rounded-md cursor-not-allowed' disabled>No puede Prestar</button>";
                            }
                            echo "</form>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='text-red-500 text-center'>No se encontró ningún estudiante con ese RU o CI.</p>";
                    }
                }
                ?>
            <?php endif; ?>

            <!-- Sección de Libros Actualmente Prestados -->
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-center mb-6">Recursos Actualmente Prestados</h2>
                <?php
                // Consulta para obtener los libros actualmente prestados
                $sql_prestados = "
                SELECT p.id_prestamo, l.titulo, e.nombre, e.apellido_paterno, e.apellido_materno, p.fecha_prestamo, p.lugar
                FROM prestamo p
                JOIN ejemplares ej ON p.id_ejemplar = ej.id_ejemplar
                JOIN libros l ON ej.id_libro = l.id_libro
                JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                WHERE p.estado = 'en curso'";
                $result_prestados = $conn->query($sql_prestados);

                if ($result_prestados->num_rows > 0) {
                    echo "<div class='overflow-x-auto bg-white rounded-lg shadow-md'>";
                    echo "<table class='min-w-full bg-white'>";
                    echo "<thead class='bg-gray-200 text-gray-600 uppercase text-sm leading-normal'>";
                    echo "<tr>";
                    echo "<th class='py-3 px-6 text-left'>Título</th>";
                    echo "<th class='py-3 px-6 text-left'>Estudiante</th>";
                    echo "<th class='py-3 px-6 text-left'>Lugar</th>";
                    echo "<th class='py-3 px-6 text-left'>Fecha de Préstamo</th>";
                    echo "<th class='py-3 px-6 text-center'>Acciones</th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody class='text-gray-600 text-sm font-light'>";
                    
                    while ($row = $result_prestados->fetch_assoc()) {
                        $nombre_estudiante = htmlspecialchars($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']);
                        echo "<tr class='border-b border-gray-200 hover:bg-gray-100' id='prestamo-" . intval($row['id_prestamo']) . "'>";
                        echo "<td class='py-3 px-6'>" . htmlspecialchars($row['titulo']) . "</td>";
                        echo "<td class='py-3 px-6'>" . $nombre_estudiante . "</td>";
                        echo "<td class='py-3 px-6'>" . htmlspecialchars($row['lugar']) . "</td>";
                        echo "<td class='py-3 px-6'>" . htmlspecialchars($row['fecha_prestamo']) . "</td>";
                        echo "<td class='py-3 px-6 text-center'>";
                        // Botón de Devolver
                        echo "<a href='devolver_libro.php?id_prestamo=" . intval($row['id_prestamo']) . "' class='bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600'>Devolver</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    
                    echo "</tbody>";
                    echo "</table>";
                    echo "</div>";
                } else {
                    echo "<p class='text-center text-gray-500'>No hay libros actualmente prestados.</p>";
                }
                ?>
            </div>    

        </main>
<?php include 'includes/footer.php'; ?>
    </body>
<!-- Modal para seleccionar el lugar -->
<!-- Modal para seleccionar el lugar -->
<div id="modalPrestamo" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h3 class="text-xl font-bold mb-4">Seleccione el lugar de préstamo</h3>
        <form id="formPrestamo" action="prestamo_libros.php" method="POST">
            <input type="hidden" name="id_libro" id="modalIdLibro" value="<?php echo $id_libro; ?>">
            <input type="hidden" name="id_ejemplar" id="modalIdEjemplar" value="<?php echo $id_ejemplar; ?>">
            <input type="hidden" name="id_estudiante" id="modalIdEstudiante">
            <input type="hidden" name="accion" value="prestar">
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Lugar:</label>
                <select name="lugar" id="selectLugar" class="w-full px-4 py-2 border rounded-md" required>
                    <option value="sala">Sala de lectura</option>
                    <option value="domicilio">Domicilio</option> 
                    <option value="fotocopia">Fotocopia</option>
                </select>
            </div>
            
            <!-- Campo de teléfono (inicialmente oculto) -->
            <div id="telefonoContainer" class="hidden mb-4">
                <label for="telefono" class="block text-sm font-medium text-gray-700 required-field">Número de celular</label>
                <input type="tel" id="telefono" name="telefono" 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       pattern="[0-9]{8}" title="Ingrese 8 dígitos sin espacios ni guiones"
                       placeholder="Ej: 76543210">
                <p class="mt-1 text-xs text-gray-500">Requerido para préstamos a domicilio o fotocopia</p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="cerrarModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                    Cancelar
                </button>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>

  
</div>

<!-- Script para manejar el modal -->
<script>
    function mostrarModalPrestamo(idEstudiante) {
        document.getElementById('modalIdEstudiante').value = idEstudiante;
        document.getElementById('modalPrestamo').classList.remove('hidden');
    }

    function cerrarModal() {
        document.getElementById('modalPrestamo').classList.add('hidden');
    }


        // Mostrar/ocultar campo de teléfono según selección
    document.getElementById('selectLugar').addEventListener('change', function() {
        const telefonoContainer = document.getElementById('telefonoContainer');
        if (this.value === 'domicilio' || this.value === 'fotocopia') {
            telefonoContainer.classList.remove('hidden');
            document.getElementById('telefono').required = true;
        } else {
            telefonoContainer.classList.add('hidden');
            document.getElementById('telefono').required = false;
        }
    });

    // Validación de teléfono en tiempo real
    document.getElementById('telefono')?.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
    });

    function mostrarModalPrestamo(idEstudiante) {
        // Resetear el modal cada vez que se abre
        document.getElementById('selectLugar').value = 'sala';
        document.getElementById('telefonoContainer').classList.add('hidden');
        document.getElementById('telefono').required = false;
        document.getElementById('telefono').value = '';
        
        document.getElementById('modalIdEstudiante').value = idEstudiante;
        document.getElementById('modalPrestamo').classList.remove('hidden');
    }

    function cerrarModal() {
        document.getElementById('modalPrestamo').classList.add('hidden');
    }
</script>


    </html>

    <?php
    $conn->close();
    ?>