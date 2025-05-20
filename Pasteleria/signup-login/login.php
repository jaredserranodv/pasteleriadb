<?php

$is_invalid = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $mysqli = require __DIR__ . "/database.php";
    
    $sql = sprintf("SELECT * FROM user
                    WHERE email = '%s'",
                   $mysqli->real_escape_string($_POST["email"]));
    
    $result = $mysqli->query($sql);
    
    $user = $result->fetch_assoc();
    
    if ($user) {
        
          if (password_verify($_POST["password"], $user["password_hash"])) {

            session_start();
            
            session_regenerate_id();
            
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_type"] = $user["user_type"]; // <- ESTA LÍNEA
            
            header("Location: index.php");
            exit;
        }
    }
    
    $is_invalid = true;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Inicio de sesión</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="home.css">
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

    <section id="cuenta">
  <div class="container login-container">
    <div class="img-container"></div>

    <div class="texto login-form">
      <h2>Iniciar sesión</h2>

      <?php if ($is_invalid): ?>
        <em style="color: red; margin-bottom: 10px;">Correo o contraseña incorrectos</em>
      <?php endif; ?>

      <form method="post">
        <input 
          type="email" 
          name="email" 
          id="email" 
          placeholder="Correo electrónico"
          value="<?= htmlspecialchars($_POST["email"] ?? "") ?>" 
          required
        >

        <input 
          type="password" 
          name="password" 
          id="password" 
          placeholder="Contraseña"
          required
        >

        <button type="submit">Iniciar sesión</button>
      </form>
      <p class="signup-text">¿No tienes una cuenta? <a href="../../../Pasteleria_DB/Pasteleria/signup-login/signup.html">Regístrate</a></p>
    </div>
   
  </div>
</section>

</body>
</html>








