<?php

$host = 'localhost';
$dbname = 'pasteleriadolceforno'; //EL NOMBRE DE LA BASE DE DATOS
$username = 'root';
$password = '';

$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

$data = json_decode(file_get_contents('php://input'), true);

if($data){
    foreach($data as $item) {
        $stmt = $conn->prepare("INSERT INTO compras (producto_id, nombre_producto, precio) VALUES (:id, :name, :price)");
        $stmt->bindParam(':id', $item['id']);
        $stmt->bindParam(':name', $item['nombre']);
        $stmt->bindParam(':price', $item['precio']);
        $stmt->execute();

    }

    echo json_encode(['message' => 'Compra procesada con exito.']);

} else{
    echo json_encode(['message' => 'No se recibieron datos.']);
}


?>