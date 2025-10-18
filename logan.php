<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 50px;
    }
    form {
      max-width: 400px;
      margin: auto;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      margin-bottom: 15px;
      box-sizing: border-box;
    }
    button {
      width: 100%;
      padding: 10px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
    }
    .error {
      color: red;
      text-align: center;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

  <h2 style="text-align:center;">Iniciar sesión</h2>

  <?php if (isset($_SESSION["error"])): ?>
    <p class="error"><?php echo $_SESSION["error"]; unset($_SESSION["error"]); ?></p>
  <?php endif; ?>

  <form action="verificar_contable.php" method="POST" onsubmit="return validarFormulario()">
    <label>Usuario </label>
    <input type="text" name="usuario" id="usuario" required>

    <label>Contraseña:</label>
    <input type="password" name="clave" required>

    <button type="submit">Ingresar</button>
  </form>

  <script>
    function validarFormulario() {
      const input = document.getElementById("usuario").value.trim();
      if (/^\d{11}$/.test(input)) {
        // Es un RUC válido (solo números y 11 dígitos)
        return true;
      }
      // Si no es RUC, asumimos que es un admin con nombre de usuario
      return true;
    }
  </script>

</body>
</html>
