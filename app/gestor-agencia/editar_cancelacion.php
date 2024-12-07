<?php
include('db.php');

// Obtener el ID de la cancelación desde la URL
if (isset($_GET['id'])) {
    $id_cancelacion = $_GET['id'];

    // Obtener los datos de la cancelación
    $stmt = $pdo->prepare("SELECT * FROM cancelacion WHERE id_cancelacion = :id_cancelacion");
    $stmt->bindParam(':id_cancelacion', $id_cancelacion);
    $stmt->execute();
    $cancelacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cancelacion) {
        die('Cancelación no encontrada.');
    }

    // Obtener la lista de reservas
    $stmt = $pdo->prepare("SELECT id_reserva, id_cliente, id_viaje FROM reserva WHERE cancelada = FALSE");
    $stmt->execute();
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar el formulario
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_reserva = $_POST['id_reserva'];
        $penalizacion = 60; // Penalización fija de 60€

        // Actualizar la cancelación
        $stmt = $pdo->prepare("UPDATE cancelacion SET id_reserva = :id_reserva, penalizacion = :penalizacion, fecha_cancelacion = NOW() WHERE id_cancelacion = :id_cancelacion");
        $stmt->bindParam(':id_reserva', $id_reserva);
        $stmt->bindParam(':penalizacion', $penalizacion);
        $stmt->bindParam(':id_cancelacion', $id_cancelacion);

        if ($stmt->execute()) {
            header('Location: cancelaciones.php');
            exit;
        } else {
            $error = "Error al actualizar la cancelación.";
        }
    }
} else {
    die('ID de cancelación no proporcionado.');
}

include('header.php');
?>

<h2 class="titulo-seccion">Editar Cancelación</h2>

<!-- Mostrar un mensaje de error si existe -->
<?php if (isset($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="editar_cancelacion.php?id=<?= $id_cancelacion ?>" method="POST">
    <label for="id_reserva">Reserva:</label>
    <select name="id_reserva" id="id_reserva" required>
        <option value="">Seleccionar reserva</option>
        <?php foreach ($reservas as $reserva): ?>
            <option value="<?= $reserva['id_reserva'] ?>" <?= ($reserva['id_reserva'] == $cancelacion['id_reserva']) ? 'selected' : '' ?>>
                Reserva #<?= $reserva['id_reserva'] ?> (Cliente: <?= $reserva['id_cliente'] ?>, Viaje: <?= $reserva['id_viaje'] ?>)
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="penalizacion">Penalización:</label>
    <input type="number" name="penalizacion" id="penalizacion" value="60" readonly><br><br>

    <button type="submit" class="btn">Actualizar Cancelación</button>
</form>

<p><a href="cancelaciones.php" class="btn">Volver a la lista de cancelaciones</a></p>

<?php include('footer.php'); ?>