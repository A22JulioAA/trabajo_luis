<?php
// Incluir el archivo de conexión a la base de datos
include('db.php');

// Inicializar la variable de error
$error = '';

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cliente = $_POST['id_cliente'];
    $telefono = $_POST['telefono'];

    // Validación de los campos
    if (empty($id_cliente) || empty($telefono)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Verificar si el teléfono ya está registrado para el cliente
        $stmt = $pdo->prepare("SELECT * FROM telefono WHERE id_cliente = :id_cliente AND telefono = :telefono");
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = "Este teléfono ya está registrado para este cliente.";
        } else {
            // Insertar el nuevo teléfono en la base de datos
            $sql = "INSERT INTO telefono (id_cliente, telefono) VALUES (:id_cliente, :telefono)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_cliente', $id_cliente);
            $stmt->bindParam(':telefono', $telefono);

            if ($stmt->execute()) {
                // Redirigir a la lista de teléfonos
                header('Location: telefonos.php');
                exit;
            } else {
                $error = "Hubo un error al agregar el teléfono.";
            }
        }
    }
}

// Obtener la lista de clientes
$stmt = $pdo->query("SELECT id_cliente, nombre FROM cliente");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Incluir el encabezado
include('header.php');
?>

<h2 class="titulo-seccion">Agregar Teléfono</h2>

<!-- Mostrar un mensaje de error si existe -->
<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- Formulario para agregar un teléfono -->
<form action="agregar_telefono.php" method="POST">
    <label for="id_cliente">Cliente:</label>
    <select name="id_cliente" id="id_cliente" required>
        <option value="">Seleccionar cliente</option>
        <?php foreach ($clientes as $cliente): ?>
            <option value="<?= $cliente['id_cliente'] ?>"><?= htmlspecialchars($cliente['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="telefono">Teléfono:</label>
    <input type="text" name="telefono" id="telefono" required><br><br>

    <button type="submit" class="btn">Agregar Teléfono</button>
</form>

<p><a href="telefonos.php" class="btn">Volver a la lista de teléfonos</a></p>

<?php
// Incluir el pie de página
include('footer.php');
?>