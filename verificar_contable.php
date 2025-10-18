<?php
session_start();
include "db_contable.php";

$usuario = $_POST["usuario"] ?? "";
$clave   = $_POST["clave"] ?? "";

// 🔐 Verificar administrador: puede usar usuario o RUC
$stmt = $conn->prepare("SELECT * FROM administradores WHERE usuario = ?");
$stmt->bind_param("s", $usuario);

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    if ($admin["clave"] === $clave) {
        $_SESSION["usuario"] = $admin["usuario"];
        $_SESSION["tipo"] = "admin";
        $_SESSION["ruc"] = $admin["ruc"];
        header("Location: admin_contable.php");
        exit;
    }
}

// 🔐 Verificar usuario: SOLO por RUC
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE ruc = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if ($user["clave"] === $clave) {
        $_SESSION["usuario"] = $user["usuario"];
        $_SESSION["tipo"] = "usuario";
        $_SESSION["ruc"] = $user["ruc"];
        header("Location: usuario_contable.php");
        exit;
    }
}

$_SESSION["error"] = "Usuario o clave incorrectos";
header("Location: logan.php");
exit;
