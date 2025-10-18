<?php
session_start();

// Verificar si es usuario autenticado
if (!isset($_SESSION["tipo"]) || $_SESSION["tipo"] !== "usuario") {
    header("Location: logan.php");
    exit;
}

include "db_contable.php";

// Obtener RUC de la sesión
$ruc = $_SESSION["ruc"];

// Consultar las deudas del usuario por RUC
$stmt = $conn->prepare("SELECT * FROM deudas WHERE ruc = ?");
$stmt->bind_param("s", $ruc);
$stmt->execute();
$deudas = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel del Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <h2 class="mb-3">Bienvenido USUARIO: <?= htmlspecialchars($_SESSION["usuario"]) ?></h2>
    <p><strong>RUC:</strong> <?= htmlspecialchars($ruc) ?></p>
    <a href="logout.php" class="btn btn-danger mb-4">Cerrar sesión</a>

    <h4 class="mb-3">Tus Deudas Registradas</h4>
    <?php if ($deudas->num_rows > 0): ?>
      <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
         <thead class="table-light">
              <tr>
                <th>N° Valor</th>
                <th>Tipo</th>
                <th>Periodo</th>
                <th>Tributo</th>
                <th>F. Emisión</th>
                <th>F. Notificación</th>
                <th>Etapa Básica</th>
                <th>Importe de Valor</th> 
                <th>Int. Capitalizado</th>
                <th>Int. Moratorio</th>
                <th>Saldo Total</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($fila = $deudas->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($fila['numero_valor'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($fila['tipo'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($fila['periodo']) ?></td>
                  <td><?= htmlspecialchars($fila['tributo']) ?></td>
                  <td><?= htmlspecialchars($fila['fecha_emision']) ?></td>
                  <td><?= htmlspecialchars($fila['fecha_notificacion']) ?></td>
                  <td><?= htmlspecialchars($fila['etapa_basica']) ?></td>
                  <td>S/. <?= number_format($fila['importe_valor'], 2) ?></td> <!-- FALTABA -->
                  <td>S/. <?= number_format($fila['interes_capitalizado'], 2) ?></td>
                  <td>S/. <?= number_format($fila['interes_moratorio'], 2) ?></td>
                  <td>S/. <?= number_format($fila['saldo_total'], 2) ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>

        </table>
      </div>
    <?php else: ?>
      <p class="text-muted">No tienes deudas registradas.</p>
    <?php endif; ?>
  </div>
</body>
</html>
