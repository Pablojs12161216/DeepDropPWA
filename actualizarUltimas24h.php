<?php
header('Content-Type: text/plain');

require_once 'conexionBD.php';

// 🔹 Clave API AEMET
$api_key = "eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJjb3JyZW9wYXJhcHJ1ZWJhc3BhYmxvQGdtYWlsLmNvbSIsImp0aSI6IjlhZWVmMzM1LTBmMGYtNGZhNC05Yzc5LTMxMzlmNTZkZjRiZiIsImlzcyI6IkFFTUVUIiwiaWF0IjoxNzcwNzIyMzIyLCJ1c2VySWQiOiI5YWVlZjMzNS0wZjBmLTRmYTQtOWM3OS0zMTM5ZjU2ZGY0YmYiLCJyb2xlIjoiIn0.lecSNlKmXBznXX1IvBPXvtTGfhtgwqKatuYPxitPbsk";

// 🔹 Obtener todas las estaciones de la BD
$sqlEst = "SELECT id, ubicacion, nombre FROM estaciones_meteo";
$resultEst = $conexion->query($sqlEst);
if(!$resultEst || $resultEst->num_rows == 0){
    die("No hay estaciones en la BD");
}

while($est = $resultEst->fetch_assoc()){
    $idEstacion = $est['id'];
    $idema = $est['ubicacion'];
    $nombreEst = $est['nombre'];

    echo "---- Procesando estación $nombreEst ($idema) ----\n";

    // 🔹 Llamada a la API AEMET
    $url_api = "https://opendata.aemet.es/opendata/api/observacion/convencional/datos/estacion/$idema?api_key=$api_key";

    $ch = curl_init($url_api);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0'
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response,true);
    if(!$data || !isset($data['datos'])){
        echo "Error: no hay datos de AEMET para $nombreEst\n";
        continue;
    }

    $datos_url = $data['datos'];

    // Segunda llamada para obtener JSON real
    $ch2 = curl_init($datos_url);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0'
    ]);
    $json_obs = curl_exec($ch2);
    curl_close($ch2);

    $observaciones = json_decode($json_obs,true);
    if(!$observaciones || count($observaciones) == 0){
        echo "No hay observaciones disponibles para $nombreEst\n";
        continue;
    }

    // 🔹 Insertar todos los datos del JSON (últimas 12h que devuelve AEMET)
    foreach($observaciones as $obs){
        $fecha = new DateTime($obs['fint']);
        $hora = $fecha->format('Y-m-d H:00:00');

        // Revisar si ya existe registro para esta hora
        $stmtCheck = $conexion->prepare("SELECT COUNT(*) as c FROM lecturas_meteo WHERE estacion_id=? AND fecha_hora=?");
        $stmtCheck->bind_param("is", $idEstacion, $hora);
        $stmtCheck->execute();
        $c = $stmtCheck->get_result()->fetch_assoc()['c'];
        if($c > 0) continue; // ya existe → saltar

        // Insertar cada tipo de medida que nos interesa
        $valores = [
            1 => $obs['ta'] ?? null,   // Temperatura
            2 => $obs['hr'] ?? null,   // Humedad
            3 => $obs['vv'] ?? null,   // Viento
            4 => $obs['prec'] ?? null  // Precipitaciones
        ];

        foreach($valores as $tipo => $valor){
            if($valor === null) continue; // saltar si no hay valor

            $stmtInsert = $conexion->prepare("
                INSERT INTO lecturas_meteo (estacion_id, tipo_medida_id, valor, fecha_hora)
                VALUES (?,?,?,?)
            ");
            $stmtInsert->bind_param("iids", $idEstacion, $tipo, $valor, $hora);
            $stmtInsert->execute();
        }
        echo "✔ Insertada hora $hora para $nombreEst\n";
    }
    
    // 🔹 Mantener SOLO las últimas 24 horas por estación
        $stmtDelete = $conexion->prepare("
            DELETE FROM lecturas_meteo
            WHERE estacion_id = ?
            AND fecha_hora NOT IN (
                SELECT fecha_hora FROM (
                    SELECT DISTINCT fecha_hora
                    FROM lecturas_meteo
                    WHERE estacion_id = ?
                    ORDER BY fecha_hora DESC
                    LIMIT 24
                ) AS ultimas
            )
        ");
        $stmtDelete->bind_param("ii", $idEstacion, $idEstacion);
        $stmtDelete->execute();

echo "✔ Ajustadas a máximo 24h para $nombreEst\n";

    }

$conexion->close();
echo "✅ Script finalizado.\n";
