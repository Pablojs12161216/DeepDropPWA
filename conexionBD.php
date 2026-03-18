<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "deepdrop";

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die(json_encode(["error" => "Error de conexión a la base de datos"]));
}

$conexion->set_charset("utf8mb4");
?>

