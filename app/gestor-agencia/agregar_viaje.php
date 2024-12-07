<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $origen = $_POST['origen'];
    $destino = $_POST['destino'];
    $fecha_salida = $_POST['fecha_salida'];
    $fecha_llegada = $_POST['fecha_llegada'];
    $precio = $_POST['precio'];
    $max_plazas = $_POST['max_plazas'];

    if (empty($origen) || empty($destino) || empty($fecha_salida) || empty($fecha_llegada) || empty($precio) || empty($max_plazas)) {
        $error = "Todos los campos son obligatorios.";
    } else {

        if (!isset($error)) {
            $sql = 'INSERT INTO viaje (origen, destino, fecha_salida, fecha_llegada, precio, max_plazas) VALUES (:origen, :destino, :fecha_salida, :fecha_llegada, :precio, :max_plazas)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':origen', $origen);
            $stmt->bindParam(':destino', $destino);
            $stmt->bindParam(':fecha_salida', $fecha_salida);
            $stmt->bindParam(':fecha_llegada', $fecha_llegada);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':max_plazas', $max_plazas);

            if ($stmt->execute()) {
                header('Location: viajes.php');
                exit;
            } else {
                $error = "Error al agregar el viaje.";
            }
        }
    }
}

include('header.php');
?>

<h2 class='titulo-seccion'>Añadir Nuevo Viaje</h2>

<?php if (isset($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="agregar_viaje.php" method="POST">
    <label for="origen">Origen:</label>
    <input type="text" id="origen" name="origen" required><br><br>

    <label for="destino">Destino:</label>
    <input type="text" id="destino" name="destino" required><br><br>

    <label for="fecha_salida">Fecha de salida:</label>
    <input type="date" id="fecha_salida" name="fecha_salida" required><br><br>

    <label for="fecha_llegada">Fecha de llegada:</label>
    <input type="date" id="fecha_llegada" name="fecha_llegada" required><br><br>

    <label for="precio">Precio:</label>
    <input type="number" id="precio" name="precio" min="1" required><br><br>

    <label for="max_plazas">Máx. Plazas:</label>
    <input type="number" id="max_plazas" name="max_plazas" min="0" required><br><br>

    <button type="submit" class="btn agregar">Agregar Viaje</button>
</form>

<p><a href="viajes.php" class="btn">Volver a la lista de viajes</a></p>

<?php
include('footer.php');
?>