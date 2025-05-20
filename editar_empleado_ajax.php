<?php
$id = $_POST['id'];
$name = $_POST['name'];
$email = $_POST['email'];
$calle = $_POST['calle'];
$numero = $_POST['numero'];
$colonia = $_POST['colonia'];
$cp = $_POST['cp'];
$ciudad = $_POST['ciudad'];
$estado = $_POST['estado'];
$telefono = $_POST['telefono'];
$puesto = $_POST['puesto'];
$salario = $_POST['salario'];


$pdo = new PDO("mysql:host=localhost;dbname=pasteleriadolceforno;charset=utf8", "root", "");
$pdo->beginTransaction();
try {
    $pdo->prepare("UPDATE empleado SET puesto = ?, salario = ? WHERE id = ?")
    ->execute([$puesto, $salario, $id]);


  $pdo->prepare("UPDATE user_info SET calle = ?, numero = ?, colonia = ?, cp = ?, ciudad = ?, estado = ?, telefono = ? WHERE user_id = ?")
      ->execute([$calle, $numero, $colonia, $cp, $ciudad, $estado, $telefono, $id]);

  $pdo->prepare("UPDATE empleado SET puesto = ? WHERE id = ?")
      ->execute([$puesto, $id]);

  $pdo->commit();
  echo "ok";
} catch (Exception $e) {
  $pdo->rollBack();
  echo "error: " . $e->getMessage();
}
