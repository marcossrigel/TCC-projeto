<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Monitoramento Creches</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/login.css">

</head>

<body>
  <div class="container">
    <div class="main-title">Sistema Monitoramento de Obras</div>
    <div class="login-container">
    
      <div class="main-title">Entrar</div>
      
      <form class="login-form" action="logar.php" method="post">
        <input type="text" id="usuario" name="usuario" placeholder="Usuário" required>
        <input type="password" id="senha" name="senha" placeholder="Senha" required>
        <div class="divider"></div>
        <button type="submit" class="btn">Entrar</button>
        <a href="#" class="forgot-password">Esqueceu a conta?</a>
      </form>
    </div>

    <img src="img/logo.png" alt="Logo" width="160px" style="margin-top: 40px;">
  </div>
</body>
</html>
