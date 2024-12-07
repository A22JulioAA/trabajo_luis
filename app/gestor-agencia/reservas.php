<?php
include('db.php');

$query = $pdo->query('SELECT * FROM reserva');
$reservas = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
include('header.php');
?>

<h2 class='titulo-seccion'>Lista de reservas</h2>
<table class="entity-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>ID Cliente</th>
            <th>ID Viaje</th>
            <th>Fecha Reserva</th>
            <th>Cantidad Pagada</th>
            <th>Num. Plazas Reservadas</th>
            <th>Cancelada</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reservas as $reserva): ?>
            <tr>
                <td><?= $reserva['id_reserva'] ?></td>
                <td><?= htmlspecialchars($reserva['id_cliente']) ?></td>
                <td><?= htmlspecialchars($reserva['id_viaje']) ?></td>
                <td><?= htmlspecialchars($reserva['fecha_reserva']) ?></td>
                <td><?= htmlspecialchars($reserva['cantidad_pagada']) ?></td>
                <td><?= htmlspecialchars($reserva['num_plazas_reservadas']) ?></td>
                <td><?= htmlspecialchars(($reserva['cancelada']) ? 'SI' : '') ?></td>
                <td>
                    <a href="editar_reserva.php?id=<?= $reserva['id_reserva'] ?>" class="btn editar">Editar</a>
                    <a href="eliminar_reserva.php?id=<?= $reserva['id_reserva'] ?>" class="btn eliminar" onclick="return confirm('¿Estás seguro de eliminar esta reserva?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href='agregar_reserva.php' class="btn agregar">Agregar Reserva</a>


<?php
include('footer.php');
?>