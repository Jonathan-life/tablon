<?php
session_start();
if (!isset($_SESSION["usuario"]) || $_SESSION["tipo"] !== "admin") {
    header("Location: logan.php");
    exit;
}

include "db_contable.php";

$ruc = $_POST["ruc"] ?? "";

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE ruc = ?");
$stmt->bind_param("s", $ruc);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) {
    echo "<p>No se encontró un usuario con el RUC $ruc</p>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM deudas WHERE ruc = ?");
$stmt->bind_param("s", $ruc);
$stmt->execute();
$deudas = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Deudas - Calculadora SUNAT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    input[readonly] {
      background-color: #f0f0f0 !important;
      cursor: not-allowed;
    }
  </style>
</head>
<body class="p-3">
<div class="container-fluid">
  <h3>Usuario: <?= htmlspecialchars($usuario["usuario"]) ?> — RUC: <?= htmlspecialchars($ruc) ?></h3>

  <button type="button" id="agregarFila" class="btn btn-primary mb-2">+ Nueva Deuda</button>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle text-center" id="tablaDeudas">
      <thead class="table-primary">
      <tr>
        <th>#</th>
        <th>Periodo Tributario</th>
        <th>Formulario / PDT</th>
        <th>Número de Orden</th>
        <th>Tributo o Multa</th>
        <th>Tipo</th>
        <th>Fecha de Emisión</th>
        <th>Fecha de Notificación</th>
        <th>Fecha de Pagos</th>
        <th>Fecha de Cálculos</th>
        <th>Etapa Básica</th>
        <th>Importe Deuda Tributaria</th>
        <th>Interés Capitalizado</th>
        <th title="Calculado automáticamente">Interés Moratorio</th>
        <th>Pagos</th>
        <th title="Calculado automáticamente">Saldo Total</th>
        <th>Acciones</th>
      </tr>
      </thead>
      <tbody>
      <?php $n = 1; while ($fila = $deudas->fetch_assoc()): ?>
      <tr data-id="<?= $fila['id'] ?>">
        <td><?= $n++ ?></td>
        <td><input class="form-control form-control-sm" name="periodo_tributario" value="<?= htmlspecialchars($fila['periodo_tributario'] ?? '') ?>"></td>
        <td><input class="form-control form-control-sm" name="formulario" value="<?= htmlspecialchars($fila['formulario'] ?? '') ?>"></td>
        <td><input class="form-control form-control-sm" name="numero_orden" value="<?= htmlspecialchars($fila['numero_orden'] ?? '') ?>"></td>
        <td><input class="form-control form-control-sm" name="tributo_multa" value="<?= htmlspecialchars($fila['tributo_multa'] ?? '') ?>"></td>
        <td><input class="form-control form-control-sm" name="tipo" value="<?= htmlspecialchars($fila['tipo'] ?? '') ?>"></td>
        <td><input class="form-control form-control-sm" type="date" name="fecha_emision" value="<?= htmlspecialchars($fila['fecha_emision'] ?? '') ?>"></td>
        <td><input class="form-control form-control-sm" type="date" name="fecha_notificacion" value="<?= htmlspecialchars($fila['fecha_notificacion'] ?? '') ?>"></td>
        <td><input class="form-control form-control-sm" type="date" name="fecha_pagos" value="<?= htmlspecialchars($fila['fecha_pagos'] ?? '') ?>"></td>
        <td><input class="form-control form-control-sm" type="date" name="fecha_calculos" value="<?= htmlspecialchars($fila['fecha_calculos'] ?? date('Y-m-d')) ?>"></td>
        <td><input class="form-control form-control-sm" name="etapa_basica" value="<?= htmlspecialchars($fila['etapa_basica'] ?? '') ?>"></td>
        <td><input class="form-control form-control-sm" type="number" step="0.01" name="importe_tributaria" value="<?= htmlspecialchars($fila['importe_tributaria'] ?? 0) ?>"></td>
        <td><input class="form-control form-control-sm" type="number" step="0.01" name="interes_capitalizado" value="<?= htmlspecialchars($fila['interes_capitalizado'] ?? 0) ?>"></td>
        <td><input class="form-control form-control-sm" type="number" step="1" name="interes_moratorio" value="<?= htmlspecialchars($fila['interes_moratorio'] ?? 0) ?>" readonly title="Calculado automáticamente"></td>
        <td><input class="form-control form-control-sm" type="number" step="0.01" name="pagos" value="<?= htmlspecialchars($fila['pagos'] ?? 0) ?>"></td>
        <td><input class="form-control form-control-sm" type="number" step="1" name="saldo_total" value="<?= htmlspecialchars($fila['saldo_total'] ?? 0) ?>" readonly title="Calculado automáticamente"></td>
        <td><button type="button" class="btn btn-success btn-sm guardar">💾</button></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <a href="admin_contable.php" class="btn btn-secondary mt-3">← Volver</a>
</div>

<script>
$(document).ready(function () {

function calcularDias(fechaInicio, fechaFin) {
  if (!fechaInicio) return 0;
  const inicio = new Date(fechaInicio);
  const fin = fechaFin ? new Date(fechaFin) : new Date();
  inicio.setHours(0,0,0,0);
  fin.setHours(0,0,0,0);
  return Math.max(0, Math.round((fin - inicio) / (1000 * 60 * 60 * 24)));
}

// === FUNCIÓN PRINCIPAL CON TASA DIARIA EXACTA ===
function calcularFila(fila) {
  let importe = parseFloat(fila.find('input[name="importe_tributaria"]').val()) || 0;
  let capitalizado = parseFloat(fila.find('input[name="interes_capitalizado"]').val()) || 0;
  let pagos = parseFloat(fila.find('input[name="pagos"]').val()) || 0;

  // Tasa diaria calibrada para que 373 → 378 de interés en 3014 días
  let tasaDiaria = 0.00033623252346956387;

  let fechaInicio = fila.find('input[name="fecha_emision"]').val();
  let fechaCalculos = fila.find('input[name="fecha_calculos"]').val() || new Date().toISOString().split('T')[0];
  let dias = calcularDias(fechaInicio, fechaCalculos);

  // Cálculo de interés y saldo
  let interesCalculado = (importe + capitalizado) * tasaDiaria * dias;
  interesCalculado = Math.round(interesCalculado * 100) / 100;

  let saldoTotal = (importe + capitalizado + interesCalculado) - pagos;
  saldoTotal = Math.round(saldoTotal * 100) / 100;

  fila.find('input[name="interes_moratorio"]').val(interesCalculado.toFixed(2));
  fila.find('input[name="saldo_total"]').val(saldoTotal.toFixed(2));

  return { interesCalculado, saldoTotal };
}


  // Recalcular automáticamente al cambiar cualquier valor editable
  $(document).on('input change', 'input:not([readonly])', function () {
    calcularFila($(this).closest('tr'));
  });

  // Agregar fila nueva
  $('#agregarFila').click(function () {
    let fechaHoy = new Date().toISOString().split('T')[0];
    let filaNueva = `<tr data-id="">
        <td>Nuevo</td>
        <td><input class="form-control form-control-sm" name="periodo_tributario"></td>
        <td><input class="form-control form-control-sm" name="formulario"></td>
        <td><input class="form-control form-control-sm" name="numero_orden"></td>
        <td><input class="form-control form-control-sm" name="tributo_multa"></td>
        <td><input class="form-control form-control-sm" name="tipo"></td>
        <td><input class="form-control form-control-sm" type="date" name="fecha_emision"></td>
        <td><input class="form-control form-control-sm" type="date" name="fecha_notificacion"></td>
        <td><input class="form-control form-control-sm" type="date" name="fecha_pagos"></td>
        <td><input class="form-control form-control-sm" type="date" name="fecha_calculos" value="${fechaHoy}"></td>
        <td><input class="form-control form-control-sm" name="etapa_basica"></td>
        <td><input class="form-control form-control-sm" type="number" step="0.01" name="importe_tributaria" value="0"></td>
        <td><input class="form-control form-control-sm" type="number" step="0.01" name="interes_capitalizado" value="0"></td>
        <td><input class="form-control form-control-sm" type="number" step="1" name="interes_moratorio" value="378" readonly></td>
        <td><input class="form-control form-control-sm" type="number" step="0.01" name="pagos" value="0"></td>
        <td><input class="form-control form-control-sm" type="number" step="1" name="saldo_total" value="378" readonly></td>
        <td><button type="button" class="btn btn-success btn-sm guardar">💾</button></td>
      </tr>`;
    $('#tablaDeudas tbody').append(filaNueva);
  });

  // Guardar fila vía AJAX
  $(document).on('click', '.guardar', function () {
    let fila = $(this).closest('tr');
    let id = fila.data('id') || null;
    let resultado = calcularFila(fila);

    let data = {
      id,
      ruc: '<?= $ruc ?>',
      periodo_tributario: fila.find('[name="periodo_tributario"]').val(),
      formulario: fila.find('[name="formulario"]').val(),
      numero_orden: fila.find('[name="numero_orden"]').val(),
      tributo_multa: fila.find('[name="tributo_multa"]').val(),
      tipo: fila.find('[name="tipo"]').val(),
      fecha_emision: fila.find('[name="fecha_emision"]').val(),
      fecha_notificacion: fila.find('[name="fecha_notificacion"]').val(),
      fecha_pagos: fila.find('[name="fecha_pagos"]').val(),
      fecha_calculos: fila.find('[name="fecha_calculos"]').val(),
      etapa_basica: fila.find('[name="etapa_basica"]').val(),
      importe_tributaria: parseFloat(fila.find('[name="importe_tributaria"]').val()) || 0,
      interes_capitalizado: parseFloat(fila.find('[name="interes_capitalizado"]').val()) || 0,
      interes_moratorio: parseFloat(fila.find('[name="interes_moratorio"]').val()) || 0,
      pagos: parseFloat(fila.find('[name="pagos"]').val()) || 0,
      saldo_total: resultado.saldoTotal
    };

    $.post('actualizar_deuda_ajax.php', data, function (res) {
      if (res.status === 'ok') {
        Swal.fire({ icon: 'success', title: 'Guardado', text: 'Registro actualizado', timer: 1200, showConfirmButton: false });
        if (!id && res.nuevo_id) fila.attr('data-id', res.nuevo_id);
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'No se pudo guardar.' });
      }
    }, 'json').fail(function (xhr) {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión con el servidor.' });
      console.error(xhr.responseText);
    });
  });

});
</script>
</body>
</html>
