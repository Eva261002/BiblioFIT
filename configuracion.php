<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
checkRole('admin'); // Solo admin puede acceder

// Obtener todos los usuarios (excepto admins) y módulos
$usuarios = $conn->query("
    SELECT u.id, u.email, u.rol,
           GROUP_CONCAT(m.nombre ORDER BY m.nombre SEPARATOR '|') as modulos_asignados,
           GROUP_CONCAT(m.id ORDER BY m.nombre SEPARATOR '|') as modulos_ids
    FROM usuarios u
    LEFT JOIN usuario_modulos um ON u.id = um.usuario_id
    LEFT JOIN modulos m ON um.modulo_id = m.id
    WHERE u.rol != 'admin'  -- Excluir usuarios admin
    GROUP BY u.id
    ORDER BY u.email
");

$modulos = $conn->query("
    SELECT id, nombre, url, icono 
    FROM modulos 
    ORDER BY nombre
");

// Procesar todas las operaciones POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Procesar eliminación de usuario
        if (isset($_POST['eliminar_usuario'])) {
            $usuario_id = (int)$_POST['usuario_id'];
            
            // Verificar que el usuario no sea admin
            $usuario_rol = $conn->query("SELECT rol FROM usuarios WHERE id = $usuario_id")->fetch_assoc()['rol'];
            if ($usuario_rol === 'admin') {
                throw new Exception("No se pueden eliminar cuentas de administrador");
            }

            // Eliminar primero las asignaciones de módulos
            $conn->query("DELETE FROM usuario_modulos WHERE usuario_id = $usuario_id");
            
            // Luego eliminar el usuario
            $result = $conn->query("DELETE FROM usuarios WHERE id = $usuario_id");
            
            if ($conn->affected_rows > 0) {
                $_SESSION['success'] = "Usuario eliminado permanentemente";
            } else {
                $_SESSION['error'] = "No se pudo eliminar el usuario";
            }
        }
        // Procesar asignación/desasignación de módulos
        elseif (isset($_POST['asignar_modulo']) || isset($_POST['quitar_modulo'])) {
            $usuario_id = (int)$_POST['usuario_id'];
            $modulo_id = (int)$_POST['modulo_id'];
            
            // Verificar que el usuario no sea admin
            $usuario_rol = $conn->query("SELECT rol FROM usuarios WHERE id = $usuario_id")->fetch_assoc()['rol'];
            if ($usuario_rol === 'admin') {
                throw new Exception("No se pueden modificar permisos de administradores");
            }

            // Verificar existencia de módulo
            $check_modulo = $conn->query("SELECT 1 FROM modulos WHERE id = $modulo_id")->num_rows;
            
            if (!$check_modulo) {
                throw new Exception("Módulo no válido");
            }

            if (isset($_POST['asignar_modulo'])) {
                // Verificar si ya existe la asignación
                $check = $conn->query("
                    SELECT 1 FROM usuario_modulos 
                    WHERE usuario_id = $usuario_id AND modulo_id = $modulo_id
                ");
                
                if (!$check->num_rows) {
                    $conn->query("
                        INSERT INTO usuario_modulos (usuario_id, modulo_id) 
                        VALUES ($usuario_id, $modulo_id)
                    ");
                    $_SESSION['success'] = "Módulo asignado correctamente";
                } else {
                    $_SESSION['error'] = "Este módulo ya estaba asignado al usuario";
                }
            }
            elseif (isset($_POST['quitar_modulo'])) {
                $result = $conn->query("
                    DELETE FROM usuario_modulos 
                    WHERE usuario_id = $usuario_id AND modulo_id = $modulo_id
                ");
                
                if ($conn->affected_rows > 0) {
                    $_SESSION['success'] = "Módulo eliminado correctamente";
                } else {
                    $_SESSION['error'] = "No se encontró la asignación para eliminar";
                }
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la operación: " . $e->getMessage();
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignación de Módulos a Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Función para confirmar antes de quitar un módulo
        function confirmarEliminacion(form) {
            const modulo = form.querySelector('select[name="modulo_id"] option:checked').text;
            return confirm(`¿Estás seguro de quitar el módulo "${modulo}"?`);
        }
    </script>
</head>
<body class="bg-gray-100">
    <?php include('includes/header.php'); ?>
    
    <main class="container mx-auto px-4 py-8">
        <!-- Mensajes de feedback -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Asignación de Módulos a Usuarios</h1>
            <div class="relative">
                <input type="text" id="buscarUsuario" placeholder="Buscar usuario..." 
                       class="border rounded px-3 py-1 text-sm">
                <i class="fas fa-search absolute right-3 top-2 text-gray-400"></i>
            </div>
        </div>
        
        <!-- Resumen de módulos -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Módulos Disponibles</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php while($modulo = $modulos->fetch_assoc()): ?>
                <div class="border rounded-lg p-4 flex items-center hover:bg-gray-50 transition-colors">
                    <i class="<?= htmlspecialchars($modulo['icono']) ?> text-xl mr-3 text-blue-500"></i>
                    <div>
                        <p class="font-medium"><?= htmlspecialchars($modulo['nombre']) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($modulo['url']) ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Tabla de asignaciones -->
        <div class="bg-white rounded-lg shadow-md p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="tablaUsuarios">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Módulos Asignados</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($usuario = $usuarios->fetch_assoc()): 
                        $modulos_asignados = !empty($usuario['modulos_asignados']) ? 
                            explode('|', $usuario['modulos_asignados']) : [];
                        $modulos_ids = !empty($usuario['modulos_ids']) ? 
                            explode('|', $usuario['modulos_ids']) : [];
                        
                        // Obtener módulos disponibles para asignar
                        $modulos_disponibles = $conn->query("
                            SELECT id, nombre 
                            FROM modulos
                            WHERE id NOT IN (
                                SELECT modulo_id 
                                FROM usuario_modulos 
                                WHERE usuario_id = {$usuario['id']}
                            )
                            ORDER BY nombre
                        ");
                    ?>
                    <tr class="usuario-row">
                        <td class="px-6 py-4 whitespace-nowrap usuario-email">
                            <div class="flex items-center">

                                <!--Boton para eliminar usuario -->
                            <form method="POST" onsubmit="return confirm('¿Está seguro de eliminar PERMANENTEMENTE este usuario? Esta acción no se puede deshacer.')" 
                                class="mr-2">
                                <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                <button type="submit" name="eliminar_usuario" 
                                        class="text-red-500 hover:text-red-700 transition-colors"
                                        title="Eliminar usuario permanentemente">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>


                                <i class="fas fa-user mr-2 text-gray-400"></i>
                                <?= htmlspecialchars($usuario['email']) ?>
                            </div>
                        </td>
                        <td class=px-"6 py-4">
                            <div class="flex flex-wrap gap-2">
                                <?php foreach($modulos_asignados as $index => $modulo): ?>
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded flex items-center">
                                    <i class="fas fa-check-circle mr-1 text-blue-500"></i>
                                    <?= htmlspecialchars($modulo) ?>
                                </span>
                                <?php endforeach; ?>
                                <?php if (empty($modulos_asignados)): ?>
                                <span class="text-gray-500 italic">Sin módulos asignados</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-2 md:flex-row md:space-y-0 md:space-x-2">
                                <!-- Formulario para asignar módulo -->
                                <form method="POST" class="flex items-center">
                                    <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                    <select name="modulo_id" class="border rounded px-2 py-1 text-sm w-40" required>
                                        <option value="">Seleccionar módulo</option>
                                        <?php while($mod = $modulos_disponibles->fetch_assoc()): ?>
                                        <option value="<?= $mod['id'] ?>"><?= htmlspecialchars($mod['nombre']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button type="submit" name="asignar_modulo" 
                                            class="ml-2 bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition-colors flex items-center">
                                        <i class="fas fa-plus mr-1"></i> Asignar
                                    </button>
                                </form>
                                
                                <!-- Formulario para quitar módulo -->
                                <?php if (!empty($modulos_asignados)): ?>
                                <form method="POST" onsubmit="return confirmarEliminacion(this)" class="flex items-center">
                                    <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                    <select name="modulo_id" class="border rounded px-2 py-1 text-sm w-40" required>
                                        <option value="">Seleccionar módulo</option>
                                        <?php foreach($modulos_asignados as $index => $modulo): ?>
                                        <option value="<?= $modulos_ids[$index] ?>"><?= htmlspecialchars($modulo) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="quitar_modulo" 
                                            class="ml-2 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors flex items-center">
                                        <i class="fas fa-minus mr-1"></i> Quitar
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <script>
        // Búsqueda en tiempo real
        document.getElementById('buscarUsuario').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.usuario-row');
            
            rows.forEach(row => {
                const email = row.querySelector('.usuario-email').textContent.toLowerCase();
                if (email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>