<?php

session_start();

if (isset($_SESSION["user_id"])) {
    
    $mysqli = require __DIR__ . "/database.php";
    
    $sql = "SELECT * FROM user
            WHERE id = {$_SESSION["user_id"]}";
            
    $result = $mysqli->query($sql);
    
    $user = $result->fetch_assoc();
}


?>
<!DOCTYPE html>
<html>
<head>
    <title>Entrar o registrarse</title>
    <meta charset="UTF-8">
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

<div class="main-content">
    <section class="form-container">
      <h2>Datos personales</h2>
      <form action="procesar.php" method="post">
        <div class="form-grid">
          <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" placeholder="Nombre" required>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required>
          </div>
        </div>

        <h2>Datos de envío</h2>
        <div class="form-grid">
          <div class="form-group wide">
            <label for="calle">Calle</label>
            <input type="text" id="calle" name="calle" placeholder="Calle" required>
          </div>
          <div class="form-group small">
            <label for="numero">Número</label>
            <input type="text" id="numero" name="numero" placeholder="Número" required>
          </div>
          <div class="form-group">
            <label for="cp">CP</label>
            <input type="text" id="cp" name="cp" placeholder="Código Postal" required>
          </div>
          <div class="form-group">
            <label for="colonia">Colonia</label>
            <input type="text" id="colonia" name="colonia" placeholder="Colonia" required>
          </div>
          <div class="form-group">
            <label for="estado">Estado</label>
            <input type="text" id="estado" name="estado" placeholder="Estado" required>
          </div>
          <div class="form-group">
            <label for="ciudad">Ciudad</label>
            <input type="text" id="ciudad" name="ciudad" placeholder="Ciudad" required>
          </div>
          <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" placeholder="Teléfono" required>
          </div>
          <div class="form-group">
            <label for="telefono2">Teléfono #2 (opcional)</label>
            <input type="text" id="telefono2" name="telefono2" placeholder="Opcional">
          </div>
        </div>

        <button type="submit">Guardar</button>
      </form>
    </section>

    <aside class="sidebar">
    <h3>Mi cuenta</h3>
    <ul>
        <?php if (isset($user)): ?>
            <li><strong><?= htmlspecialchars($user["name"]) ?></strong></li>
            <li><a href="#">Editar perfil</a></li>
            <li><a href="#">Cambiar contraseña</a></li>
            <li><a href="#">Mis pedidos</a></li>
            <li><a href="perfilguardado.php">Mi informacion</a></li>
            <li><a href="logout.php">Cerrar sesión</a></li>
        <?php else: ?>
            <li><a href="login.php">Iniciar sesión</a></li>
            <li><a href="signup.html">Registrarse</a></li>
        <?php endif; ?>
    </ul>
    </aside>
</div>






    
</body>
</html>
    
    
    
    
    
    
    
    
    
    
    