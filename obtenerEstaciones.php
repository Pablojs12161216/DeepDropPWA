<?php
require_once "conexionBD.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['provincia_id'])) {
    echo json_encode([]);
    exit;
}

$provincia_id = intval($_GET['provincia_id']);

$stmt = $conexion->prepare("
    SELECT id, nombre, ubicacion 
    FROM estaciones_meteo 
    WHERE provincia_id = ?
    ORDER BY nombre ASC
");

$stmt->bind_param("i", $provincia_id);
$stmt->execute();
$result = $stmt->get_result();

$estaciones = [];

while ($row = $result->fetch_assoc()) {
    $estaciones[] = $row;
}

echo json_encode($estaciones, JSON_UNESCAPED_UNICODE);

$stmt->close();
$conexion->close();
?>
