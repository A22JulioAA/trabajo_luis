<?php
// Incluir el archivo de conexión a la base de datos
include('db.php');

// Verificar si se pasó el ID del teléfono
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de teléfono no especificado.");
}

$id_telefono = $_GET['id'];

// Eliminar el teléfono
$stmt = $pdo->prepare("DELETE FROM telefono WHERE telefono = :id_telefono");
$stmt->bindParam(':id_telefono', $id_telefono);

if ($stmt->execute()) {
    // Redirigir a la lista de teléfonos
    header('Location: telefonos.php');
    exit;
} else {
    die("Hubo un error al eliminar el teléfono.");
}
