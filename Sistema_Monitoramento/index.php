<?php
session_start();
require_once("templates/config.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : null;

if (!$page) {
    $page = ($_SESSION["tipo_usuario"] === "admin") ? "diretorias" : "home";
    header("Location: index.php?page=".$page);
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sistema de Monitoramento</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<?php
  $cssMap = [
    'home' => 'home.css',
    'diretorias' => 'diretorias.css',
    'formulario' => 'formulario.css',
    'editar_iniciativa' => 'editar_iniciativa.css',
    'acompanhamento' => 'acompanhamento.css',
    'info_contratuais' => 'info_contratuais.css',
    'medicoes' => 'medicoes.css',
    'cronogramamarcos' => 'cronogramamarcos.css',
    'compartilhar' => 'formulario.css',
    'remover_compartilhamento' => 'formulario.css',
    'salvar_compartilhamento' => 'formulario.css',
    'projeto_licitacoes' => 'medicoes.css',
    'deletar_linha' => null,
    'excluir_linha' => null,
    'marcos_excluir_linha' => null,
    'excluir_linha_medicoes' => null,
    'excluir_pendencia' => null,
    'marcar_concluida' => null,
  ];
  if (isset($cssMap[$page]) && $cssMap[$page]) {
      echo '<link rel="stylesheet" href="assets/css/' . $cssMap[$page] . '">';
  }
?>
</head>
<body style="height: 100vh; display: flex; flex-direction: column;">
  <main>
<?php
  $allowedPages = array_keys($cssMap);
  if (in_array($page, $allowedPages)) {
    include_once 'templates/' . $page . '.php';
  } else {
    echo "<p style='text-align:center;'>Página não encontrada.</p>";
  }
?>
  </main>
</body>
</html>
