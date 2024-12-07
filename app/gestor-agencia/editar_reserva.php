<?php
// Incluir el archivo de conexión a la base de datos
include('db.php');

// Verificar si se ha proporcionado un id de reserva en la URL
if (isset($_GET['id'])) {
    $id_reserva = $_GET['id'];

    // Obtener la información actual de la reserva
    $stmt = $pdo->prepare("SELECT r.id_reserva, r.id_cliente, r.id_viaje, r.fecha_reserva, r.cantidad_pagada, r.num_plazas_reservadas,
                                   c.nombre AS cliente_nombre, c.apellidos AS cliente_apellidos,
                                   v.origen, v.destino, v.precio
                            FROM reserva r
                            JOIN cliente c ON r.id_cliente = c.id_cliente
                            JOIN viaje v ON r.id_viaje = v.id_viaje
                            WHERE r.id_reserva = :id_reserva");
    $stmt->bindParam(':id_reserva', $id_reserva);
    $stmt->execute();
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        $error = "La reserva especificada no existe.";
    }
} else {
    $error = "No se proporcionó un ID de reserva.";
}

// Obtener la lista de clientes
$stmt = $pdo->prepare("SELECT id_cliente, nombre, apellidos FROM cliente");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de viajes (con origen y destino concatenados)
$stmt = $pdo->prepare("SELECT id_viaje, origen, destino FROM viaje");
$stmt->execute();
$viajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cliente = $_POST['id_cliente'];
    $id_viaje = $_POST['id_viaje'];
    $num_plazas_reservadas = $_POST['num_plazas_reservadas'];

    // Validación de campos
    if (empty($id_cliente) || empty($id_viaje) || empty($num_plazas_reservadas)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Validar que el cliente existe
        $stmt = $pdo->prepare("SELECT id_cliente FROM cliente WHERE id_cliente = :id_cliente");
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $error = "El cliente especificado no existe.";
        }

        // Validar que el viaje existe y obtener el precio
        $stmt = $pdo->prepare("SELECT id_viaje, origen, destino, precio, max_plazas FROM viaje WHERE id_viaje = :id_viaje");
        $stmt->bindParam(':id_viaje', $id_viaje);
        $stmt->execute();
        $viaje = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$viaje) {
            $error = "El viaje especificado no existe.";
        } elseif ($viaje['max_plazas'] < $num_plazas_reservadas) {
            $error = "No hay suficientes plazas disponibles para este viaje.";
        }

        // Si no hay errores, actualizar la reserva
        if (!isset($error)) {
            // Calcular la cantidad pagada
            $cantidad_pagada = $viaje['precio'] * $num_plazas_reservadas;

            // Actualizar la reserva
            $sql = "UPDATE reserva 
                    SET id_cliente = :id_cliente, id_viaje = :id_viaje, cantidad_pagada = :cantidad_pagada, 
                        num_plazas_reservadas = :num_plazas_reservadas
                    WHERE id_reserva = :id_reserva";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_cliente', $id_cliente);
            $stmt->bindParam(':id_viaje', $id_viaje);
            $stmt->bindParam(':cantidad_pagada', $cantidad_pagada);
            $stmt->bindParam(':num_plazas_reservadas', $num_plazas_reservadas);
            $stmt->bindParam(':id_reserva', $id_reserva);

            if ($stmt->execute()) {
                // Actualizar las plazas disponibles del viaje
                $stmt = $pdo->prepare("UPDATE viaje SET max_plazas = max_plazas - :num_plazas WHERE id_viaje = :id_viaje");
                $stmt->bindParam(':num_plazas', $num_plazas_reservadas);
                $stmt->bindParam(':id_viaje', $id_viaje);
                $stmt->execute();

                // Redirigir a la lista de reservas
                header('Location: reservas.php');
                exit;
            } else {
                $error = "Error al actualizar la reserva.";
            }
        }
    }
}

include('header.php');
?>

<h2 class="titulo-seccion">Editar Reserva</h2>

<!-- Mostrar un mensaje de error si existe -->
<?php if (isset($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- Formulario para editar reserva -->
<form action="editar_reserva.php?id=<?= $id_reserva ?>" method="POST">
    <label for="id_cliente">Cliente:</label>
    <select name="id_cliente" id="id_cliente" required>
        <option value="">Seleccionar cliente</option>
        <?php foreach ($clientes as $cliente): ?>
            <option value="<?= $cliente['id_cliente'] ?>" <?= $cliente['id_cliente'] == $reserva['id_cliente'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellidos']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="id_viaje">Viaje:</label>
    <select name="id_viaje" id="id_viaje" required>
        <option value="">Seleccionar viaje</option>
        <?php foreach ($viajes as $viaje): ?>
            <option value="<?= $viaje['id_viaje'] ?>" <?= $viaje['id_viaje'] == $reserva['id_viaje'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($viaje['origen'] . ' - ' . $viaje['destino']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="fecha_reserva">Fecha de Reserva:</label>
    <input type="date" name="fecha_reserva" id="fecha_reserva" value="<?= $reserva['fecha_reserva'] ?>" readonly><br><br>

    <label for="num_plazas_reservadas">Número de Plazas Reservadas:</label>
    <input type="number" name="num_plazas_reservadas" id="num_plazas_reservadas" min="1" value="<?= $reserva['num_plazas_reservadas'] ?>" required><br><br>

    <button type="submit" class="btn">Actualizar Reserva</button>
</form>

<p><a href="reservas.php" class="btn">Volver a la lista de reservas</a></p>

<?php include('footer.php'); ?>