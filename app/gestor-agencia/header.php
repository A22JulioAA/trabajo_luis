<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Agencia de Viajes</title>
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <header id="cabecera">
        <nav>
            <ul>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'selected' : '' ?>"><a href="index.php">Inicio</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'clientes.php' ? 'selected' : '' ?>"><a href="clientes.php">Clientes</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'viajes.php' ? 'selected' : '' ?>"><a href="viajes.php">Viajes</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'reservas.php' ? 'selected' : '' ?>"><a href="reservas.php">Reservas</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'cancelaciones.php' ? 'selected' : '' ?>"><a href="cancelaciones.php">Cancelaciones</a></li>
                <li class="<?= basename($_SERVER['PHP_SELF']) == 'telefonos.php' ? 'selected' : '' ?>"><a href="telefonos.php">Teléfonos</a></li>
            </ul>
        </nav>
    </header>
    <main>