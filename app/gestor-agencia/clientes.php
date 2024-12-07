<?php
include('db.php');

$query = $pdo->query('SELECT * FROM cliente');
$clientes = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
include('header.php');
?>

<h2 class='titulo-seccion'>Lista de Clientes</h2>
<table class="entity-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>DNI</th>
            <th>Correo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clientes as $cliente): ?>
            <tr>
                <td><?= $cliente['id_cliente'] ?></td>
                <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                <td><?= htmlspecialchars($cliente['apellidos']) ?></td>
                <td><?= htmlspecialchars($cliente['dni']) ?></td>
                <td><?= htmlspecialchars($cliente['correo_electronico']) ?></td>
                <td>
                    <a href="editar_cliente.php?id=<?= $cliente['id_cliente'] ?>" class="btn editar">Editar</a>
                    <a href="eliminar_cliente.php?id=<?= $cliente['id_cliente'] ?>" class="btn eliminar" onclick="return confirm('¿Estás seguro de eliminar este cliente?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href='agregar_cliente.php' class="btn agregar">Agregar Cliente</a>


<?php
include('footer.php');
?>