<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    http_response_code(401); // No autorizado
    echo json_encode(['message' => 'Usuario no autenticado.']);
    exit;
}

$host = 'localhost';
$dbname = 'pasteleriadolceforno';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION["user_id"];

    // Obtener el ID de user_info
    $stmtInfo = $conn->prepare("SELECT id FROM user_info WHERE user_id = :user_id");
    $stmtInfo->bindParam(':user_id', $user_id);
    $stmtInfo->execute();
    $userInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    if (!$userInfo) {
        echo json_encode(['message' => 'No se encontró dirección de envío para este usuario.']);
        exit;
    }

    $user_info_id = $userInfo['id'];

    $data = json_decode(file_get_contents('php://input'), true);

    if ($data) {
        foreach ($data as $item) {
            $stmt = $conn->prepare("INSERT INTO pedido (user_id, user_info_id, pedido_id, nombre_producto, precio, fecha_compra)
                                    VALUES (:user_id, :user_info_id, :pedido_id, :nombre_producto, :precio, :fecha_compra)");

            $fechaCompra = date("Y-m-d H:i:s");
            $pedidoId = uniqid("PED");

            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':user_info_id', $user_info_id);
            $stmt->bindParam(':pedido_id', $pedidoId);
            $stmt->bindParam(':nombre_producto', $item['nombre']);
            $stmt->bindParam(':precio', $item['precio']);
            $stmt->bindParam(':fecha_compra', $fechaCompra);

            $stmt->execute();
        }

        echo json_encode(['message' => 'Compra procesada con éxito.']);
    } else {
        echo json_encode(['message' => 'No se recibieron datos.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
