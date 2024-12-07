<?php
include('db.php');

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_reserva = $_POST['id_reserva'];
    $penalizacion = 60;  // Penalización fija de 60€

    // Validación de campos
    if (empty($id_reserva)) {
        $error = "La reserva es obligatoria.";
    } else {
        // Verificar si la reserva existe
        $stmt = $pdo->prepare("SELECT * FROM reserva WHERE id_reserva = :id_reserva");
        $stmt->bindParam(':id_reserva', $id_reserva);
        $stmt->execute();
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reserva) {
            $error = "La reserva especificada no existe.";
        }

        // Si no hay errores, realizar la cancelación
        if (!isset($error)) {
            $fecha_cancelacion = date('Y-m-d');  // Fecha de cancelación actual

            // Insertar la cancelación en la base de datos
            $sql = "INSERT INTO cancelacion (id_reserva, fecha_cancelacion, penalizacion) 
                    VALUES (:id_reserva, :fecha_cancelacion, :penalizacion)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_reserva', $id_reserva);
            $stmt->bindParam(':fecha_cancelacion', $fecha_cancelacion);
            $stmt->bindParam(':penalizacion', $penalizacion);

            if ($stmt->execute()) {
                // Redirigir a la lista de cancelaciones
                header('Location: cancelaciones.php');
                exit;
            } else {
                $error = "Error al agregar la cancelación.";
            }
        }
    }
}

// Obtener la lista de reservas
$stmt = $pdo->prepare("SELECT id_reserva, id_cliente, id_viaje FROM reserva WHERE cancelada = FALSE");
$stmt->execute();
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<h2 class='titulo-seccion'>Agregar Nueva Cancelación</h2>

<!-- Mostrar un mensaje de error si existe -->
<?php if (isset($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- Formulario para agregar cancelación -->
<form action="agregar_cancelacion.php" method="POST">
    <label for="id_reserva">Reserva a Cancelar:</label>
    <select name="id_reserva" id="id_reserva" required>
        <option value="">Seleccionar reserva</option>
        <?php foreach ($reservas as $reserva): ?>
            <option value="<?= $reserva['id_reserva'] ?>">Reserva ID: <?= $reserva['id_reserva'] ?> (Cliente: <?= $reserva['id_cliente'] ?>, Viaje ID: <?= $reserva['id_viaje'] ?>)</option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="penalizacion">Penalización:</label>
    <input type="number" name="penalizacion" id="penalizacion" value="60" readonly><br><br>

    <button type="submit" class="btn">Agregar Cancelación</button>
</form>

<p><a href="cancelaciones.php" class="btn">Volver a la lista de cancelaciones</a></p>

<?php include('footer.php'); ?>