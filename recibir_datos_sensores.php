<?php
$conexion = new mysqli("localhost", "root", "", "deepdrop"); // Ajustar DB

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Recoger datos del POST
$sensor_id = $_POST['sensor_id'];
$valor = $_POST['valor'];

// Insertar en la tabla lecturas_sensores
$sql = "INSERT INTO lecturas_sensores (sensor_id, fecha_hora, valor) 
        VALUES ('$sensor_id', NOW(), '$valor')";

if ($conexion->query($sql) === TRUE) {
    echo "OK";
} else {
    echo "Error: " . $conexion->error;
}

$conexion->close();
?>