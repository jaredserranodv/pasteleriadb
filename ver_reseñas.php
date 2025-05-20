<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=pasteleriadolceforno;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT r.comentario, r.calificacion, r.fecha, u.name FROM reseñas r JOIN user u ON r.user_id = u.id ORDER BY r.fecha DESC");
    $reseñas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Reseñas</title>
    <link rel="stylesheet" href="../Pasteleria_DB/admin.css">
    <link rel="stylesheet" href="../Pasteleria_DB/reseñas.css">

        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #f9f9f9;
                margin: 0;
                padding: 0;
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

    <div class="titulo-reseñas"><h1>Reseñas de usuarios</h1></div>


        <?php if (empty($reseñas)): ?>
            <p>No hay reseñas aún.</p>
        <?php else: ?>
            <?php foreach ($reseñas as $r): ?>
                <div class="reseña">
                    <div class="usuario"><?= htmlspecialchars($r['name']) ?></div>
                    <div class="fecha"><?= htmlspecialchars($r['fecha']) ?></div>
                    <div class="calificacion">Calificación: <?= htmlspecialchars($r['calificacion']) ?>/5</div>
                    <p><?= nl2br(htmlspecialchars($r['comentario'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

</body>
</html>