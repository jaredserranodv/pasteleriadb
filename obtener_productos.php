<?php

$host = 'localhost';
$dbname = 'pasteleriadolceforno'; //EL NOMBRE DE LA BASE DE DATOS
$username = 'root';
$password = '';

$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);


$stmt = $conn->query("SELECT id, nombre, descripcion, precio, cantidad, categoria, imagen FROM productos");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);


header('Content-Type: application/json');
echo json_encode($productos);
?>