<?php
// Incluir el archivo de conexión a la base de datos
include('db.php');

// Verificar si se ha proporcionado un id de reserva en la URL
if (isset($_GET['id'])) {
    $id_reserva = $_GET['id'];

    // Obtener la información de la reserva para asegurarse de que existe
    $stmt = $pdo->prepare("SELECT r.id_reserva, r.id_viaje, r.num_plazas_reservadas, 
                                  c.nombre AS cliente_nombre, c.apellidos AS cliente_apellidos, 
                                  v.destino, v.origen, v.id_viaje, v.max_plazas
                            FROM reserva r
                            JOIN cliente c ON r.id_cliente = c.id_cliente
                            JOIN viaje v ON r.id_viaje = v.id_viaje
                            WHERE r.id_reserva = :id_reserva");
    $stmt->bindParam(':id_reserva', $id_reserva);
    $stmt->execute();
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        $error = "La reserva especificada no existe.";
    } else {
        // Si la reserva existe, procedemos con la eliminación
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Primero, actualizamos las plazas disponibles del viaje
            $stmt = $pdo->prepare("UPDATE viaje 
                                   SET max_plazas = max_plazas + :num_plazas 
                                   WHERE id_viaje = :id_viaje");
            $stmt->bindParam(':num_plazas', $reserva['num_plazas_reservadas']);
            $stmt->bindParam(':id_viaje', $reserva['id_viaje']);
            $stmt->execute();

            // Ahora eliminamos la reserva de la base de datos
            $stmt = $pdo->prepare("DELETE FROM reserva WHERE id_reserva = :id_reserva");
            $stmt->bindParam(':id_reserva', $id_reserva);
            if ($stmt->execute()) {
                // Redirigir a la lista de reservas
                header('Location: reservas.php');
                exit;
            } else {
                $error = "Error al eliminar la reserva.";
            }
        }
    }
} else {
    $error = "No se proporcionó un ID de reserva.";
}

include('header.php');
?>

<h2 class="titulo-seccion">Eliminar Reserva</h2>

<!-- Mostrar un mensaje de error si existe -->
<?php if (isset($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- Confirmación de eliminación -->
<form action="eliminar_reserva.php?id=<?= $id_reserva ?>" method="POST">
    <p>¿Estás seguro de que deseas eliminar esta reserva?</p>
    <p>Cliente: <?= htmlspecialchars($reserva['cliente_nombre'] . ' ' . $reserva['cliente_apellidos']) ?></p>
    <p>Viaje: <?= htmlspecialchars($reserva['origen'] . ' - ' . $reserva['destino']) ?></p>
    <p>Número de Plazas Reservadas: <?= $reserva['num_plazas_reservadas'] ?></p>
    <button type="submit" class="btn">Eliminar Reserva</button>
</form>

<p><a href="reservas.php" class="btn">Volver a la lista de reservas</a></p>

<?php include('footer.php'); ?>