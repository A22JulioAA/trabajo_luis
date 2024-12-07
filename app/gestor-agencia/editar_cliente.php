<?php
// Incluir el archivo de conexión a la base de datos
include('db.php');

// Verificar si se ha proporcionado un id en la URL
if (isset($_GET['id'])) {
    $id_cliente = $_GET['id'];

    // Validar que el id sea un número entero
    if (filter_var($id_cliente, FILTER_VALIDATE_INT)) {
        // Preparar la consulta para obtener los datos del cliente
        $sql = 'SELECT * FROM cliente WHERE id_cliente = :id_cliente';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->execute();

        // Comprobar si el cliente existe
        if ($stmt->rowCount() > 0) {
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // Si no existe el cliente, redirigir a la lista de clientes
            header('Location: clientes.php');
            exit;
        }
    } else {
        // Si el id no es válido
        header('Location: clientes.php');
        exit;
    }
} else {
    // Si no se ha proporcionado un id
    header('Location: clientes.php');
    exit;
}

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $dni = $_POST['dni'];
    $correo = $_POST['correo'];

    // Validación
    if (empty($nombre) || empty($apellidos) || empty($dni) || empty($correo)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Validar DNI
        if (!preg_match("/^\d{8}[A-Za-z]$/", $dni)) {
            $error = "El DNI debe tener 8 dígitos y una letra.";
        }
        // Validar correo
        elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $error = "El correo electrónico no es válido.";
        } else {
            // Comprobar si ya existe otro cliente con el mismo DNI o correo
            $stmt = $pdo->prepare("SELECT id_cliente FROM cliente WHERE dni = :dni AND id_cliente != :id_cliente");
            $stmt->bindParam(':dni', $dni);
            $stmt->bindParam(':id_cliente', $id_cliente);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error = "Ya existe un cliente con ese DNI.";
            } else {
                $stmt = $pdo->prepare("SELECT id_cliente FROM cliente WHERE correo_electronico = :correo AND id_cliente != :id_cliente");
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':id_cliente', $id_cliente);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $error = "Ya existe un cliente con ese correo electrónico.";
                } else {
                    // Si todo está bien, actualizar el cliente en la base de datos
                    $sql = 'UPDATE cliente SET nombre = :nombre, apellidos = :apellidos, dni = :dni, correo_electronico = :correo WHERE id_cliente = :id_cliente';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':nombre', $nombre);
                    $stmt->bindParam(':apellidos', $apellidos);
                    $stmt->bindParam(':dni', $dni);
                    $stmt->bindParam(':correo', $correo);
                    $stmt->bindParam(':id_cliente', $id_cliente);

                    if ($stmt->execute()) {
                        // Redirigir a la lista de clientes si la actualización es exitosa
                        header('Location: clientes.php');
                        exit;
                    } else {
                        $error = "Error al actualizar los datos del cliente.";
                    }
                }
            }
        }
    }
}
?>

<?php include('header.php'); ?>

<h2 class="titulo-seccion">Editar Cliente</h2>

<!-- Mostrar un mensaje de error si existe -->
<?php if (isset($error)): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<!-- Formulario de edición -->
<form method="POST">
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($cliente['nombre']) ?>" required>

    <label for="apellidos">Apellidos:</label>
    <input type="text" name="apellidos" id="apellidos" value="<?= htmlspecialchars($cliente['apellidos']) ?>" required>

    <label for="dni">DNI:</label>
    <input type="text" name="dni" id="dni" value="<?= htmlspecialchars($cliente['dni']) ?>" required>

    <label for="correo">Correo electrónico:</label>
    <input type="email" name="correo" id="correo" value="<?= htmlspecialchars($cliente['correo_electronico']) ?>" required>

    <button type="submit" class="btn">Actualizar Cliente</button>
</form>

<?php include('footer.php'); ?>