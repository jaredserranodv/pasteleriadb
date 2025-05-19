<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$mysqli = require __DIR__ . "/database.php";

$sql = "SELECT u.name, u.email, d.calle, d.numero, d.cp, d.colonia, d.estado, d.ciudad, d.telefono, d.telefono2
        FROM user u
        JOIN user_info d ON u.id = d.user_id
        WHERE u.id = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();

$result = $stmt->get_result();
$userData = $result->fetch_assoc();

if (!$userData) {
    echo "<p>No se encontró la información del usuario.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="account.css">
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
                    <a href="/Pasteleria/signup-login/login.php"> <img src="../usuario.png" alt="usuario" id="usuario-img"></a>
                </nav>
            </div>  
    </header>
    <h2>Perfil del Usuario</h2>

    <section class="form-container">
        <div class="form-grid">
            <p><strong>Nombre:</strong> <?= htmlspecialchars($userData["name"]) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($userData["email"]) ?></p>
            <p><strong>Calle:</strong> <?= htmlspecialchars($userData["calle"]) ?></p>
            <p><strong>Número:</strong> <?= htmlspecialchars($userData["numero"]) ?></p>
            <p><strong>Código Postal:</strong> <?= htmlspecialchars($userData["cp"]) ?></p>
            <p><strong>Colonia:</strong> <?= htmlspecialchars($userData["colonia"]) ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($userData["estado"]) ?></p>
            <p><strong>Ciudad:</strong> <?= htmlspecialchars($userData["ciudad"]) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($userData["telefono"]) ?></p>
            <p><strong>Teléfono 2:</strong> <?= htmlspecialchars($userData["telefono2"]) ?: "No proporcionado" ?></p>
        </div>

        <a href="index.php" class="btn">Editar</a>
    </section>
</body>
</html>
