<?php
// Incluir el archivo de conexión a la base de datos
include('db.php');

// Obtener el id del teléfono a editar
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de teléfono no especificado.");
}

$id_telefono = $_GET['id'];

// Obtener el teléfono actual
$stmt = $pdo->prepare("SELECT * FROM telefono WHERE telefono = :id_telefono");
$stmt->bindParam(':id_telefono', $id_telefono);
$stmt->execute();
$telefono = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no existe el teléfono, redirigir
if (!$telefono) {
    die("Teléfono no encontrado.");
}

// Inicializar la variable de error
$error = '';

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cliente = $_POST['id_cliente'];
    $telefono_nuevo = $_POST['telefono'];

    // Validación de los campos
    if (empty($id_cliente) || empty($telefono_nuevo)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Actualizar el teléfono en la base de datos
        $sql = "UPDATE telefono SET id_cliente = :id_cliente, telefono = :telefono WHERE telefono = :id_telefono";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':telefono', $telefono_nuevo);
        $stmt->bindParam(':id_telefono', $id_telefono);

        if ($stmt->execute()) {
            // Redirigir a la lista de teléfonos
            header('Location: telefonos.php');
            exit;
        } else {
            $error = "Hubo un error al editar el teléfono.";
        }
    }
}

// Obtener la lista de clientes
$stmt = $pdo->query("SELECT id_cliente, nombre FROM cliente");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Incluir el encabezado
include('header.php');
?>

<h2 class="titulo-seccion">Editar Teléfono</h2>

<!-- Mostrar un mensaje de error si existe -->
<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- Formulario para editar el teléfono -->
<form action="editar_telefono.php?id=<?= $telefono['telefono'] ?>" method="POST">
    <label for="id_cliente">Cliente:</label>
    <select name="id_cliente" id="id_cliente" required>
        <option value="">Seleccionar cliente</option>
        <?php foreach ($clientes as $cliente): ?>
            <option value="<?= $cliente['id_cliente'] ?>" <?= ($cliente['id_cliente'] == $telefono['id_cliente']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cliente['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="telefono">Teléfono:</label>
    <input type="text" name="telefono" id="telefono" value="<?= htmlspecialchars($telefono['telefono']) ?>" required><br><br>

    <button type="submit" class="btn">Actualizar Teléfono</button>
</form>

<p><a href="telefonos.php" class="btn">Volver a la lista de teléfonos</a></p>

<?php
// Incluir el pie de página
include('footer.php');
?>