<?php
// Incluir el archivo de conexión a la base de datos
include('db.php');

// Verificar si se ha proporcionado un id en la URL
if (isset($_GET['id'])) {
    $id_cliente = $_GET['id'];

    // Validar que el id sea un número entero (para evitar inyecciones SQL)
    if (filter_var($id_cliente, FILTER_VALIDATE_INT)) {
        // Preparar la consulta para eliminar el cliente con el id proporcionado
        $sql = 'DELETE FROM cliente WHERE id_cliente = :id_cliente';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_cliente', $id_cliente);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Redirigir a la lista de clientes
            header('Location: clientes.php');
            exit;
        } else {
            // Si hubo un error en la eliminación
            $error = "Error al eliminar el cliente.";
        }
    } else {
        // Si el id no es válido
        $error = "ID de cliente no válido.";
    }
} else {
    // Si no se ha proporcionado un id
    $error = "No se proporcionó un ID de cliente.";
}

// Mostrar un mensaje de error si existe
if (isset($error)) {
    echo "<p>$error</p>";
}
