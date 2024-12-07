<?php
include('db.php');

$query = $pdo->query('SELECT * FROM cancelacion');
$cancelaciones = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
include('header.php');
?>

<h2 class='titulo-seccion'>Lista de Cancelaciones</h2>
<table class="entity-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Fecha Cancelación</th>
            <th>Penalización</th>
            <th>ID Reserva</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cancelaciones as $cancelacion): ?>
            <tr>
                <td><?= $cancelacion['id_cancelacion'] ?></td>
                <td><?= htmlspecialchars($cancelacion['fecha_cancelacion']) ?></td>
                <td><?= htmlspecialchars($cancelacion['penalizacion']) ?></td>
                <td><?= htmlspecialchars($cancelacion['id_reserva']) ?></td>
                <td>
                    <a href="editar_cancelacion.php?id=<?= $cancelacion['id_cancelacion'] ?>" class="btn editar">Editar</a>
                    <a href="eliminar_cancelacion.php?id=<?= $cancelacion['id_cancelacion'] ?>" class="btn eliminar" onclick="return confirm('¿Estás seguro de eliminar esta cancelacion?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href='agregar_cancelacion.php?action=add' class="btn agregar">Agregar Cancelación</a>


<?php
include('footer.php');
?>