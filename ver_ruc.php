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
</head>
<body class="p-3">
<div class="container-fluid">
    <h3>Usuario: <?= htmlspecialchars($usuario["usuario"]) ?> — RUC: <?= htmlspecialchars($ruc) ?></h3>

    <!-- Botón para agregar fila -->
    <button type="button" id="agregarFila" class="btn btn-primary mb-2">+ Nueva Deuda</button>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle text-center">
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
          <th>Intereses Moratorios</th>
          <th>Pagos / Acciones</th>
          <th>Interés Diario (%)</th>
          <th>Interés Acumulado</th>
          <th>Saldo Total</th>
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
    <td><input class="form-control form-control-sm" type="number" step="0.01" name="interes_moratorio" value="<?= htmlspecialchars($fila['interes_moratorio'] ?? 0) ?>"></td>
    <td><input class="form-control form-control-sm" type="number" step="0.01" name="pagos" value="<?= htmlspecialchars($fila['pagos'] ?? 0) ?>"></td>
    <td><input class="form-control form-control-sm" type="number" step="0.01" name="interes_diario" value="<?= htmlspecialchars($fila['interes_diario'] ?? 0) ?>"></td>
    <td class="interes-acumulado">0.00</td>
    <td class="saldo-total">0.00</td>
    <td>
        <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-success btn-sm guardar">Guardar</button>
        </div>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
      </table>
    </div>

    <a href="admin_contable.php" class="btn btn-secondary mt-3">← Volver</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {

    function calcularFila(fila) {
        let importe = parseFloat(fila.find('input[name="importe_tributaria"]').val()) || 0;
        let capitalizado = parseFloat(fila.find('input[name="interes_capitalizado"]').val()) || 0;
        let moratorio = parseFloat(fila.find('input[name="interes_moratorio"]').val()) || 0;
        let pagos = parseFloat(fila.find('input[name="pagos"]').val()) || 0;
        let interesDiario = parseFloat(fila.find('input[name="interes_diario"]').val()) || 0;

        let fechaInicio = fila.find('input[name="fecha_emision"]').val();
        let fechaCalculos = fila.find('input[name="fecha_calculos"]').val();
        let inicio = fechaInicio ? new Date(fechaInicio) : new Date();
        let fin = fechaCalculos ? new Date(fechaCalculos) : new Date();

        let dias = Math.floor((fin - inicio) / (1000*60*60*24));
        if (dias < 0) dias = 0;

        let saldoPendiente = importe - pagos;
        let saldoParaInteres = Math.max(0, saldoPendiente);

        let interesCalculado = saldoParaInteres * (interesDiario / 100) * dias;

        let interesAcumulado = capitalizado + moratorio + interesCalculado;
        let saldoTotal = saldoPendiente + interesAcumulado;

        fila.find('.interes-acumulado').text(interesAcumulado.toFixed(2));
        fila.find('.saldo-total').text(saldoTotal.toFixed(2));

        return { interesAcumulado, saldoTotal };
    }

    // recalcular al cambiar valores
    $(document).on('input', 'input[name="importe_tributaria"], input[name="interes_capitalizado"], input[name="interes_moratorio"], input[name="pagos"], input[name="interes_diario"], input[name="fecha_emision"], input[name="fecha_calculos"]', function(){
        calcularFila($(this).closest('tr'));
    });

    // inicializar
    $('tr[data-id]').each(function(){ calcularFila($(this)); });

    // AGREGAR NUEVA FILA copiando valores de la última fila
    $('#agregarFila').click(function(){
        let ultimaFila = $('table tbody tr').last();
        let nuevaFila = `
<tr data-id="">
    <td>#</td>
    <td><input class="form-control form-control-sm" name="periodo_tributario" value="${ultimaFila.find('input[name="periodo_tributario"]').val()}"></td>
    <td><input class="form-control form-control-sm" name="formulario" value="${ultimaFila.find('input[name="formulario"]').val()}"></td>
    <td><input class="form-control form-control-sm" name="numero_orden" value="${ultimaFila.find('input[name="numero_orden"]').val()}"></td>
    <td><input class="form-control form-control-sm" name="tributo_multa" value="${ultimaFila.find('input[name="tributo_multa"]').val()}"></td>
    <td><input class="form-control form-control-sm" name="tipo" value="${ultimaFila.find('input[name="tipo"]').val()}"></td>
    <td><input class="form-control form-control-sm" type="date" name="fecha_emision" value="${ultimaFila.find('input[name="fecha_emision"]').val()}"></td>
    <td><input class="form-control form-control-sm" type="date" name="fecha_notificacion" value="${ultimaFila.find('input[name="fecha_notificacion"]').val()}"></td>
    <td><input class="form-control form-control-sm" type="date" name="fecha_pagos" value="${ultimaFila.find('input[name="fecha_pagos"]').val()}"></td>
    <td><input class="form-control form-control-sm" type="date" name="fecha_calculos" value="${new Date().toISOString().split('T')[0]}"></td>
    <td><input class="form-control form-control-sm" name="etapa_basica" value="${ultimaFila.find('input[name="etapa_basica"]').val()}"></td>
    <td><input class="form-control form-control-sm" type="number" step="0.01" name="importe_tributaria" value="${ultimaFila.find('input[name="importe_tributaria"]').val()}"></td>
    <td><input class="form-control form-control-sm" type="number" step="0.01" name="interes_capitalizado" value="${ultimaFila.find('input[name="interes_capitalizado"]').val()}"></td>
    <td><input class="form-control form-control-sm" type="number" step="0.01" name="interes_moratorio" value="${ultimaFila.find('input[name="interes_moratorio"]').val()}"></td>
    <td><input class="form-control form-control-sm" type="number" step="0.01" name="pagos" value="${ultimaFila.find('input[name="pagos"]').val()}"></td>
    <td><input class="form-control form-control-sm" type="number" step="0.01" name="interes_diario" value="${ultimaFila.find('input[name="interes_diario"]').val()}"></td>
    <td class="interes-acumulado">0.00</td>
    <td class="saldo-total">0.00</td>
    <td>
        <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-success btn-sm guardar">Guardar</button>
        </div>
    </td>
</tr>
`;
        $('table tbody').append(nuevaFila);
        calcularFila($('table tbody tr').last());
    });

    // GUARDAR fila (existente o nueva) vía AJAX
    $(document).on('click', '.guardar', function (e) {
        e.preventDefault();
        let fila = $(this).closest('tr');
        let id = fila.data('id') || null;

        let resultado = calcularFila(fila);

        let data = {
            id,
            ruc: '<?= $ruc ?>',
            periodo_tributario: fila.find('input[name="periodo_tributario"]').val().trim(),
            formulario: fila.find('input[name="formulario"]').val().trim(),
            numero_orden: fila.find('input[name="numero_orden"]').val().trim(),
            tributo_multa: fila.find('input[name="tributo_multa"]').val().trim(),
            tipo: fila.find('input[name="tipo"]').val().trim(),
            fecha_emision: fila.find('input[name="fecha_emision"]').val(),
            fecha_notificacion: fila.find('input[name="fecha_notificacion"]').val(),
            fecha_pagos: fila.find('input[name="fecha_pagos"]').val(),
            fecha_calculos: fila.find('input[name="fecha_calculos"]').val(),
            etapa_basica: fila.find('input[name="etapa_basica"]').val(),
            importe_tributaria: parseFloat(fila.find('input[name="importe_tributaria"]').val()) || 0,
            interes_capitalizado: parseFloat(fila.find('input[name="interes_capitalizado"]').val()) || 0,
            interes_moratorio: parseFloat(fila.find('input[name="interes_moratorio"]').val()) || 0,
            pagos: parseFloat(fila.find('input[name="pagos"]').val()) || 0,
            interes_diario: parseFloat(fila.find('input[name="interes_diario"]').val()) || 0,
            interes_acumulado: resultado.interesAcumulado,
            saldo_total: resultado.saldoTotal
        };

        $.post('actualizar_deuda_ajax.php', data, function(respuesta){
            if(respuesta.status === 'ok'){
                Swal.fire({ icon: 'success', title: 'Guardado', text: 'Datos actualizados', timer: 1500, showConfirmButton: false });
                if(!id && respuesta.nuevo_id){
                    fila.attr('data-id', respuesta.nuevo_id);
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: respuesta.message || 'Error al guardar' });
            }
        }, 'json').fail(function(xhr, status, error){
            console.error("Error AJAX:", status, error, xhr.responseText);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al comunicarse con el servidor.' });
        });
    });

});
</script>

</body>
</html>
