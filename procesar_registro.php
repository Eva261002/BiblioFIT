<?php
include('includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $idEstudiante = isset($_POST['id_estudiante']) ? (int)$_POST['id_estudiante'] : null;
    $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';

    if ($accion == 'buscar') {
        $tipoBusqueda = $_POST['tipo_busqueda'];
        $valorBusqueda = $_POST['valor_busqueda'];
        
        if (!in_array($tipoBusqueda, ['ru', 'ci'])) {
            echo json_encode(["error" => "Tipo de búsqueda no válido"]);
            exit;
        }

        $sql = "SELECT e.*, es.estado 
                FROM estudiantes e 
                LEFT JOIN entradas_salidas es ON e.id_estudiante = es.id_estudiante AND es.estado = 0
                WHERE e.$tipoBusqueda = ? 
                ORDER BY es.hora_entrada DESC 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $valorBusqueda);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $estudiante = $result->fetch_assoc();
            echo json_encode($estudiante);
        } else {
            echo json_encode(["error" => "No se encontró ningún estudiante con ese $tipoBusqueda."]);
        }
    }
    elseif ($accion == 'registrar_entrada') {
        if (!$idEstudiante) {
            echo json_encode(["error" => "ID de estudiante no válido"]);
            exit;
        }

        // Verificar si el estudiante ya está dentro
        $sql_check = "SELECT * FROM entradas_salidas WHERE id_estudiante = ? AND estado = 0";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $idEstudiante);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            echo json_encode(["error" => "El estudiante ya está dentro de la biblioteca."]);
        } else {
            // Registrar la entrada
            $sql_insert = "INSERT INTO entradas_salidas (id_estudiante, hora_entrada, estado, motivo) VALUES (?, NOW(), 0, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("is", $idEstudiante, $motivo);

            if ($stmt_insert->execute()) {
                echo json_encode(["success" => "Entrada registrada exitosamente."]);
            } else {
                echo json_encode(["error" => "Error al registrar la entrada."]);
            }
        }
    } elseif ($accion == 'registrar_salida') {
        if (!$idEstudiante) {
            echo json_encode(["error" => "ID de estudiante no válido"]);
            exit;
        }

        // Registrar la salida
        $sql_update = "UPDATE entradas_salidas SET hora_salida = NOW(), estado = 1 
                    WHERE id_estudiante = ? AND estado = 0";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $idEstudiante);

        if ($stmt_update->execute()) {
            // Enviamos un success sin mensaje (solo para confirmación)
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["error" => "Error al registrar la salida."]);
        }
    }
    
}

// Obtener la lista de estudiantes actualmente dentro
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $sql_list = "SELECT e.id_estudiante, e.ci, 
                        CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) AS nombre_completo, 
                        e.carrera, es.hora_entrada, es.motivo
                 FROM entradas_salidas es
                 JOIN estudiantes e ON es.id_estudiante = e.id_estudiante
                 WHERE es.estado = 0
                 ORDER BY es.hora_entrada DESC";

    $result_list = $conn->query($sql_list);

    $estudiantes_dentro = [];
    while ($row = $result_list->fetch_assoc()) {
        $estudiantes_dentro[] = $row;
    }

    echo json_encode($estudiantes_dentro);
}
?>