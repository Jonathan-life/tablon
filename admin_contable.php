<?php
session_start();

if (!isset($_SESSION["usuario"]) || $_SESSION["tipo"] !== "admin") {
    header("Location: logan.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel del Administrador</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="mb-4">
      <h2 class="text-primary">Bienvenido ADMINISTRADOR <?= htmlspecialchars($_SESSION["usuario"]) ?></h2>
      <a href="logout.php" class="btn btn-danger btn-sm mt-2">Cerrar sesión</a>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Buscar Información por RUC</h5>
        <form method="POST" action="ver_ruc.php" class="row g-3">
          <div class="col-sm-8">
            <input type="text" name="ruc" class="form-control" placeholder="Ingrese RUC" required>
          </div>
          <div class="col-sm-4">
            <button type="submit" class="btn btn-primary w-100">Buscar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
