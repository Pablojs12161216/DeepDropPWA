<?php
require_once "conexionBD.php";
session_start();
header('Content-Type: application/json');

$nombre     = trim($_POST["nombre"] ?? "");
$apellidos  = trim($_POST["apellidos"] ?? "");
$email      = trim($_POST["email"] ?? "");
$password   = trim($_POST["password"] ?? "");
$telefono   = trim($_POST["telefono"] ?? "");

/* ============================= */
/* 1️⃣ VALIDAR CAMPOS */
/* ============================= */

if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Todos los campos obligatorios deben completarse"
    ]);
    exit;
}

/* ============================= */
/* 2️⃣ COMPROBAR SI EL EMAIL EXISTE */
/* ============================= */

$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Este email ya está registrado"
    ]);
    exit;
}

$stmt->close();

/* ============================= */
/* 2️⃣ COMPROBAR SI EL TELÉFONO EXISTE */
/* ============================= */

$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE telefono = ?");
$stmt->bind_param("s", $telefono);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Este telefono ya está registrado"
    ]);
    exit;
}

$stmt->close();

/* ============================= */
/* 3️⃣ ENCRIPTAR CONTRASEÑA */
/* ============================= */

$password_hash = password_hash($password, PASSWORD_DEFAULT);

/* ============================= */
/* 4️⃣ INSERTAR USUARIO */
/* ============================= */

$stmt = $conexion->prepare("
    INSERT INTO usuarios
    (nombre, apellidos, email, password_hash, telefono, activo, fecha_creacion)
    VALUES (?, ?, ?, ?, ?, 1, NOW())
");

$stmt->bind_param("sssss", $nombre, $apellidos, $email, $password_hash, $telefono);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id; // obtiene el ID del usuario insertado
    echo json_encode([
        "success" => true,
        "mensaje" => "Usuario registrado correctamente",
        "user_id" => $user_id
    ]);
} else {

    echo json_encode([
        "success" => false,
        "mensaje" => "Error al registrar el usuario"
    ]);
}

$stmt->close();
$conexion->close();
?>
