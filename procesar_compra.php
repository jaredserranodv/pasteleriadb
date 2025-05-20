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

    if ($data && count($data) > 0) {
        $conn->beginTransaction();

        // Insertar pedido general
        $fechaCompra = date("Y-m-d H:i:s");
        $estatus = 'Pendiente';

        $stmtPedido = $conn->prepare("
            INSERT INTO pedido (user_id, user_info_id, fecha_compra, estatus) 
            VALUES (:user_id, :user_info_id, :fecha_compra, :estatus)
        ");
        $stmtPedido->bindParam(':user_id', $user_id);
        $stmtPedido->bindParam(':user_info_id', $user_info_id);
        $stmtPedido->bindParam(':fecha_compra', $fechaCompra);
        $stmtPedido->bindParam(':estatus', $estatus);
        $stmtPedido->execute();

        $pedido_id = $conn->lastInsertId();

        $stmtDetalle = $conn->prepare("
            INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
            VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario, :subtotal)
        ");

        $total = 0;

        foreach ($data as $item) {
            $producto_id = $item['id'];
            $cantidad = isset($item['cantidad']) ? $item['cantidad'] : 1;
            $precio_unitario = $item['precio'];
            $subtotal = $cantidad * $precio_unitario;

            $stmtDetalle->bindParam(':pedido_id', $pedido_id);
            $stmtDetalle->bindParam(':producto_id', $producto_id);
            $stmtDetalle->bindParam(':cantidad', $cantidad);
            $stmtDetalle->bindParam(':precio_unitario', $precio_unitario);
            $stmtDetalle->bindParam(':subtotal', $subtotal);

            $stmtDetalle->execute();

            $total += $subtotal;
        }

        // Actualizar total en pedido
        $stmtUpdateTotal = $conn->prepare("UPDATE pedido SET total = :total WHERE id = :pedido_id");
        $stmtUpdateTotal->bindParam(':total', $total);
        $stmtUpdateTotal->bindParam(':pedido_id', $pedido_id);
        $stmtUpdateTotal->execute();

        $conn->commit();

        echo json_encode(['message' => 'Compra procesada con éxito.', 'pedido_id' => $pedido_id, 'total' => $total]);

    } else {
        echo json_encode(['message' => 'No se recibieron datos.']);
    }

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(['message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
