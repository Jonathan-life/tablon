<?php
$host = 'localhost';
$usuario = 'root';
$contrasena = '';
$base_de_datos = 'contable';
$puerto = 3306;

$conn = new mysqli($host, $usuario, $contrasena, $base_de_datos, $puerto);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
