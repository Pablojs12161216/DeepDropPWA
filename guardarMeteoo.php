<?php
// guardarMeteo.php
// Función para guardar los datos de AEMET en la base de datos
// Se llama desde datosMeteoBD.php o un cron

require_once 'conexionBD.php';

function guardarDatosAemet($conexion, $idema) {
    $api_key = "eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJjb3JyZW9wYXJhcHJ1ZWJhc3BhYmxvQGdtYWlsLmNvbSIsImp0aSI6IjlhZWVmMzM1LTBmMGYtNGZhNC05Yzc5LTMxMzlmNTZkZjRiZiIsImlzcyI6IkFFTUVUIiwiaWF0IjoxNzcwNzIyMzIyLCJ1c2VySWQiOiI5YWVlZjMzNS0wZjBmLTRmYTQtOWM3OS0zMTM5ZjU2ZGY0YmYiLCJyb2xlIjoiIn0.lecSNlKmXBznXX1IvBPXvtTGfhtgwqKatuYPxitPbsk";

    echo "✅ Conexión BD OK\n";

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

    $data = json_decode($response, true);
    if (!$data || !isset($data['datos'])) return;

    $datos_url = $data['datos'];

    $ch2 = curl_init($datos_url);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0'
    ]);

    $json_obs = curl_exec($ch2);
    curl_close($ch2);

    $observaciones = json_decode($json_obs, true);
    if (!$observaciones || count($observaciones) == 0) return;

    $ultimo = end($observaciones);

    $tipos = ['ta'=>1, 'hr'=>2, 'vv'=>3, 'prec'=>4];

    foreach ($tipos as $campo => $tipo_id) {
        $valor = $ultimo[$campo] ?? null;
        if ($valor === null) continue;

        $fecha_hora = date('Y-m-d H:00:00', strtotime($ultimo['fint']));
        $check = $conexion->prepare("
            SELECT 1 FROM lecturas_meteo 
            WHERE estacion_id=? AND tipo_medida_id=? 
            AND DATE_FORMAT(fecha_hora, '%Y-%m-%d %H') = DATE_FORMAT(?, '%Y-%m-%d %H')
        ");
        $check->bind_param("iis", $idema, $tipo_id, $fecha_hora);
        $check->execute();
        $resCheck = $check->get_result();

        if ($resCheck->num_rows == 0) {
            $ins = $conexion->prepare("
                INSERT INTO lecturas_meteo (estacion_id, tipo_medida_id, valor, fecha_hora)
                VALUES (?, ?, ?, ?)
            ");
            $ins->bind_param("iids", $idema, $tipo_id, $valor, $fecha_hora);
            $ins->execute();

            file_put_contents("log_cron.txt", date("Y-m-d H:i:s") . " Insertado tipo $tipo_id valor $valor\n", FILE_APPEND);
        }
    }
}

?>


