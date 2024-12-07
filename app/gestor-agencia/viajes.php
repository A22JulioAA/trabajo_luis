<?php
include('db.php');

$query = $pdo->query('SELECT * FROM viaje');
$viajes = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
include('header.php');
?>

<h2 class='titulo-seccion'>Lista de Viajes</h2>
<table class="entity-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Origen</th>
            <th>Destino</th>
            <th>Fecha Salida</th>
            <th>Fecha Llegada</th>
            <th>Precio</th>
            <th>Máx. Plazas</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($viajes as $viaje): ?>
            <tr>
                <td><?= $viaje['id_viaje'] ?></td>
                <td><?= htmlspecialchars($viaje['origen']) ?></td>
                <td><?= htmlspecialchars($viaje['destino']) ?></td>
                <td><?= htmlspecialchars($viaje['fecha_salida']) ?></td>
                <td><?= htmlspecialchars($viaje['fecha_llegada']) ?></td>
                <td><?= htmlspecialchars($viaje['precio']) ?></td>
                <td><?= htmlspecialchars($viaje['max_plazas']) ?></td>
                <td>
                    <a href="editar_viaje.php?id=<?= $viaje['id_viaje'] ?>" class="btn editar">Editar</a>
                    <a href="eliminar_viaje.php?id=<?= $viaje['id_viaje'] ?>" class="btn eliminar" onclick="return confirm('¿Estás seguro de eliminar este viaje?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href='agregar_viaje.php' class="btn agregar">Agregar Viaje</a>


<?php
include('footer.php');
?>