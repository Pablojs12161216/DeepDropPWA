<?php

$apiKey = "eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJjb3JyZW9wYXJhcHJ1ZWJhc3BhYmxvQGdtYWlsLmNvbSIsImp0aSI6IjlhZWVmMzM1LTBmMGYtNGZhNC05Yzc5LTMxMzlmNTZkZjRiZiIsImlzcyI6IkFFTUVUIiwiaWF0IjoxNzcwNzIyMzIyLCJ1c2VySWQiOiI5YWVlZjMzNS0wZjBmLTRmYTQtOWM3OS0zMTM5ZjU2ZGY0YmYiLCJyb2xlIjoiIn0.lecSNlKmXBznXX1IvBPXvtTGfhtgwqKatuYPxitPbsk";
$idEstacion = "6213X";

function obtenerDatosAEMET($estacion, $key)
{
    $url = "https://opendata.aemet.es/opendata/api/observacion/convencional/datos/estacion/" . $estacion;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "api_key: $key",
        "accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $respuesta = curl_exec($ch);
    $info = json_decode($respuesta, true);
    curl_close($ch);

    if (isset($info['datos'])) {
        sleep(1);
        $datosFinales = file_get_contents($info['datos']);
        return json_decode($datosFinales, true);
    } else {
        return ["error" => $info['descripcion'] ?? "No se pudo obtener el enlace de datos."];
    }
}

header('Content-Type: application/json');
echo json_encode(obtenerDatosAEMET($idEstacion, $apiKey));
