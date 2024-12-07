<?php
// Incluir el archivo de conexión a la base de datos
include('db.php');

// Consulta para obtener todos los teléfonos con el nombre del cliente
$query = $pdo->query('
    SELECT telefono.id_cliente, telefono.telefono, cliente.nombre 
    FROM telefono
    INNER JOIN cliente ON telefono.id_cliente = cliente.id_cliente
');
$telefonos = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
// Incluir el encabezado
include('header.php');
?>

<h2 class="titulo-seccion">Lista de Teléfonos</h2>

<!-- Tabla que muestra la lista de teléfonos -->
<table class="entity-table">
    <thead>
        <tr>
            <th>Nombre Cliente</th>
            <th>Teléfono</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($telefonos as $telefono): ?>
            <tr>
                <td><?= htmlspecialchars($telefono['nombre']) ?></td> <!-- Mostrar nombre del cliente -->
                <td><?= htmlspecialchars($telefono['telefono']) ?></td>
                <td>
                    <a href="editar_telefono.php?id=<?= $telefono['telefono'] ?>" class="btn editar">Editar</a>
                    <a href="eliminar_telefono.php?id=<?= $telefono['telefono'] ?>" class="btn eliminar" onclick="return confirm('¿Estás seguro de eliminar este teléfono?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Enlace para agregar un nuevo teléfono -->
<a href='agregar_telefono.php' class="btn agregar">Agregar Teléfono</a>

<?php
// Incluir el pie de página
include('footer.php');
?>