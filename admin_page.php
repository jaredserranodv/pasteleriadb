<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    // Si no hay sesión iniciada, redirige a login
    header("Location: ../Pasteleria/signup-login/login.php");
    exit;
}

$conn = mysqli_connect('localhost', 'root', '', 'pasteleriadolceforno');

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
$user_id = $_SESSION["user_id"];

// Verificar si el usuario es empleado (admin)
$stmt = $conn->prepare("SELECT 1 FROM empleado WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // No es empleado => no es admin => acceso denegado
    header("Location: ../Pasteleria/signup-login/login.php");
    exit;
}

$stmt->close();

if(isset($_POST['add_product'])){

   $product_name = $_POST['product_name'];
   $product_desc = $_POST['product_desc'];
   $product_price = $_POST['product_price'];
   $product_category = $_POST['product_category'];
   $product_quantity = $_POST['product_quantity'];
   $product_image = $_FILES['product_image']['name'];
   $product_image_tmp_name = $_FILES['product_image']['tmp_name'];
   $product_image_folder = 'uploaded_img/'.$product_image;

   // Validar que no estén vacíos
   if(empty($product_name) || empty($product_desc) || empty($product_price) || empty($product_category) || empty($product_quantity) || empty($product_image)){
      $message[] = 'Por favor llena todos los campos';
   }else{
      // Escapar strings para evitar inyección
      $product_name = mysqli_real_escape_string($conn, $product_name);
      $product_desc = mysqli_real_escape_string($conn, $product_desc);
      $product_category = mysqli_real_escape_string($conn, $product_category);
      $product_price = floatval($product_price);
      $product_quantity = intval($product_quantity);

      $insert = "INSERT INTO productos(nombre, descripcion, precio, categoria, cantidad, imagen) 
                 VALUES ('$product_name', '$product_desc', $product_price, '$product_category', $product_quantity, '$product_image')";

      $upload = mysqli_query($conn, $insert);

      if($upload){
         move_uploaded_file($product_image_tmp_name, $product_image_folder);
         $message[] = 'Producto añadido correctamente';
      }else{
         $message[] = 'No se pudo añadir el producto';
      }
   }

};

if(isset($_GET['delete'])){
   $id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM productos WHERE id = $id");
   header('location:admin_page.php');
};

?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Inventario</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
   <link rel="stylesheet" href="admin.css">

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

         <div class="admin-container">
         <aside class="sidebar">
            <h2>Panel Admin</h2>
            <a href="../Pasteleria_DB/admin_pedidos.php">📦 Ver pedidos</a>
            <a href="../Pasteleria_DB/verempleados.php">👥 Ver empleados</a>
            <a href="../Pasteleria_DB/ver_reseñas.php">⭐ Ver reseñas</a>
            <a href="admin_page.php?add=1" class="add-product-btn">➕ Agregar producto</a>
         </aside>

         <main class="admin-main-content">
            <?php
            if(isset($message)){
               foreach($message as $message){
                  echo '<span class="message">'.$message.'</span>';
               }
            }
            ?>

               <?php

               $select = mysqli_query($conn, "SELECT * FROM productos");
               
               ?>
      <div class="product-display">
      <div class="tituloinventario">
         <h2>Inventario</h2>
      </div>

      <!-- Filtro de ordenamiento -->
      <label for="ordenInventario">Ordenar por:</label>
      <select id="ordenInventario">
         <option value="cantidad-desc">Cantidad (mayor a menor)</option>
         <option value="cantidad-asc">Cantidad (menor a mayor)</option>
         <option value="precio-desc">Precio (mayor a menor)</option>
         <option value="precio-asc">Precio (menor a mayor)</option>
         <option value="categoria-asc">Categoría (A-Z)</option>
         <option value="categoria-desc">Categoría (Z-A)</option>
      </select>

      <table class="product-display-table" id="tablaInventario">
         <thead>
            <tr>
            <th>Imagen</th>
            <th>Nombre del producto</th>
            <th>Descripción</th>
            <th>Categoría</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Acción</th>
            </tr>
         </thead>
         <tbody>
            <?php while($row = mysqli_fetch_assoc($select)): ?>
            <tr>
               <td><img src="images/<?= htmlspecialchars($row['imagen']); ?>" height="100" alt=""></td>
               <td><?= htmlspecialchars($row['nombre']); ?></td>
               <td><?= htmlspecialchars($row['descripcion']); ?></td>
               <td><?= htmlspecialchars($row['categoria']); ?></td>
               <td><?= (int)$row['cantidad']; ?></td>
               <td>$<?= number_format($row['precio'], 2); ?></td>
               <td>
                  <a href="admin_update.php?edit=<?= (int)$row['id']; ?>" class="btn">
                  <i class="fas fa-edit"></i> Editar
                  </a>
                  <a href="admin_page.php?delete=<?= (int)$row['id']; ?>" class="btn">
                  <i class="fas fa-trash"></i> Eliminar
                  </a>
               </td>
            </tr>
            <?php endwhile; ?>
         </tbody>
      </table>
      </div>

         <!-- Script de ordenamiento -->
         <script>
         const selectOrden = document.getElementById('ordenInventario');
         const tabla = document.getElementById('tablaInventario').querySelector('tbody');

         selectOrden.addEventListener('change', () => {
            const filas = Array.from(tabla.querySelectorAll('tr'));
            const valor = selectOrden.value;

            filas.sort((a, b) => {
               const cantidadA = parseInt(a.cells[4].textContent);
               const cantidadB = parseInt(b.cells[4].textContent);
               const precioA = parseFloat(a.cells[5].textContent.replace('$', ''));
               const precioB = parseFloat(b.cells[5].textContent.replace('$', ''));
               const categoriaA = a.cells[3].textContent.trim().toLowerCase();
               const categoriaB = b.cells[3].textContent.trim().toLowerCase();

               switch (valor) {
               case 'cantidad-desc':
                  return cantidadB - cantidadA;
               case 'cantidad-asc':
                  return cantidadA - cantidadB;
               case 'precio-desc':
                  return precioB - precioA;
               case 'precio-asc':
                  return precioA - precioB;
               case 'categoria-asc':
                  return categoriaA.localeCompare(categoriaB);
               case 'categoria-desc':
                  return categoriaB.localeCompare(categoriaA);
               default:
                  return 0;
               }
            });

            filas.forEach(fila => tabla.appendChild(fila));
         });
         </script>


               <!-- Botones de acción 
                     <a href="admin_page.php?add=1" class="btn">Agregar producto</a>
                     <a href="../Pasteleria_DB/admin_pedidos.php" class="btn btn-view-orders">Ver pedidos</a>
                     <a href="../Pasteleria_DB/verempleados.php" class="btn btn-view-orders">Ver empleados</a>
                     <a href="../Pasteleria_DB/ver_reseñas.php" class="btn btn-view-orders">Ver reseñas</a>
                     -->

                     <?php if (isset($_GET['add']) && $_GET['add'] == 1): ?>
                     <div class="modal-overlay">
                     <div class="modal-content">
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                           <h3>Añadir un nuevo producto</h3>
                           <input type="text" name="product_name" placeholder="Ingresa el nombre del producto" class="box" required>
                           <textarea name="product_desc" placeholder="Ingresa la descripción del producto" class="box" required></textarea>
                           <input type="number" step="0.01" name="product_price" placeholder="Ingresa el precio del producto" class="box" required>
                           <input type="text" name="product_category" placeholder="Ingresa la categoría del producto" class="box" required>
                           <input type="number" name="product_quantity" placeholder="Ingresa la cantidad disponible" class="box" required>
                           <input type="file" accept="image/png, image/jpeg, image/jpg" name="product_image" class="box" required>
                           <input type="submit" class="btn" name="add_product" value="Añadir producto">
                           <a href="admin_page.php" class="btn btn-cancel">Cancelar</a>
                        </form>
                     </div>
                     </div>
                     <?php endif; ?>

                  <?php if (isset($_GET['add']) && $_GET['add'] == 1): ?>

               <a href="admin_page.php" class="btn">Cancelar</a>
                  <?php endif; ?>


      </div>
   </main>
   </div>


</body>
</html>