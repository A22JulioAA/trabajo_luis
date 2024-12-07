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

    // Eliminar la cancelación si se confirma
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $stmt = $pdo->prepare("DELETE FROM cancelacion WHERE id_cancelacion = :id_cancelacion");
        $stmt->bindParam(':id_cancelacion', $id_cancelacion);

        if ($stmt->execute()) {
            header('Location: cancelaciones.php');
            exit;
        } else {
            $error = "Error al eliminar la cancelación.";
        }
    }
} else {
    die('ID de cancelación no proporcionado.');
}

include('header.php');
?>

<h2 class="titulo-seccion">Eliminar Cancelación</h2>

<!-- Mostrar un mensaje de error si existe -->
<?php if (isset($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<p>¿Estás seguro de que deseas eliminar esta cancelación?</p>

<p><strong>ID Cancelación:</strong> <?= htmlspecialchars($cancelacion['id_cancelacion']) ?></p>
<p><strong>Reserva ID:</strong> <?= htmlspecialchars($cancelacion['id_reserva']) ?></p>
<p><strong>Penalización:</strong> <?= htmlspecialchars($cancelacion['penalizacion']) ?>€</p>
<p><strong>Fecha Cancelación:</strong> <?= htmlspecialchars($cancelacion['fecha_cancelacion']) ?></p>

<form action="eliminar_cancelacion.php?id=<?= $id_cancelacion ?>" method="POST">
    <button type="submit" class="btn eliminar">Eliminar Cancelación</button>
</form>

<p><a href="cancelaciones.php" class="btn">Volver a la lista de cancelaciones</a></p>

<?php include('footer.php'); ?>