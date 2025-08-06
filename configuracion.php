<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
checkRole('admin'); // Solo admin puede acceder


// Procesar asignación/desasignación de módulos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['asignar_modulo'])) {
            $usuario_id = (int)$_POST['usuario_id'];
            $modulo_id = (int)$_POST['modulo_id'];
            
            // Verificar si ya existe la asignación
            $check = $conn->prepare("SELECT 1 FROM usuario_modulos WHERE usuario_id = ? AND modulo_id = ?");
            $check->bind_param("ii", $usuario_id, $modulo_id);
            $check->execute();
            
            if (!$check->get_result()->num_rows) {
                $stmt = $conn->prepare("INSERT INTO usuario_modulos (usuario_id, modulo_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $usuario_id, $modulo_id);
                $stmt->execute();
                $_SESSION['success'] = "Módulo asignado correctamente";
            } else {
                $_SESSION['error'] = "Este módulo ya estaba asignado al usuario";
            }
        }
        elseif (isset($_POST['quitar_modulo'])) {
            $usuario_id = (int)$_POST['usuario_id'];
            $modulo_id = (int)$_POST['modulo_id'];
            
            $stmt = $conn->prepare("DELETE FROM usuario_modulos WHERE usuario_id = ? AND modulo_id = ?");
            $stmt->bind_param("ii", $usuario_id, $modulo_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $_SESSION['success'] = "Módulo eliminado correctamente";
            } else {
                $_SESSION['error'] = "No se encontró la asignación para eliminar";
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la operación: " . $e->getMessage();
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Obtener todos los usuarios y módulos
$usuarios = $conn->query("SELECT id, email FROM usuarios ORDER BY email");
$modulos = $conn->query("SELECT id, nombre, url, icono FROM modulos ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignación de Módulos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include('includes/header.php'); ?>
    
    <main class="container mx-auto px-4 py-8">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['success'] ?></span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['error'] ?></span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <h1 class="text-2xl font-bold mb-6">Asignación de Módulos por Usuario</h1>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Todos los Módulos Disponibles</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php while($modulo = $modulos->fetch_assoc()): ?>
                <div class="border rounded-lg p-4 flex items-center">
                    <i class="<?= htmlspecialchars($modulo['icono']) ?> text-xl mr-3 text-blue-500"></i>
                    <span><?= htmlspecialchars($modulo['nombre']) ?></span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Módulos Asignados</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($usuario = $usuarios->fetch_assoc()): 
                        // Obtener módulos asignados al usuario
                        $stmt = $conn->prepare("
                            SELECT m.id, m.nombre, m.icono 
                            FROM usuario_modulos um
                            JOIN modulos m ON um.modulo_id = m.id
                            WHERE um.usuario_id = ?
                        ");
                        $stmt->bind_param("i", $usuario['id']);
                        $stmt->execute();
                        $modulos_usuario = $stmt->get_result();
                        
                        // Obtener módulos disponibles para asignar
                        $stmt = $conn->prepare("
                            SELECT m.id, m.nombre 
                            FROM modulos m
                            WHERE m.id NOT IN (
                                SELECT modulo_id 
                                FROM usuario_modulos 
                                WHERE usuario_id = ?
                            )
                            ORDER BY m.nombre
                        ");
                        $stmt->bind_param("i", $usuario['id']);
                        $stmt->execute();
                        $modulos_disponibles = $stmt->get_result();
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= htmlspecialchars($usuario['email']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                <?php while($mod = $modulos_usuario->fetch_assoc()): ?>
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded flex items-center">
                                    <i class="<?= htmlspecialchars($mod['icono']) ?> mr-1"></i>
                                    <?= htmlspecialchars($mod['nombre']) ?>
                                </span>
                                <?php endwhile; ?>
                                <?php if ($modulos_usuario->num_rows === 0): ?>
                                <span class="text-gray-500 italic">Sin módulos asignados</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-2 md:flex-row md:space-y-0 md:space-x-2">
                                <!-- Formulario para asignar módulo -->
                                <form method="POST" class="flex items-center">
                                    <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                    <select name="modulo_id" class="border rounded px-2 py-1 text-sm" required>
                                        <option value="">Seleccionar módulo</option>
                                        <?php while($mod = $modulos_disponibles->fetch_assoc()): ?>
                                        <option value="<?= $mod['id'] ?>"><?= htmlspecialchars($mod['nombre']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button type="submit" name="asignar_modulo" class="ml-2 bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                        <i class="fas fa-plus mr-1"></i> Asignar
                                    </button>
                                </form>
                                
                                <!-- Formulario para quitar módulo -->
                                <?php if ($modulos_usuario->num_rows > 0): ?>
                                <form method="POST" class="flex items-center">
                                    <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                    <select name="modulo_id" class="border rounded px-2 py-1 text-sm" required>
                                        <option value="">Seleccionar módulo</option>
                                        <?php 
                                            $modulos_usuario->data_seek(0); // Reiniciar el puntero
                                            while($mod = $modulos_usuario->fetch_assoc()):
                                        ?>
                                        <option value="<?= $mod['id'] ?>"><?= htmlspecialchars($mod['nombre']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button type="submit" name="quitar_modulo" class="ml-2 bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
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
    
    <?php include('includes/footer.php'); ?>
</body>
</html>