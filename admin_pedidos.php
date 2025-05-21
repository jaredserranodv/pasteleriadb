<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Pasteleria/signup-login/login.php");
    exit;
}

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'pasteleriadolceforno');

// Verifica si hubo error en la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$user_id = $_SESSION["user_id"];

// Verificar si el usuario es empleado (admin)
$stmt = $conn->prepare("SELECT 1 FROM empleado WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // No es empleado => acceso denegado
    header("Location: ../Pasteleria/signup-login/login.php");
    exit;
} else {
    // Sí es empleado => asignamos user_type = 1 (admin)
    $_SESSION["user_type"] = 1;
}


$stmt->close();

// (Opcional) Verificación de tipo de usuario, si manejas roles
if (isset($_SESSION["user_type"]) && $_SESSION["user_type"] != 1) {
    echo "Acceso denegado. No tienes permisos para ver esta página.";
    exit;
}

$host = 'localhost';
$dbname = 'pasteleriadolceforno';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $stmt = $conn->prepare("
        SELECT 
            p.id AS pedido_id,
            p.fecha_compra,
            p.estatus,
            mp.nombre AS metodo_pago,
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
        JOIN metodo_pago mp ON p.metodo_pago_id = mp.id
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
                'metodo_pago' => $row['metodo_pago'],
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
     body { font-family: Arial, sans-serif; }
        .filtro-container { margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th  { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: top; font-size: 1.5em; }
        td  { border: 1px solid #ccc; padding: 10px; vertical-align: top; font-size: 1.6em; }
        th { background-color: #f4f4f4; }
        img { max-width: 100px; height: auto; border-radius: 8px; }
    </style>
</head>
<body>
    <header>
        <div class="navbar-container">
            <a href="index.html" class="logo-text">Dolce Forno</a>
            <nav class="navbar">
                <a href="#acerca-de">Acerca de</a>
                <a href="#menu">Menú</a>
                <a href="../Pasteleria_DB/Pasteleria/signup-login/mispedidos.php">Pedidos</a>
                <a href="galeria.html">Galería</a>
                <a href="#reseñas">Reseñas</a>
                <a href="carrito.html" class="icon-link">
                    <img src="Pasteleria/carrito.png" alt="Carrito" id="carrito-img">
                </a>
                <a href="Pasteleria/signup-login/login.php" class="icon-link">
                    <img src="Pasteleria/usuario.png" alt="Usuario" id="usuario-img">
                </a>
            </nav>
        </div>
    </header>
    <div class="titulo-pedidos"><h2>Todos los Pedidos</h2></div>
    

    <div class="filtro-container">
        <label for="ordenar">Ordenar por: </label>
        <select id="ordenar">
            <option value="reciente">Más reciente</option>
            <option value="antiguo">Más antiguo</option>
            <option value="precio_mayor">Precio mayor a menor</option>
            <option value="precio_menor">Precio menor a mayor</option>
        </select>
    </div>

    <table id="tabla-pedidos">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Dirección</th>
                <th>Productos</th>
                <th>Método de pago</th>
                <th>Estatus</th>
                <th>Total</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido_id => $pedido): ?>
                <?php
                $total = 0;
                foreach ($pedido['productos'] as $prod) {
                    $total += $prod['subtotal'];
                }
                ?>
                <tr data-fecha="<?= $pedido['fecha_compra'] ?>" data-total="<?= $total ?>">
                    <td><?= htmlspecialchars($pedido['fecha_compra']) ?></td>
                    <td><?= htmlspecialchars($pedido['nombre_usuario']) ?></td>
                    <td>
                        <?= htmlspecialchars($pedido['direccion']['calle']) ?> #<?= htmlspecialchars($pedido['direccion']['numero']) ?><br>
                        <?= htmlspecialchars($pedido['direccion']['colonia']) ?>, CP <?= htmlspecialchars($pedido['direccion']['cp']) ?><br>
                        <?= htmlspecialchars($pedido['direccion']['ciudad']) ?>, <?= htmlspecialchars($pedido['direccion']['estado']) ?>
                    </td>
                    <td>
                        <?php foreach ($pedido['productos'] as $prod): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="/Pasteleria_DB/images/<?= htmlspecialchars($prod['imagen']) ?>" alt="Imagen de producto"><br>
                                <strong><?= htmlspecialchars($prod['nombre_producto']) ?></strong><br>
                                Cantidad: <?= $prod['cantidad'] ?>, $<?= number_format($prod['precio_unitario'], 2) ?><br>
                                Subtotal: $<?= number_format($prod['subtotal'], 2) ?>
                            </div>
                        <?php endforeach; ?>
                    </td>
                    <td><?= htmlspecialchars($pedido['metodo_pago']) ?></td>
                    <td><?= htmlspecialchars($pedido['estatus']) ?></td>
                    <td>$<?= number_format($total, 2) ?></td>
                    <td>
                        <form method="POST" action="editar_pedido.php">
                            <input type="hidden" name="pedido_id" value="<?= htmlspecialchars($pedido_id) ?>">
                            <select name="estatus">
                                <option value="Pendiente" <?= $pedido['estatus'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="En proceso" <?= $pedido['estatus'] == 'En proceso' ? 'selected' : '' ?>>En proceso</option>
                                <option value="Entregado" <?= $pedido['estatus'] == 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                            </select>
                            <button type="submit">Actualizar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        const selectOrden = document.getElementById('ordenar');
        const tbody = document.querySelector('#tabla-pedidos tbody');

        selectOrden.addEventListener('change', () => {
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const orden = selectOrden.value;

            rows.sort((a, b) => {
                const fechaA = new Date(a.dataset.fecha);
                const fechaB = new Date(b.dataset.fecha);
                const totalA = parseFloat(a.dataset.total);
                const totalB = parseFloat(b.dataset.total);

                switch (orden) {
                    case 'reciente': return fechaB - fechaA;
                    case 'antiguo': return fechaA - fechaB;
                    case 'precio_mayor': return totalB - totalA;
                    case 'precio_menor': return totalA - totalB;
                    default: return 0;
                }
            });

            rows.forEach(row => tbody.appendChild(row));
        });
    </script>
</body>
</html>