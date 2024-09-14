<?php
include('includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $ru = $_POST['ru'];
    $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : '';

    if ($accion == 'buscar') {
        // Buscar estudiante por RU
        $sql = "SELECT e.*, es.estado FROM estudiantes e LEFT JOIN entradas_salidas es ON e.id_estudiante = es.id_estudiante WHERE e.ru = ? ORDER BY es.hora_entrada DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ru);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $estudiante = $result->fetch_assoc();
            echo json_encode($estudiante);
        } else {
            echo json_encode(["error" => "No se encontró ningún estudiante con ese RU."]);
        }
    }elseif ($accion == 'registrar_entrada') {
        // Verificar si el estudiante ya está dentro (estado = 0)
        $sql_check = "SELECT * FROM entradas_salidas WHERE id_estudiante = (SELECT id_estudiante FROM estudiantes WHERE ru = ?) AND estado = 0";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $ru);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            echo json_encode(["error" => "El estudiante ya está dentro de la biblioteca."]);
        } else {
            // Registrar la entrada del estudiante si no está dentro
            $sql_insert = "INSERT INTO entradas_salidas (id_estudiante, hora_entrada, estado, motivo) VALUES ((SELECT id_estudiante FROM estudiantes WHERE ru = ?), NOW(), 0, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ss", $ru, $motivo);

            if ($stmt_insert->execute()) {
                echo json_encode(["success" => "Entrada registrada exitosamente."]);
            } else {
                echo json_encode(["error" => "Error al registrar la entrada."]);
            }
        }
    } elseif ($accion == 'registrar_salida') {
        // Registrar la salida del estudiante (sin sobrescribir el motivo)
        $sql_update = "UPDATE entradas_salidas SET hora_salida = NOW(), estado = 1 WHERE id_estudiante = (SELECT id_estudiante FROM estudiantes WHERE ru = ?) AND estado = 0";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("s", $ru);

        if ($stmt_update->execute()) {
            echo json_encode(["success" => "Salida registrada exitosamente."]);
        } else {
            echo json_encode(["error" => "Error al registrar la salida."]);
        }
    }
}

// Obtener la lista de estudiantes actualmente dentro
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $sql_list = "SELECT e.ci, CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) AS nombre_completo, e.carrera, es.hora_entrada, es.motivo
                 FROM entradas_salidas es
                 JOIN estudiantes e ON es.id_estudiante = e.id_estudiante
                 WHERE es.estado = 0";

    $result_list = $conn->query($sql_list);

    $estudiantes_dentro = [];
    while ($row = $result_list->fetch_assoc()) {
        $estudiantes_dentro[] = $row;
    }

    echo json_encode($estudiantes_dentro);
}
?>
 