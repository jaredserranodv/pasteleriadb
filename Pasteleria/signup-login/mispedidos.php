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

    // Consulta para traer pedidos y sus detalles junto con dirección y producto
    $stmt = $conn->prepare("
        SELECT 
            p.id AS pedido_id,
            p.fecha_compra,
            p.estatus,
            ui.calle,
            ui.numero,
            ui.colonia,
            ui.cp,
            ui.ciudad,
            ui.estado,
            dp.cantidad,
            dp.precio_unitario,
            prod.nombre AS nombre_producto,
            prod.imagen
        FROM pedido p
        JOIN user_info ui ON p.user_info_id = ui.id
        JOIN detalle_pedido dp ON dp.pedido_id = p.id
        JOIN productos prod ON dp.producto_id = prod.id
        WHERE p.user_id = :user_id
        ORDER BY p.fecha_compra DESC, p.id, dp.id
    ");

    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organizar resultados agrupando por pedido
    $pedidos = [];
    foreach ($results as $row) {
        $pid = $row['pedido_id'];
        if (!isset($pedidos[$pid])) {
            $pedidos[$pid] = [
                'fecha_compra' => $row['fecha_compra'],
                'estatus' => $row['estatus'],
                'direccion' => [
                    'calle' => $row['calle'],
                    'numero' => $row['numero'],
                    'colonia' => $row['colonia'],
                    'cp' => $row['cp'],
                    'ciudad' => $row['ciudad'],
                    'estado' => $row['estado'],
                ],
                'productos' => []
            ];
        }
        $pedidos[$pid]['productos'][] = [
            'nombre_producto' => $row['nombre_producto'],
            'cantidad' => $row['cantidad'],
            'precio_unitario' => $row['precio_unitario'],
            'imagen' => $row['imagen'],
            'subtotal' => $row['cantidad'] * $row['precio_unitario']
        ];
    }

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
        .pedido {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 10px;
        }
        .productos {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .producto {
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 8px;
            width: 180px;
            text-align: center;
        }
        .producto img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <a href="../../index.html"><div class="img-container"></div></a>
            <nav>
                <a href="../../index.html">Acerca de</a>
                <a href="../../index.html">Menú</a>
                <a href="#">Pedidos</a>
                <a href="../../galeria.html">Galería</a>
                <a href="../../index.html">Reseñas</a>
                <a href="../../carrito.html"><img src="../carrito.png" alt="carrito" id="carrito-img"></a>
                <a href="../../Pasteleria/signup-login/login.php"><img src="../usuario.png" alt="usuario" id="usuario-img"></a>
            </nav>
        </div>  
    </header>
    <div class="titulo-pedidos">
        <h1>Mis Pedidos</h1>
    </div>

            <?php if (empty($pedidos)): ?>
            <p>No has realizado ningún pedido aún.</p>
        <?php else: ?>
            <?php foreach ($pedidos as $pedido_id => $pedido): ?>
                <?php
                $totalPedido = 0;
                foreach ($pedido['productos'] as $producto) {
                    $totalPedido += $producto['subtotal'];
                }
                ?>
                <div class="pedido">
                    <h2>Pedido #<?= htmlspecialchars($pedido_id) ?></h2>
                    <p><strong>Fecha de compra:</strong> <?= htmlspecialchars($pedido['fecha_compra']) ?></p>
                    <p><strong>Estatus:</strong> <?= htmlspecialchars($pedido['estatus']) ?></p>
                    <div class="direccion">
                        <strong>Dirección de envío:</strong><br>
                        <?= htmlspecialchars($pedido['direccion']['calle']) ?> #<?= htmlspecialchars($pedido['direccion']['numero']) ?><br>
                        <?= htmlspecialchars($pedido['direccion']['colonia']) ?>, CP <?= htmlspecialchars($pedido['direccion']['cp']) ?><br>
                        <?= htmlspecialchars($pedido['direccion']['ciudad']) ?>, <?= htmlspecialchars($pedido['direccion']['estado']) ?>
                    </div>

                    <h3>Productos:</h3>
                    <div class="productos">
                        <?php foreach ($pedido['productos'] as $producto): ?>
                            <div class="producto">
                                <img src="/Pasteleria_DB/images/<?= htmlspecialchars($producto['imagen']) ?>" alt="Imagen de producto">
                                <h4><?= htmlspecialchars($producto['nombre_producto']) ?></h4>
                                <p>Cantidad: <?= htmlspecialchars($producto['cantidad']) ?></p>
                                <p>Precio unitario: $<?= number_format($producto['precio_unitario'], 2) ?></p>
                                <p>Subtotal: $<?= number_format($producto['subtotal'], 2) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Mostrar total del pedido -->
                    <h3>Total del pedido: $<?= number_format($totalPedido, 2) ?></h3>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

</body>
</html>
