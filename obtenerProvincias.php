<?php
require_once "conexionBD.php";

header('Content-Type: application/json; charset=utf-8');

$provincias = [];

$sql = "SELECT id, nombre FROM provincias ORDER BY nombre ASC";
$result = $conexion->query($sql);

while ($row = $result->fetch_assoc()) {
    $provincias[] = $row;
}

// 🔥 Importante para que Málaga no salga como MÃ¡laga
echo json_encode($provincias, JSON_UNESCAPED_UNICODE);

$conexion->close();
?>
