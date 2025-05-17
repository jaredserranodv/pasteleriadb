<?php

$host = 'localhost';
$dbname = 'pasteleriadolceforno'; //EL NOMBRE DE LA BASE DE DATOS
$username = 'root';
$password = '';

$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

$data = json_decode(file_get_contents('php://input'), true);

if($data){
    foreach($data as $item) {
        $stmt = $conn->prepare("INSERT INTO pedido (pedido_id, nombre_producto, precio, fecha_compra)
                                VALUES (:id, :name, :price, :fecha)");

        $fechaCompra = date("Y-m-d H:i:s");

        $stmt->bindParam(':id', $item['id']);
        $stmt->bindParam(':name', $item['nombre']);
        $stmt->bindParam(':price', $item['precio']);
        $stmt->bindParam(':fecha', $fechaCompra);

        $stmt->execute();
    }

    echo json_encode(['message' => 'Compra procesada con éxito.']);
} else {
    echo json_encode(['message' => 'No se recibieron datos.']);
}

?>