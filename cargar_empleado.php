<?php
$id = $_GET['id'];
$pdo = new PDO("mysql:host=localhost;dbname=pasteleriadolceforno;charset=utf8", "root", "");
$stmt = $pdo->prepare("
  SELECT u.name, u.email, ui.calle, ui.numero, ui.colonia, ui.cp, ui.ciudad, ui.estado, ui.telefono, e.puesto, e.salario
  FROM empleado e
  JOIN user u ON e.id = u.id
  JOIN user_info ui ON u.id = ui.user_id
  WHERE u.id = ?
");

$stmt->execute([$id]);
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
