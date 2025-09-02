<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Solicitação de Acesso</title>
  <link rel="stylesheet" href="../assets/css/formulario.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="solicitar-acesso">
  <main>
    <div class="pagina-formulario">
      <form method="POST" action="../templates/salvar_solicitacao.php" class="formulario">
        <h2 class="main-title">Solicitação de Acesso</h2>

        <div class="linha">
          <div class="campo-longo">
            <label for="nome" class="label">Nome Completo</label>
            <input type="text" id="nome" name="nome" placeholder="Digite seu nome completo" required
              value="<?php echo isset($_GET['nome']) ? htmlspecialchars(urldecode($_GET['nome'])) : ''; ?>">
          </div>
        </div>

        <div class="linha">
          <div class="campo-longo">
            <label for="nome_rede" class="label">Nome de Usuário na Rede CEHAB</label>
            <input type="text" id="nome_rede" name="nome_rede" placeholder="Ex: marcos.rigel" required
              value="<?php echo isset($_GET['rede']) ? htmlspecialchars(urldecode($_GET['rede'])) : ''; ?>">
          </div>
        </div>

        <div class="linha">
          <div class="campo-longo">
            <label for="telefone" class="label">Telefone</label>
            <input type="text" id="telefone" name="telefone" placeholder="(81) 99999-9999" required 
              pattern="\(\d{2}\) \d{5}-\d{4}" title="Formato esperado: (81) 99999-9999">
          </div>
        </div>

        <input type="hidden" name="g_id" value="<?php echo isset($_GET['g_id']) ? intval($_GET['g_id']) : ''; ?>">

        <button type="submit" class="btn">Solicitar Acesso</button>
      </form>
    </div>
  </main>

  <script>
document.addEventListener("DOMContentLoaded", function () {
  const telefoneInput = document.getElementById("telefone");

  telefoneInput.addEventListener("input", function (e) {
    let valor = telefoneInput.value.replace(/\D/g, "").substring(0, 11); // Só números, máx 11 dígitos
    const ddd = valor.substring(0, 2);
    const parte1 = valor.substring(2, 7);
    const parte2 = valor.substring(7, 11);

    if (valor.length > 7) {
      telefoneInput.value = `(${ddd}) ${parte1}-${parte2}`;
    } else if (valor.length > 2) {
      telefoneInput.value = `(${ddd}) ${parte1}`;
    } else {
      telefoneInput.value = `(${ddd}`;
    }
  });
});
</script>


</body>
</html>

