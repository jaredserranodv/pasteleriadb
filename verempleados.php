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

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=pasteleriadolceforno;charset=utf8",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Unimos las tablas user + user_info + empleado
    $sql = "
        SELECT 
            u.id,
            u.name,
            u.email,
            ui.calle,
            ui.numero,
            ui.colonia,
            ui.cp,
            ui.ciudad,
            ui.estado,
            ui.telefono,
            e.puesto,
            e.salario
        FROM empleado e
        JOIN user u     ON e.id       = u.id
        JOIN user_info ui ON u.id      = ui.user_id
        ORDER BY u.name
    ";
    $stmt = $pdo->query($sql);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Empleados</title>
  <link rel="stylesheet" href="admin.css">
  <style>body { font-family: Arial, sans-serif; font-size: 1.5em;}</style>
</head>
<body>

    <header>
            <div class="header-container">
            <a href="index.html"> <div class="img-container"></div> </a>
            <nav>
                <a href="#acerca-de">Acerca de</a>
                <a href="#menu">Menú</a>
                <a href="../Pasteleria_DB/Pasteleria/signup-login/mispedidos.php">Pedidos</a>
                <a href="galeria.html">Galería</a>
                <a href="#reseñas">Reseñas</a>
                <a href="carrito.html"> <img src="Pasteleria/carrito.png" alt="carrito" id="carrito-img"></a>
                <a href="Pasteleria/signup-login/login.php"> <img src="Pasteleria/usuario.png" alt="usuario" id="usuario-img"></a>
            </nav>
            </div>
    </header>


  <h1>Lista de empleados</h1>
  <button onclick="abrirModalCrear()">Crear nuevo empleado</button>

  <?php if (empty($empleados)): ?>
    <p>No hay empleados registrados.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Dirección</th>
          <th>Teléfono</th>
          <th>Puesto</th>
          <th>Salario</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($empleados as $emp): ?>
          <tr>
            <td><?= htmlspecialchars($emp['name']) ?></td>
            <td><?= htmlspecialchars($emp['email']) ?></td>
            <td>
              <?= htmlspecialchars($emp['calle']) ?> #<?= htmlspecialchars($emp['numero']) ?>,
              <?= htmlspecialchars($emp['colonia']) ?>, CP <?= htmlspecialchars($emp['cp']) ?>,
              <?= htmlspecialchars($emp['ciudad']) ?>, <?= htmlspecialchars($emp['estado']) ?>
            </td>
            <td><?= htmlspecialchars($emp['telefono']) ?></td>
            <td><?= htmlspecialchars($emp['puesto']) ?></td>
            <td><?= htmlspecialchars($emp['salario'],2) ?></td>
            <td>
            <button onclick="abrirModal(<?= $emp['id'] ?>)">Editar</button>
            <button class="btn-eliminar" data-id="<?= $empleado['id'] ?>">Eliminar</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <!-- Modal -->
<div id="modalEditar" class="modal">
  <div class="modal-content">
    <span class="cerrar" onclick="cerrarModal()">&times;</span>
    <h2>Editar Empleado</h2>
    <form id="formEditar">
      <input type="hidden" name="id" id="id">
      <label>Nombre: <input type="text" name="name" id="name" required></label><br>
      <label>Email: <input type="email" name="email" id="email" required></label><br>
      <label>Calle: <input type="text" name="calle" id="calle" required></label><br>
      <label>Número: <input type="text" name="numero" id="numero" required></label><br>
      <label>Colonia: <input type="text" name="colonia" id="colonia" required></label><br>
      <label>CP: <input type="text" name="cp" id="cp" required></label><br>
      <label>Ciudad: <input type="text" name="ciudad" id="ciudad" required></label><br>
      <label>Estado: <input type="text" name="estado" id="estado" required></label><br>
      <label>Teléfono: <input type="text" name="telefono" id="telefono" required></label><br>
      <label>Puesto: <input type="text" name="puesto" id="puesto" required></label><br><br>
      <label>Salario: <input type="number" step="0.01" name="salario" id="salario" required></label><br><br>

      <button type="submit">Guardar Cambios</button>
      <button type="button" onclick="cerrarModal()">Cancelar</button>
    </form>
  </div>
</div>

<!-- Modal Crear -->
<div id="modalCrear" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="cerrar" onclick="cerrarModalCrear()">&times;</span>
    <h2>Crear Empleado</h2>
    <form id="formCrear">
      <label>Nombre: <input type="text" name="name" required></label><br>
      <label>Email: <input type="email" name="email" required></label><br>
      <label>Contraseña: <input type="password" name="password" required></label><br>
      <label>Calle: <input type="text" name="calle" required></label><br>
      <label>Número: <input type="text" name="numero" required></label><br>
      <label>Colonia: <input type="text" name="colonia" required></label><br>
      <label>CP: <input type="text" name="cp" required></label><br>
      <label>Ciudad: <input type="text" name="ciudad" required></label><br>
      <label>Estado: <input type="text" name="estado" required></label><br>
      <label>Teléfono: <input type="text" name="telefono" required></label><br>
      <label>Puesto: <input type="text" name="puesto" required></label><br>
      <label>Salario: <input type="number" step="0.01" name="salario" required></label><br><br>
      <button type="submit">Crear</button>
      <button type="button" onclick="cerrarModalCrear()">Cancelar</button>
    </form>
  </div>
</div>


<script>

        function abrirModal(id) {
        fetch(`cargar_empleado.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
            document.getElementById('modalEditar').style.display = 'block';
            document.getElementById('id').value = id;
            document.getElementById('name').value = data.name;
            document.getElementById('email').value = data.email;
            document.getElementById('calle').value = data.calle;
            document.getElementById('numero').value = data.numero;
            document.getElementById('colonia').value = data.colonia;
            document.getElementById('cp').value = data.cp;
            document.getElementById('ciudad').value = data.ciudad;
            document.getElementById('estado').value = data.estado;
            document.getElementById('telefono').value = data.telefono;
            document.getElementById('puesto').value = data.puesto;
            document.getElementById('salario').value = data.salario;
            });
        }

        function cerrarModal() {
        document.getElementById('modalEditar').style.display = 'none';
        }

        document.getElementById('formEditar').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('editar_empleado_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(response => {
            alert('Empleado actualizado correctamente');
            cerrarModal();
            location.reload(); // recargar la tabla
        })
        .catch(err => alert('Error al guardar cambios'));
        });

        document.querySelectorAll('.btn-eliminar').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            if(confirm("¿Seguro que quieres eliminar este empleado?")) {
            fetch('eliminar_empleado.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + encodeURIComponent(id)
            })
            .then(response => response.text())
            .then(data => {
                if(data === 'ok') {
                alert('Empleado eliminado correctamente.');
                // Refrescar tabla o eliminar fila sin recargar página:
                this.closest('tr').remove();
                } else {
                alert('Error al eliminar empleado: ' + data);
                }
            })
            .catch(err => alert('Error en la petición: ' + err));
            }
        });
        });

        function abrirModalCrear() {
        document.getElementById('modalCrear').style.display = 'block';
        }

        function cerrarModalCrear() {
        document.getElementById('modalCrear').style.display = 'none';
        }

        document.getElementById('formCrear').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('crear_empleado.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            if (data === 'ok') {
            alert('Empleado creado correctamente');
            cerrarModalCrear();
            location.reload(); // Refrescar tabla
            } else {
            alert('Error al crear empleado: ' + data);
            }
        })
        .catch(err => alert('Error en la petición: ' + err));
        });


</script>


</body>
</html>
