<<<<<<< HEAD
<?php
session_start();
require_once('templates/config.php');

// valida se os campos vieram
if (!isset($_POST['usuario'], $_POST['senha'])) {
    die("Requisição inválida.");
}

$login = trim($_POST['usuario']);
$senha = trim($_POST['senha']);

// use o nome correto da coluna: usuario_rede
$sql = "SELECT 
            id_usuario,
            usuario_rede AS usuario, 
            nome, 
            senha, 
            tipo
        FROM usuarios
        WHERE usuario_rede = ? AND senha = ?
        LIMIT 1";

$stmt = mysqli_prepare($conexao, $sql);
if (!$stmt) {
    die("Erro na preparação da query: " . mysqli_error($conexao));
}

mysqli_stmt_bind_param($stmt, "ss", $login, $senha);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($result);

if (!$usuario) {
    echo "<script>alert('Usuário ou senha inválidos'); window.location.href = 'login.php';</script>";
    exit;
}

// cria sessão
$_SESSION['id_usuario']   = $usuario['id_usuario'];
$_SESSION['usuario']      = $usuario['usuario']; // alias acima
$_SESSION['nome']         = $usuario['nome'];
$_SESSION['tipo_usuario'] = $usuario['tipo'];
// $_SESSION['setor']      = null; // só use se você realmente tiver essa coluna depois

// redireciona conforme tipo
$page = ($usuario['tipo'] === 'admin') ? 'diretorias' : 'home';
header("Location: index.php?page=" . $page);
exit;
=======
<?php
session_start();
require_once('templates/config.php');

// valida se os campos vieram
if (!isset($_POST['usuario'], $_POST['senha'])) {
    die("Requisição inválida.");
}

$login = trim($_POST['usuario']);
$senha = trim($_POST['senha']);

// use o nome correto da coluna: usuario_rede
$sql = "SELECT 
            id_usuario,
            usuario_rede AS usuario, 
            nome, 
            senha, 
            tipo
        FROM usuarios
        WHERE usuario_rede = ? AND senha = ?
        LIMIT 1";

$stmt = mysqli_prepare($conexao, $sql);
if (!$stmt) {
    die("Erro na preparação da query: " . mysqli_error($conexao));
}

mysqli_stmt_bind_param($stmt, "ss", $login, $senha);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($result);

if (!$usuario) {
    echo "<script>alert('Usuário ou senha inválidos'); window.location.href = 'login.php';</script>";
    exit;
}

// cria sessão
$_SESSION['id_usuario']   = $usuario['id_usuario'];
$_SESSION['usuario']      = $usuario['usuario']; // alias acima
$_SESSION['nome']         = $usuario['nome'];
$_SESSION['tipo_usuario'] = $usuario['tipo'];
// $_SESSION['setor']      = null; // só use se você realmente tiver essa coluna depois

// redireciona conforme tipo
$page = ($usuario['tipo'] === 'admin') ? 'diretorias' : 'home';
header("Location: index.php?page=" . $page);
exit;
>>>>>>> 7a6b3a60ed50304554a32283faa4a38b5b504435
