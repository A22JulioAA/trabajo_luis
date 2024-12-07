<?php
// Incluir el archivo de conexión a la base de datos
include('db.php');

// Verificar si se ha proporcionado un id en la URL
if (isset($_GET['id'])) {
    $id_viaje = $_GET['id'];

    // Validar que el id sea un número entero
    if (filter_var($id_viaje, FILTER_VALIDATE_INT)) {
        // Preparar la consulta para obtener los datos del viaje
        $sql = 'SELECT * FROM viaje WHERE id_viaje = :id_viaje';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_viaje', $id_viaje);
        $stmt->execute();

        // Comprobar si el viaje existe
        if ($stmt->rowCount() > 0) {
            $viaje = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // Si no existe el viaje, redirigir a la lista de viajes
            header('Location: viajes.php');
            exit;
        }
    } else {
        // Si el id no es válido
        header('Location: viajes.php');
        exit;
    }
} else {
    // Si no se ha proporcionado un id
    header('Location: viajes.php');
    exit;
}

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $origen = trim($_POST['origen']);
    $destino = trim($_POST['destino']);
    $fecha_salida = $_POST['fecha_salida'];
    $fecha_llegada = $_POST['fecha_llegada'];
    $precio = (float) $_POST['precio'];
    $max_plazas = (int) $_POST['max_plazas'];

    // Validación
    if (empty($origen) || empty($destino) || empty($fecha_salida) || empty($fecha_llegada) || $precio <= 0 || $max_plazas < 0) {
        $error = "Todos los campos son obligatorios y deben ser válidos.";
    } elseif ($fecha_salida >= $fecha_llegada) {
        $error = "La fecha de salida debe ser anterior a la fecha de llegada.";
    } else {
        // Si todo está bien, actualizar el viaje en la base de datos
        $sql = 'UPDATE viaje 
                SET origen = :origen, destino = :destino, fecha_salida = :fecha_salida, 
                    fecha_llegada = :fecha_llegada, precio = :precio, max_plazas = :max_plazas 
                WHERE id_viaje = :id_viaje';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':origen', $origen);
        $stmt->bindParam(':destino', $destino);
        $stmt->bindParam(':fecha_salida', $fecha_salida);
        $stmt->bindParam(':fecha_llegada', $fecha_llegada);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':max_plazas', $max_plazas);
        $stmt->bindParam(':id_viaje', $id_viaje);

        if ($stmt->execute()) {
            // Redirigir a la lista de viajes si la actualización es exitosa
            header('Location: viajes.php');
            exit;
        } else {
            $error = "Error al actualizar los datos del viaje.";
        }
    }
}
?>

<?php include('header.php'); ?>

<h2 class="titulo-seccion">Editar Viaje</h2>

<!-- Mostrar un mensaje de error si existe -->
<?php if (isset($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- Formulario de edición -->
<form method="POST">
    <label for="origen">Origen:</label>
    <input type="text" name="origen" id="origen" value="<?= htmlspecialchars($viaje['origen']) ?>" required>

    <label for="destino">Destino:</label>
    <input type="text" name="destino" id="destino" value="<?= htmlspecialchars($viaje['destino']) ?>" required>

    <label for="fecha_salida">Fecha de salida:</label>
    <input type="date" name="fecha_salida" id="fecha_salida"
        value="<?= htmlspecialchars(date('Y-m-d', strtotime($viaje['fecha_salida']))) ?>" required>

    <label for="fecha_llegada">Fecha de llegada:</label>
    <input type="date" name="fecha_llegada" id="fecha_llegada"
        value="<?= htmlspecialchars(date('Y-m-d', strtotime($viaje['fecha_llegada']))) ?>" required>

    <label for="precio">Precio:</label>
    <input type="number" name="precio" id="precio" min="1" value="<?= htmlspecialchars($viaje['precio']) ?>" required>

    <label for="max_plazas">Máx. Plazas:</label>
    <input type="number" name="max_plazas" id="max_plazas" min="0" value="<?= htmlspecialchars($viaje['max_plazas']) ?>" required>

    <button type="submit" class="btn">Actualizar Viaje</button>
</form>


<?php include('footer.php'); ?>