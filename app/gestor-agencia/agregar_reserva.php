<?php
// Incluir el archivo de conexión a la base de datos
include('db.php');

// Inicializar la fecha de reserva con la fecha actual
$fecha_reserva = date('Y-m-d');  // Fecha actual en formato YYYY-MM-DD

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

        // Si no hay errores, calcular el total pagado y hacer la reserva
        if (!isset($error)) {
            // Calcular la cantidad pagada
            $cantidad_pagada = $viaje['precio'] * $num_plazas_reservadas;

            // Insertar la reserva
            $sql = "INSERT INTO reserva (id_cliente, id_viaje, fecha_reserva, cantidad_pagada, num_plazas_reservadas, cancelada) 
                    VALUES (:id_cliente, :id_viaje, :fecha_reserva, :cantidad_pagada, :num_plazas_reservadas, false)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_cliente', $id_cliente);
            $stmt->bindParam(':id_viaje', $id_viaje);
            $stmt->bindParam(':fecha_reserva', $fecha_reserva);
            $stmt->bindParam(':cantidad_pagada', $cantidad_pagada);
            $stmt->bindParam(':num_plazas_reservadas', $num_plazas_reservadas);

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
                $error = "Error al agregar la reserva.";
            }
        }
    }
}

include('header.php');

// Obtener la lista de clientes
$stmt = $pdo->prepare("SELECT id_cliente, nombre, apellidos FROM cliente");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de viajes (con origen y destino concatenados)
$stmt = $pdo->prepare("SELECT id_viaje, origen, destino FROM viaje");
$stmt->execute();
$viajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="titulo-seccion">Agregar Nueva Reserva</h2>

<!-- Mostrar un mensaje de error si existe -->
<?php if (isset($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- Formulario para agregar reserva -->
<form action="agregar_reserva.php" method="POST">
    <label for="id_cliente">Cliente:</label>
    <select name="id_cliente" id="id_cliente" required>
        <option value="">Seleccionar cliente</option>
        <?php foreach ($clientes as $cliente): ?>
            <option value="<?= $cliente['id_cliente'] ?>"><?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellidos']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="id_viaje">Viaje:</label>
    <select name="id_viaje" id="id_viaje" required>
        <option value="">Seleccionar viaje</option>
        <?php foreach ($viajes as $viaje): ?>
            <option value="<?= $viaje['id_viaje'] ?>"><?= htmlspecialchars($viaje['origen'] . ' - ' . $viaje['destino']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label for="fecha_reserva">Fecha de Reserva:</label>
    <input type="date" name="fecha_reserva" id="fecha_reserva" value="<?= $fecha_reserva ?>" readonly><br><br>

    <label for="num_plazas_reservadas">Número de Plazas Reservadas:</label>
    <input type="number" name="num_plazas_reservadas" id="num_plazas_reservadas" min="1" required><br><br>

    <button type="submit" class="btn">Agregar Reserva</button>
</form>

<p><a href="reservas.php" class="btn">Volver a la lista de reservas</a></p>

<?php include('footer.php'); ?>