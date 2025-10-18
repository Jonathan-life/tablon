<?php
session_start();
if (!isset($_SESSION["usuario"]) || $_SESSION["tipo"] !== "admin") {
    header("Location: logan.php");
    exit;
}

include "db_contable.php";

$ruc = $_POST["ruc"] ?? "";

// Recorrer los registros enviados
foreach ($_POST["importe"] as $id => $importe) {
    $periodo = $_POST["periodo"][$id];
    $tributo = $_POST["tributo"][$id];
    $fecha = $_POST["fecha"][$id];
    $capitalizado = $_POST["capitalizado"][$id];
    $moratorio = $_POST["moratorio"][$id];

    // Calcular saldo total y exigible
    $saldo_total = $importe + $capitalizado + $moratorio;
    $saldo_exigible = $saldo_total; // Puedes aplicar lógica más adelante

    $stmt = $conn->prepare("UPDATE deudas SET 
        periodo_semana = ?, tributo = ?, fecha_emision = ?, 
        importe_valor = ?, interes_capitalizado = ?, interes_moratorio = ?, 
        saldo_total = ?, saldo_exigible = ?
        WHERE id = ? AND ruc = ?");

    $stmt->bind_param("sssddddiss", $periodo, $tributo, $fecha, $importe, $capitalizado, $moratorio, $saldo_total, $saldo_exigible, $id, $ruc);
    $stmt->execute();
}

echo "<p>Las deudas fueron actualizadas correctamente.</p>";
echo '<a href="admin.php">Volver al panel</a>';
?>
