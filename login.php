<?php
require_once "conexionBD.php";
session_start();
header('Content-Type: application/json');

$email    = trim($_POST["email"] ?? "");
$password = trim($_POST["password"] ?? "");

/* ============================= */
/* 1️⃣ VALIDAR CAMPOS VACÍOS */
/* ============================= */

if (empty($email) && empty($password)) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Debes introducir email y contraseña"
    ]);
    exit;
}

if (empty($email)) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Debes introducir tu email"
    ]);
    exit;
}

if (empty($password)) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Debes introducir tu contraseña"
    ]);
    exit;
}

/* ============================= */
/* 2️⃣ BUSCAR USUARIO */
/* ============================= */

$stmt = $conexion->prepare("
    SELECT id, nombre, password_hash, activo
    FROM usuarios
    WHERE email = ?
");

$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

/* ============================= */
/* 3️⃣ USUARIO NO EXISTE */
/* ============================= */

if ($resultado->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "mensaje" => "No existe ningún usuario con ese email"
    ]);
    exit;
}

$usuario = $resultado->fetch_assoc();

/* ============================= */
/* 4️⃣ USUARIO DESACTIVADO */
/* ============================= */

if ($usuario["activo"] != 1) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Tu cuenta está desactivada"
    ]);
    exit;
}

/* ============================= */
/* 5️⃣ CONTRASEÑA INCORRECTA */
/* ============================= */

if (!password_verify($password, $usuario["password_hash"])) {
    echo json_encode([
        "success" => false,
        "mensaje" => "La contraseña no es válida"
    ]);
    exit;
}

/* ============================= */
/* 6️⃣ LOGIN CORRECTO */
/* ============================= */

$_SESSION["usuario_id"] = $usuario["id"];
$_SESSION["usuario_nombre"] = $usuario["nombre"];

/* Actualizar último acceso */
$update = $conexion->prepare("
    UPDATE usuarios
    SET ultimo_acceso = NOW()
    WHERE id = ?
");
$update->bind_param("i", $usuario["id"]);
$update->execute();
$update->close();

/* ============================= */
/* 7️⃣ DEVOLVER JSON CON NOMBRE E ID */
/* ============================= */

echo json_encode([
    "success" => true,
    "mensaje" => "Inicio de sesión correcto",
    "user_id" => $usuario["id"],
    "nombre" => $usuario["nombre"]
]);

$stmt->close();
$conexion->close();
?>
