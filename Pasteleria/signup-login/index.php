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
    <link rel="stylesheet" href="home.css">
    
</head>
<body>
<header>
        <div class="container">
            <a href="index.html"> <div class="img-container"></div> </a>
            <nav>
                <a href="#acerca-de">Acerca de</a>
                <a href="#menu">Menú</a>
                <a href="#">Pedidos</a>
                <a href="galeria.html">Galería</a>
                <a href="#reseñas">Reseñas</a>
                <a href="#">Abastecimiento</a>
                <a href=""> <img src="Pasteleria/carrito.png" alt="carrito" id="carrito-img"></a>
                <a href="C"> <img src="Pasteleria/usuario.png" alt="usuario" id="usuario-img"></a>
            </nav>
        </div>  
</header>

    <section id="cuenta">
        <div class="container">
            <div class="img-container"></div>
            <div class="texto">
            <h2>Inicia sesion</h2>
            <p>Somos mucho más que una simple pasteleria; somos un equipo apasionado por endulzar la vida de nuestros clientes. Desde nuestros inicios, nos hemos comprometido a ofrecer productos de la más alta calidad, elaborados con dedicación, creatividad y un toque de autenticidad que nos distingue. En Dolce Forno, no solo creamos pasteles; creamos sonrisas. Te invitamos a ser parte de nuestra dulce historia y a descubrir por qué somos la elección preferida de quienes buscan calidad, innovación y autenticidad en cada bocado.</p>
            </div>    
        </div>
    </section>
    
    <?php if (isset($user)): ?>
        
        <p>Hello <?= htmlspecialchars($user["name"]) ?></p>
        
        <p><a href="logout.php">Log out</a></p>
        
    <?php else: ?>
        
        <p><a href="login.php">Log in</a> or <a href="signup.html">sign up</a></p>
        
    <?php endif; ?>
    
</body>
</html>
    
    
    
    
    
    
    
    
    
    
    