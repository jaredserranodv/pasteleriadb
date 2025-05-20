<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != 1) {
    header("Location: login.php");
    exit;
}

if ($_SESSION["user_type"] != 1) {
    echo "Acceso denegado. No tienes permisos para ver esta página.";
    exit;
}

$host = 'localhost';
$dbname = 'pasteleriadolceforno';
$username = 'root';
$password = '';

try {
    // Crear conexión PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
            prod.imagen,
            u.name AS nombre_usuario
        FROM pedido p
        JOIN detalle_pedido dp ON dp.pedido_id = p.id
        JOIN productos prod ON dp.producto_id = prod.id
        JOIN user u ON p.user_id = u.id
        JOIN user_info ui ON u.id = ui.user_id
        ORDER BY p.fecha_compra DESC, p.id, dp.id
    ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pedidos = [];
    foreach ($results as $row) {
        $pid = $row['pedido_id'];
        if (!isset($pedidos[$pid])) {
            $pedidos[$pid] = [
                'fecha_compra' => $row['fecha_compra'],
                'estatus' => $row['estatus'],
                'nombre_usuario' => $row['nombre_usuario'],
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
    <title>Todos los Pedidos (Admin)</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .pedido {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .pedido img {
            border-radius: 10px;
            object-fit: cover;
        }
        .detalle-pedido {
            flex-grow: 1;
        }
    </style>
</head>
<body>
    <header>
            <div class="header-container">
            <a href="index.html"> <div class="img-container"></div> </a>
            <nav>
                <a href="#acerca-de">Acerca de</a>
                <a href="#menu">Menú</a>
                <a href="../Pasteleria_DB/Pasteleria/signup-login/mispedidos.php">Pedidos</a>
                <a href="galeria.html">Galería</a>
                <a href="#reseñas">Reseñas</a>
                <a href="carrito.html"> <img src="Pasteleria/carrito.png" alt="carrito" id="carrito-img"></a>
                <a href="Pasteleria/signup-login/login.php"> <img src="Pasteleria/usuario.png" alt="usuario" id="usuario-img"></a>
            </nav>
            </div>
    </header>
        <div class="titulo-pedidos"><h1>Todos los Pedidos</h1></div>
    <?php if (empty($pedidos)): ?>
        <p>No hay pedidos realizados.</p>
    <?php else: ?>
        <?php foreach ($pedidos as $pedido_id => $pedido): ?>
            <?php
            $totalPedido = 0;
            foreach ($pedido['productos'] as $producto) {
                $totalPedido += $producto['subtotal'];
            }
            ?>
            <div class="pedido">
                <div class="productos">
                    <?php foreach ($pedido['productos'] as $producto): ?>
                        <div class="producto">
                            <img src="/Pasteleria_DB/images/<?= htmlspecialchars($producto['imagen']) ?>" alt="Imagen de producto">
                            <div class="detalle-pedido">
                                <h3><?= htmlspecialchars($producto['nombre_producto']) ?></h3>
                                <p><strong>Cantidad:</strong> <?= htmlspecialchars($producto['cantidad']) ?></p>
                                <p><strong>Precio unitario:</strong> $<?= number_format($producto['precio_unitario'], 2) ?></p>
                                <p><strong>Subtotal:</strong> $<?= number_format($producto['subtotal'], 2) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="info-pedido">
                    <p><strong>Fecha de compra:</strong> <?= htmlspecialchars($pedido['fecha_compra']) ?></p>
                    <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['nombre_usuario']) ?></p>
                    <p><strong>Estatus:</strong> <?= htmlspecialchars($pedido['estatus']) ?></p>
                    <p><strong>Dirección de envío:</strong><br>
                        <?= htmlspecialchars($pedido['direccion']['calle']) ?> #<?= htmlspecialchars($pedido['direccion']['numero']) ?><br>
                        <?= htmlspecialchars($pedido['direccion']['colonia']) ?>, CP <?= htmlspecialchars($pedido['direccion']['cp']) ?><br>
                        <?= htmlspecialchars($pedido['direccion']['ciudad']) ?>, <?= htmlspecialchars($pedido['direccion']['estado']) ?>
                    </p>
                    <h3>Total del pedido: $<?= number_format($totalPedido, 2) ?></h3>
                </div>
            </div>

        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
