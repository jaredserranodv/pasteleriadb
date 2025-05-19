<?php

$conn = mysqli_connect('localhost', 'root', '', 'pasteleriadolceforno');

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}


$id = $_GET['edit'];

if(isset($_POST['update_product'])){

   $id = $_POST['product_id']; // Asegúrate de que este campo esté presente en tu formulario

   $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
   $product_desc = mysqli_real_escape_string($conn, $_POST['product_desc']);
   $product_price = floatval($_POST['product_price']);
   $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
   $product_quantity = intval($_POST['product_quantity']);

   $product_image = $_FILES['product_image']['name'];
   $product_image_tmp_name = $_FILES['product_image']['tmp_name'];
   $product_image_folder = 'uploaded_img/'.$product_image;

   if(empty($product_name) || empty($product_desc) || empty($product_price) || empty($product_category) || empty($product_quantity)){
      $message[] = 'Por favor llena todos los campos obligatorios';
   } else {
      if(!empty($product_image)){
         // Con nueva imagen
         $update_query = "UPDATE productos SET 
            nombre = '$product_name',
            descripcion = '$product_desc',
            precio = $product_price,
            categoria = '$product_category',
            cantidad = $product_quantity,
            imagen = '$product_image'
            WHERE id = $id";

         $upload = mysqli_query($conn, $update_query);

         if($upload){
            move_uploaded_file($product_image_tmp_name, $product_image_folder);
            header('Location: admin_page.php');
            exit;
         } else {
            $message[] = 'No se pudo actualizar el producto';
         }
      } else {
         // Sin nueva imagen
         $update_query = "UPDATE productos SET 
            nombre = '$product_name',
            descripcion = '$product_desc',
            precio = $product_price,
            categoria = '$product_category',
            cantidad = $product_quantity
            WHERE id = $id";

         $upload = mysqli_query($conn, $update_query);

         if($upload){
            header('Location: admin_page.php');
            exit;
         } else {
            $message[] = 'No se pudo actualizar el producto';
         }
      }
   }
};

?>

<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="admin.css">
</head>
<body>

<?php
   if(isset($message)){
      foreach($message as $message){
         echo '<span class="message">'.$message.'</span>';
      }
   }
?>

<div class="container">
      <div class="admin-product-form-container centered">
         <?php
                  
            $select = mysqli_query($conn, "SELECT * FROM productos WHERE id = '$id'");
            while($row = mysqli_fetch_assoc($select)){

         ?>
            <form action="" method="post" enctype="multipart/form-data">
            <h3 class="title">Actualizar producto</h3>

            <!-- ID oculto -->
            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">

            <input type="text" class="box" name="product_name" value="<?php echo htmlspecialchars($row['nombre']); ?>" placeholder="Nombre del producto">

            <textarea class="box" name="product_desc" placeholder="Descripción del producto"><?php echo htmlspecialchars($row['descripcion']); ?></textarea>

            <input type="number" step="0.01" min="0" class="box" name="product_price" value="<?php echo $row['precio']; ?>" placeholder="Precio del producto">

            <input type="text" class="box" name="product_category" value="<?php echo htmlspecialchars($row['categoria']); ?>" placeholder="Categoría del producto">

            <input type="number" min="0" class="box" name="product_quantity" value="<?php echo $row['cantidad']; ?>" placeholder="Cantidad disponible">

            <p>Imagen actual:</p>
            <img src="uploaded_img/<?php echo $row['imagen']; ?>" height="100" alt="">
            
            <input type="file" class="box" name="product_image" accept="image/png, image/jpeg, image/jpg">

            <input type="submit" value="Actualizar producto" name="update_product" class="btn">
            <a href="admin_page.php" class="btn">Regresar</a>
         </form>

         <?php }; ?>

      </div>
</div>

</body>
</html>