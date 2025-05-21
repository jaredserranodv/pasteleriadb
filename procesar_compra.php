<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Evita que se mande salida antes de tiempo
ob_start();
header('Content-Type: application/json');

// LOG para confirmar entrada
file_put_contents('debug_log.txt', "Entró al archivo\n", FILE_APPEND);

// Verifica que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ob_clean();
    echo json_encode(['message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Guarda el input recibido para debug
file_put_contents('debug_carrito.json', json_encode($input, JSON_PRETTY_PRINT));

if (!$input) {
    ob_clean();
    echo json_encode(['message' => 'No se recibió JSON o está mal formado']);
    exit;
}
if (!isset($input['carrito'])) {
    ob_clean();
    echo json_encode(['message' => 'No se recibió carrito']);
    exit;
}
if (!isset($input['metodo_pago'])) {
    ob_clean();
    echo json_encode(['message' => 'No se recibió metodo_pago']);
    exit;
}

// Conexión a base de datos
$conn = new mysqli('localhost', 'root', '', 'pasteleriadolceforno');
if ($conn->connect_error) {
    ob_clean();
    echo json_encode(['message' => 'Error de conexión a la base de datos']);
    exit;
}

$carrito = $input['carrito'];
$metodoPago = $conn->real_escape_string($input['metodo_pago']);
$datosPago = $input['datos_pago'] ?? [];

session_start();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    ob_clean();
    echo json_encode(['message' => 'Usuario no autenticado']);
    exit;
}

// Determinar ID de método de pago
$metodoPago = strtolower(trim($input['metodo_pago']));
file_put_contents('debug_log.txt', "Método de pago recibido: '$metodoPago'\n", FILE_APPEND);

$queryMetodo = $conn->prepare("SELECT id FROM metodo_pago WHERE LOWER(nombre) = ?");
$queryMetodo->bind_param("s", $metodoPago);
$queryMetodo->execute();
$resultMetodo = $queryMetodo->get_result();

if ($resultMetodo->num_rows === 0) {
    file_put_contents('debug_log.txt', "Método no encontrado en DB\n", FILE_APPEND);
    ob_clean();
    echo json_encode(['message' => 'Método de pago no válido']);
    exit;
}

$metodo_pago_id = $resultMetodo->fetch_assoc()['id'];


// Obtener dirección de entrega
$sqlUserInfo = "SELECT id FROM user_info WHERE user_id = $user_id LIMIT 1";
$resUserInfo = $conn->query($sqlUserInfo);
if (!$resUserInfo || $resUserInfo->num_rows === 0) {
    ob_clean();
    echo json_encode(['message' => 'Información de entrega no encontrada']);
    exit;
}
$user_info_id = $resUserInfo->fetch_assoc()['id'];

// Calcular total
$total = 0;
foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Insertar pedido
$stmtPedido = $conn->prepare("INSERT INTO pedido (user_id, user_info_id, fecha_compra, estatus, total, metodo_pago_id) VALUES (?, ?, NOW(), 'pendiente', ?, ?)");
$stmtPedido->bind_param("iidi", $user_id, $user_info_id, $total, $metodo_pago_id);

if (!$stmtPedido->execute()) {
    ob_clean();
    echo json_encode(['message' => 'Error al guardar el pedido']);
    exit;
}
$pedido_id = $stmtPedido->insert_id;

// Insertar productos del carrito al detalle del pedido
$stmtDetalle = $conn->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
foreach ($carrito as $item) {
    $subtotal = $item['precio'] * $item['cantidad'];
    $stmtDetalle->bind_param("iiidd", $pedido_id, $item['id'], $item['cantidad'], $item['precio'], $subtotal);
    $stmtDetalle->execute();
}

// Si es pago con tarjeta, guardar detalles
if ($metodoPago === 'tarjeta') {
    $nombreTarjeta = $conn->real_escape_string($datosPago['nombreTarjeta'] ?? '');
    $referencia = substr(md5($datosPago['numeroTarjeta'] ?? ''), 0, 8);
    $stmtPago = $conn->prepare("INSERT INTO pago_tarjeta (pedido_id, referencia, nombre) VALUES (?, ?, ?)");
    $stmtPago->bind_param("iss", $pedido_id, $referencia, $nombreTarjeta);
    $stmtPago->execute();
}

// Obtener resumen de productos
$resumen = [];
$stmtResumen = $conn->prepare("
    SELECT p.nombre, dp.cantidad, dp.precio_unitario, dp.subtotal
    FROM detalle_pedido dp
    JOIN productos p ON p.id = dp.producto_id
    WHERE dp.pedido_id = ?
");
$stmtResumen->bind_param("i", $pedido_id);
$stmtResumen->execute();
$result = $stmtResumen->get_result();
while ($row = $result->fetch_assoc()) {
    $resumen[] = $row;
}

// Limpia todo lo que se haya enviado antes y envía el JSON final
ob_clean();
echo json_encode([
    'message' => 'Compra procesada correctamente',
    'pedido_id' => $pedido_id,
    'total' => $total,
    'metodo_pago' => $metodoPago,
    'productos' => $resumen
]);
exit;
?>
