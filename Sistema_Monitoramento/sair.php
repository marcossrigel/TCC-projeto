<?php
// sair.php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// limpa os dados da sessão
$_SESSION = [];

// apaga o cookie da sessão (se existir)
if (ini_get('session.use_cookies')) {
  $p = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// destrói a sessão
session_destroy();

// headers para evitar cache de páginas autenticadas
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// redireciona para o GETIC
$destino = 'https://www.getic.pe.gov.br/?p=home';
header('Location: ' . $destino, true, 302);
exit;

// (fallback caso headers já tenham sido enviados)
?>
<!DOCTYPE html>
<html lang="pt-BR"><meta charset="utf-8">
<meta http-equiv="refresh" content="0;url=https://www.getic.pe.gov.br/?p=home">
<script>location.replace('https://www.getic.pe.gov.br/?p=home');</script>
Saindo...
</html>
