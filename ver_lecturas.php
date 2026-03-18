<?php
$conexion = new mysqli("localhost", "root", "", "deepdrop");

$resultado = $conexion->query("
    SELECT l.*, s.nombre AS sensor_nombre, d.nombre AS dispositivo_nombre, p.nombre AS parcela_nombre
    FROM lecturas_sensores l
    JOIN sensores s ON l.sensor_id = s.id
    JOIN dispositivos d ON s.dispositivo_id = d.id
    JOIN parcelas p ON d.parcela_id = p.id
    ORDER BY l.fecha_hora DESC
    LIMIT 20
");

echo "<h2>Últimas lecturas de sensores</h2>";
while($fila = $resultado->fetch_assoc()){
    echo "Parcela: " . $fila['parcela_nombre'] . 
         " | Dispositivo: " . $fila['dispositivo_nombre'] .
         " | Sensor: " . $fila['sensor_nombre'] .
         " | Valor: " . $fila['valor'] .
         " | Fecha: " . $fila['fecha_hora'] . "<br>";
}
?>