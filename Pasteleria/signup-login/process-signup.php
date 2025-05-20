<?php

if (empty($_POST["name"])) {
    die("Name is required");
}

if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die("Valid email is required");
}

if (strlen($_POST["password"]) < 8) {
    die("Password must be at least 8 characters");
}

if (!preg_match("/[a-z]/i", $_POST["password"])) {
    die("Password must contain at least one letter");
}

if (!preg_match("/[0-9]/", $_POST["password"])) {
    die("Password must contain at least one number");
}

if ($_POST["password"] !== $_POST["password_confirmation"]) {
    die("Passwords must match");
}

$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

$mysqli = require __DIR__ . "/database.php";

// Verificar si el correo ya existe
$sql_check = "SELECT id FROM user WHERE email = ?";
$stmt_check = $mysqli->prepare($sql_check);
$stmt_check->bind_param("s", $_POST["email"]);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    die("Este correo ya está registrado.");
}

// Insertar en tabla user
$sql = "INSERT INTO user (name, email, password_hash, created_at)
        VALUES (?, ?, ?, NOW())";

$stmt = $mysqli->stmt_init();

if (!$stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}

$stmt->bind_param("sss",
    $_POST["name"],
    $_POST["email"],
    $password_hash
);

if ($stmt->execute()) {
    $user_id = $mysqli->insert_id;

    // Insertar en tabla cliente con user_id
    $sql_cliente = "INSERT INTO cliente (user_id) VALUES (?)";
    $stmt_cliente = $mysqli->prepare($sql_cliente);
    $stmt_cliente->bind_param("i", $user_id);

    if ($stmt_cliente->execute()) {
        header("Location: login.php");
        exit;
    } else {
        die("Error al crear cliente: " . $stmt_cliente->error);
    }

} else {
    die("Error al registrar usuario: " . $stmt->error);
}
