<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
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

    // Traer pedidos junto con información de envío
    // Traer pedidos con dirección e imagen del producto
    $stmt = $conn->prepare("
        SELECT 
            p.pedido_id,
            p.nombre_producto,
            p.precio,
            p.fecha_compra,
            ui.calle,
            ui.numero,
            ui.colonia,
            ui.cp,
            ui.ciudad,
            ui.estado,
            prod.imagen
        FROM pedido p
        JOIN user_info ui ON p.user_info_id = ui.id
        JOIN productos prod ON p.nombre_producto = prod.nombre
        WHERE p.user_id = :user_id
        ORDER BY p.fecha_compra DESC
    ");


    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Pedidos</title>
    <link rel="stylesheet" href="account.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <header>
            <div class="container">
                <a href="../../index.html"> <div class="img-container"></div> </a>
                <nav>
                <a href="../../index.html">Acerca de</a>
                <a href="../../index.html">Menú</a>
                <a href="#">Pedidos</a>
                <a href="../../galeria.html">Galería</a>
                <a href="../../index.html">Reseñas</a>
                    <a href="../../carrito.html"> <img src="../carrito.png" alt="carrito" id="carrito-img"></a>
                    <a href="../../Pasteleria/signup-login/login.php"> <img src="../usuario.png" alt="usuario" id="usuario-img"></a>
                </nav>
            </div>  
    </header>
    <div class="titulo-pedidos">
    <h1>Mis Pedidos</h1>
    </div>
    
    <?php if (count($pedidos) === 0): ?>
        <p>No has realizado ningún pedido aún.</p>
    <?php else: ?>
        <?php foreach ($pedidos as $pedido): ?>
            <div class="pedido">
            <img src="/Pasteleria_DB/images/<?= htmlspecialchars($pedido['imagen']) ?>" alt="Imagen de producto" width="150" height="150" style="object-fit: cover; border-radius: 10px;">
                <h2><?= htmlspecialchars($pedido['nombre_producto']) ?></h2>
                <p><strong>Precio:</strong> $<?= number_format($pedido['precio'], 2) ?></p>
                <p><strong>Fecha de compra:</strong> <?= htmlspecialchars($pedido['fecha_compra']) ?></p>
                <div class="direccion">
                    <strong>Direccion envio:</strong><br>
                    <?= htmlspecialchars($pedido['calle']) ?> #<?= htmlspecialchars($pedido['numero']) ?><br>
                    <?= htmlspecialchars($pedido['colonia']) ?>, CP <?= htmlspecialchars($pedido['cp']) ?><br>
                    <?= htmlspecialchars($pedido['ciudad']) ?>, <?= htmlspecialchars($pedido['estado']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
