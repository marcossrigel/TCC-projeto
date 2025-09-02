<div class="container">
  <div class="header">
    <div class="header-text">
      <p>Olá, <?php echo $_SESSION['nome']; ?>!</p>
      <h1>Bem-vindo ao Sistema de Monitoramento</h1>
      <p>Organize e cadastre suas informações com eficiência e facilidade.</p>
    </div>

    <div class="button-group">
      <a href="index.php?page=formulario" class="btn">Criar Iniciativa</a>
      <a href="index.php?page=visualizar" class="btn btn-secondary">Minhas Vistorias</a>
      <a href="templates/sair.php" style="color: red; font-weight: bold;" class="texto-login">Sair</a>
    </div>
  </div>

  <div class="accordion" onclick="toggleAccordion()">
    <div class="accordion-header">
      <h2>Ajuda</h2>
      <span id="accordion-icon">⌄</span>
    </div>
    <div id="accordion-content" class="accordion-content hidden">
      <p>Para criar novas iniciativas, clique em "Criar Iniciativa". Você será levado a um formulário onde poderá cadastrar os dados iniciais.</p>
      <p>Em "Minhas Vistorias", você pode visualizar e editar as informações já cadastradas.</p>
    </div>
  </div>
</div>

<script src="js/home.js"></script>
