<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../Pasteleria/signup-login/login.php");
    exit;
}

$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST["comentario"]) && !empty($_POST["calificacion"])) {
        $comentario = trim($_POST["comentario"]);
        $calificacion = intval($_POST["calificacion"]);
        $user_id = $_SESSION["user_id"];

        if ($calificacion < 1 || $calificacion > 5) {
            $mensaje = "Calificación inválida.";
        } else {
            try {
                $pdo = new PDO("mysql:host=localhost;dbname=pasteleriadolceforno;charset=utf8", "root", "");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $pdo->prepare("INSERT INTO reseñas (user_id, comentario, calificacion, fecha) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$user_id, $comentario, $calificacion]);

                $mensaje = "¡Gracias por tu reseña!";
            } catch (PDOException $e) {
                $mensaje = "Error al guardar la reseña: " . $e->getMessage();
            }
        }
    } else {
        $mensaje = "Por favor completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Dejar una reseña</title>
    <link rel="stylesheet" href="carrito.css">
    <link rel="stylesheet" href="reseñas.css">
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
    <div class="titulo-reseñas"><h1>Deja tu reseña</h1></div>

  <?php if ($mensaje): ?>
    <p style="padding: 10px; background-color: #d4edda; color: #155724; border-radius: 5px;"><?= htmlspecialchars($mensaje) ?></p>
  <?php endif; ?>

  <form method="POST" action="">
    <label for="comentario">Tu reseña:</label>
    <textarea id="comentario" name="comentario" rows="5" required><?= isset($_POST["comentario"]) ? htmlspecialchars($_POST["comentario"]) : "" ?></textarea>

    <label for="calificacion">Calificación (1 a 5):</label>
    <select id="calificacion" name="calificacion" required>
        <option value="">Selecciona</option>
        <?php
        for ($i=1; $i <= 5; $i++) {
            $selected = (isset($_POST["calificacion"]) && $_POST["calificacion"] == $i) ? "selected" : "";
            echo "<option value='$i' $selected>$i</option>";
        }
        ?>
    </select>

    <button type="submit">Enviar reseña</button>
  </form>
</div>

</body>
</html>