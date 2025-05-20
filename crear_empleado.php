<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=pasteleriadolceforno;charset=utf8", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 1. Crear usuario
        $stmt = $pdo->prepare("INSERT INTO user (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
        $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt->execute([$_POST['name'], $_POST['email'], $password_hash]);
        $user_id = $pdo->lastInsertId();

        // 2. Crear info de usuario
        $stmt = $pdo->prepare("INSERT INTO user_info (user_id, calle, numero, colonia, cp, ciudad, estado, telefono) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $_POST['calle'],
            $_POST['numero'],
            $_POST['colonia'],
            $_POST['cp'],
            $_POST['ciudad'],
            $_POST['estado'],
            $_POST['telefono']
        ]);

        // 3. Crear entrada en empleado
        $stmt = $pdo->prepare("INSERT INTO empleado (id, puesto, salario) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $_POST['puesto'], $_POST['salario']]);

        echo "ok";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Método no permitido";
}
