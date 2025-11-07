<?php

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<div class="cards-container">

  <a href="index.php?page=visualizar&diretoria=Educacao" class="card-link">
    <div class="card-conteudo">Educação</div>
  </a>

  <a href="index.php?page=visualizar&diretoria=Saude" class="card-link">
    <div class="card-conteudo">Saúde</div>
  </a>

  <a href="index.php?page=visualizar&diretoria=Infra Estrategicas" class="card-link">
    <div class="card-conteudo">
      <div class="card-titulo">
        <div>Infra</div>
        <div>Estratégicas</div>
      </div>
    </div>
  </a>

  <a href="index.php?page=visualizar&diretoria=Infra Grandes Obras" class="card-link">
    <div class="card-conteudo">
      <div class="card-titulo">
        <div>Infra</div>
        <div>Grandes Obras</div>
      </div>
    </div>
  </a>

  <a href="index.php?page=visualizar&diretoria=Seguranca" class="card-link">
    <div class="card-conteudo">Segurança</div>
  </a>

  <a href="index.php?page=visualizar&diretoria=Social" class="card-link">
    <div class="card-conteudo">Social</div>
  </a>

</div>

<div class="botao-sair">
  <a href="templates/sair.php" style="background-color: red; color: white; font-weight: bold; padding: 10px 20px; border-radius: 10px; display: inline-block; text-decoration: none;">Sair</a>
</div>


