<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["usuario"]) || $_SESSION["tipo"] !== "admin") {
    echo json_encode(["status" => "error", "message" => "Sin autorización"]);
    exit;
}

include "db_contable.php";

// Recibir datos
$id = $_POST['id'] ?? null;
$periodo_tributario = $_POST['periodo_tributario'] ?? '';
$formulario = $_POST['formulario'] ?? '';
$numero_orden = $_POST['numero_orden'] ?? '';
$tributo_multa = $_POST['tributo_multa'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$fecha_emision = $_POST['fecha_emision'] ?? '';
$fecha_notificacion = $_POST['fecha_notificacion'] ?? '';
$fecha_pagos = $_POST['fecha_pagos'] ?? '';
$fecha_calculos = $_POST['fecha_calculos'] ?? '';
$etapa_basica = $_POST['etapa_basica'] ?? '';
$importe_tributaria = floatval($_POST['importe_tributaria'] ?? 0);
$interes_capitalizado = floatval($_POST['interes_capitalizado'] ?? 0);
$interes_moratorio = floatval($_POST['interes_moratorio'] ?? 0);
$pagos = floatval($_POST['pagos'] ?? 0);
$interes_diario = floatval($_POST['interes_diario'] ?? 0);
$interes_acumulado = floatval($_POST['interes_acumulado'] ?? 0);
$saldo_total = floatval($_POST['saldo_total'] ?? 0);

try {
    if ($id) {
        // Actualizar registro existente
        $stmt = $conn->prepare("UPDATE deudas SET 
            periodo_tributario=?, formulario=?, numero_orden=?, tributo_multa=?, tipo=?,
            fecha_emision=?, fecha_notificacion=?, fecha_pagos=?, fecha_calculos=?,
            etapa_basica=?, importe_tributaria=?, interes_capitalizado=?, interes_moratorio=?,
            pagos=?, interes_diario=?, interes_acumulado=?, saldo_total=?
            WHERE id=?");

        $stmt->bind_param(
            "ssssssssssdddddddi",
            $periodo_tributario, $formulario, $numero_orden, $tributo_multa, $tipo,
            $fecha_emision, $fecha_notificacion, $fecha_pagos, $fecha_calculos,
            $etapa_basica, $importe_tributaria, $interes_capitalizado, $interes_moratorio,
            $pagos, $interes_diario, $interes_acumulado, $saldo_total, $id
        );

        $stmt->execute();
        echo json_encode(["status" => "ok", "message" => "Actualización exitosa"]);
    } else {
        // Insertar nueva fila
        $stmt = $conn->prepare("INSERT INTO deudas 
            (periodo_tributario, formulario, numero_orden, tributo_multa, tipo,
            fecha_emision, fecha_notificacion, fecha_pagos, fecha_calculos,
            etapa_basica, importe_tributaria, interes_capitalizado, interes_moratorio,
            pagos, interes_diario, interes_acumulado, saldo_total)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "ssssssssssddddddd",
            $periodo_tributario, $formulario, $numero_orden, $tributo_multa, $tipo,
            $fecha_emision, $fecha_notificacion, $fecha_pagos, $fecha_calculos,
            $etapa_basica, $importe_tributaria, $interes_capitalizado, $interes_moratorio,
            $pagos, $interes_diario, $interes_acumulado, $saldo_total
        );

        $stmt->execute();
        $nuevo_id = $stmt->insert_id;
        echo json_encode(["status" => "ok", "message" => "Registro creado", "nuevo_id" => $nuevo_id]);
    }
} catch (mysqli_sql_exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
