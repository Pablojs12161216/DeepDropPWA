<?php
header('Content-Type: application/json');

require_once 'conexionBD.php';

if(!isset($_GET['estacion']) || empty($_GET['estacion'])){
    echo json_encode(["error" => "No se indicó estación"]);
    exit;
}

$estacion = $conexion->real_escape_string($_GET['estacion']);

$sql = "SELECT tipo_medida_id, valor, fecha_hora 
        FROM lecturas_meteo 
        WHERE estacion_id = (
            SELECT id FROM estaciones_meteo WHERE ubicacion = '$estacion' LIMIT 1
        )
        ORDER BY fecha_hora ASC";

$result = $conexion->query($sql);

if(!$result){
    echo json_encode(["error" => "Error al consultar datos"]);
    exit;
}

$datos = [];
while($fila = $result->fetch_assoc()){
    $datos[] = $fila;
}

echo json_encode($datos);
$conexion->close();
?>
