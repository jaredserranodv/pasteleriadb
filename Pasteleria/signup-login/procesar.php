<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    // Usuario no autenticado
    header("Location: login.php");
    exit;
}

$mysqli = require __DIR__ . "/database.php";

// Sanitizar entradas
$nombre = $_POST["nombre"];
$email = $_POST["email"];
$user_id = $_SESSION["user_id"];
$calle = $_POST["calle"];
$numero = $_POST["numero"];
$cp = $_POST["cp"];
$colonia = $_POST["colonia"];
$estado = $_POST["estado"];
$ciudad = $_POST["ciudad"];
$telefono = $_POST["telefono"];
$telefono2 = $_POST["telefono2"] ?? null; // Puede ser null

// Actualizar nombre y correo en la tabla user
$sqlUser = "UPDATE user SET name = ?, email = ? WHERE id = ?";
$stmtUser = $mysqli->prepare($sqlUser);
$stmtUser->bind_param("ssi", $nombre, $email, $user_id);

if (!$stmtUser->execute()) {
    echo "Error al actualizar nombre y correo: " . $stmtUser->error;
    exit;
}

// Comprobar si ya existe registro para este usuario
$checkSql = "SELECT id FROM user_info WHERE user_id = ?";
$stmt = $mysqli->prepare($checkSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->fetch_assoc()) {
    // Si ya existe, actualizar
    $sql = "UPDATE user_info SET calle=?, numero=?, cp=?, colonia=?, estado=?, ciudad=?, telefono=?, telefono2=? WHERE user_id=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ssssssssi", $calle, $numero, $cp, $colonia, $estado, $ciudad, $telefono, $telefono2, $user_id);
} else {
    // Si no existe, insertar
    $sql = "INSERT INTO user_info (user_id, calle, numero, cp, colonia, estado, ciudad, telefono, telefono2)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("issssssss", $user_id, $calle, $numero, $cp, $colonia, $estado, $ciudad, $telefono, $telefono2);
}

if ($stmt->execute()) {
    header("Location: perfilguardado.php"); // Redirige a una página de confirmación
    exit;
} else {
    echo "Error al guardar datos: " . $stmt->error;
}
?>
