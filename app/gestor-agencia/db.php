<?php
$host = "postgres-container";
$dbname = "agencia_viajes";
$dbuser = "postgres";
$password = "abc123.";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $dbuser, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error: ". $e->getMessage();
}
?>