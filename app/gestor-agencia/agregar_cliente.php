<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $dni = $_POST['dni'];
    $correo = $_POST['correo'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];
        $dni = $_POST['dni'];
        $correo = $_POST['correo'];

        if (empty($nombre) || empty($apellidos) || empty($dni) || empty($correo)) {
            $error = "Todos los campos son obligatorios.";
        } else {

            if (!preg_match("/^\d{8}[A-Za-z]$/", $dni)) {
                $error = "El DNI debe tener 8 dígitos y una letra.";
            } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $error = "El correo electrónico no es válido.";
            } elseif ($stmt = $pdo->prepare("SELECT id_cliente FROM cliente WHERE dni = :dni")) {
                $stmt->bindParam(":dni", $dni);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $error = "Ya existe un cliente con ese DNI.";
                } elseif ($stmt = $pdo->prepare("SELECT id_cliente FROM cliente WHERE correo_electronico = :correo")) {
                    $stmt->bindParam(":correo", $correo);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        $error = "Ya existe un cliente con ese correo electrónico.";
                    }
                }
            }

            if (!isset($error)) {
                $sql = 'INSERT INTO cliente (nombre, apellidos, dni, correo_electronico) VALUES (:nombre, :apellidos, :dni, :correo)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':apellidos', $apellidos);
                $stmt->bindParam(':dni', $dni);
                $stmt->bindParam(':correo', $correo);

                if ($stmt->execute()) {
                    header('Location: clientes.php');
                    exit;
                } else {
                    $error = "Error al agregar el cliente.";
                }
            }
        }
    }
}

include('header.php');
?>

<h2 class='titulo-seccion'>Añadir Nuevo Cliente</h2>

<?php if (isset($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="agregar_cliente.php" method="POST">
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" required><br><br>

    <label for="apellidos">Apellidos:</label>
    <input type="text" id="apellidos" name="apellidos" required><br><br>

    <label for="dni">DNI:</label>
    <input type="text" id="dni" name="dni" required><br><br>

    <label for="correo">Correo Electrónico:</label>
    <input type="email" id="correo" name="correo" required><br><br>

    <button type="submit" class="btn agregar">Agregar Cliente</button>
</form>

<p><a href="clientes.php" class="btn">Volver a la lista de clientes</a></p>

<?php
include('footer.php');
?>