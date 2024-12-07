<?php
// Incluir el archivo de conexión a la base de datos
include('db.php');

// Verificar si se ha proporcionado un id en la URL
if (isset($_GET['id'])) {
    $id_viaje = $_GET['id'];

    // Validar que el id sea un número entero (para evitar inyecciones SQL)
    if (filter_var($id_viaje, FILTER_VALIDATE_INT)) {
        // Preparar la consulta para eliminar el viaje con el id proporcionado
        $sql = 'DELETE FROM viaje WHERE id_viaje = :id_viaje';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_viaje', $id_viaje);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Redirigir a la lista de viajes
            header('Location: viajes.php');
            exit;
        } else {
            // Si hubo un error en la eliminación
            $error = "Error al eliminar el viaje.";
        }
    } else {
        // Si el id no es válido
        $error = "ID de viaje no válido.";
    }
} else {
    // Si no se ha proporcionado un id
    $error = "No se proporcionó un ID de viaje.";
}

// Mostrar un mensaje de error si existe
if (isset($error)) {
    echo "<p>$error</p>";
}
