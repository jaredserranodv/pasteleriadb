<?php
session_start();

// Debug opcional
// echo '<pre>';
// var_dump($_SESSION);
// echo '</pre>';

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_type"])) {
    echo "No tienes sesión iniciada o user_type no definido.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["pedido_id"], $_POST["estatus"])) {
        echo "Datos incompletos.";
        exit;
    }

    $pedido_id = $_POST["pedido_id"];
    $nuevo_estatus = $_POST["estatus"];

    try {
        $conn = new PDO("mysql:host=localhost;dbname=pasteleriadolceforno;charset=utf8", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("UPDATE pedido SET estatus = :estatus WHERE id = :id");
        $stmt->bindParam(':estatus', $nuevo_estatus);
        $stmt->bindParam(':id', $pedido_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            header("Location: admin_pedidos.php");
            exit;
        } else {
            echo "Error al actualizar el pedido.";
        }
    } catch (PDOException $e) {
        echo "Error en la base de datos: " . $e->getMessage();
    }
} else {
    echo "Método no permitido.";
}
