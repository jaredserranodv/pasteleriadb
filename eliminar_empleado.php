<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    echo "No tienes sesión iniciada.";
    exit;
}

$conn = mysqli_connect('localhost', 'root', '', 'pasteleriadolceforno');
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

$user_id = $_SESSION["user_id"];

// Validar que el usuario sea empleado (admin)
$stmt = $conn->prepare("SELECT 1 FROM empleado WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo "No tienes permisos para eliminar empleados.";
    exit;
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Primero eliminar user_info
    $stmt1 = $conn->prepare("DELETE FROM user_info WHERE user_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();

    // Luego eliminar empleado
    $stmt2 = $conn->prepare("DELETE FROM empleado WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();

    // Finalmente eliminar usuario
    $stmt3 = $conn->prepare("DELETE FROM user WHERE id = ?");
    $stmt3->bind_param("i", $id);
    $stmt3->execute();
    $stmt3->close();

    echo "ok";
} else {
    echo "Solicitud inválida.";
}
?>
