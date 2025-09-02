<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

include("config.php");

$id_usuario = $_SESSION['id_usuario'];

$sql_iniciativas = "SELECT id, iniciativa FROM iniciativas WHERE id_usuario = $id_usuario";

$res_iniciativas = $conexao->query($sql_iniciativas);

$sql_compartilhados = "
    SELECT DISTINCT u.nome AS nome_usuario, u.id_usuario
    FROM compartilhamentos c
    JOIN iniciativas i  ON i.id = c.id_iniciativa   -- garante que é iniciativa do dono
    JOIN usuarios    u  ON u.id_usuario = c.id_compartilhado
    WHERE i.id_usuario = $id_usuario                -- dono = usuário logado
      AND u.tipo = 'comum'                          -- só listar 'comum'
";
$res_compartilhados = $conexao->query($sql_compartilhados);
?>

<div class="pagina-formulario">
  <div class="formulario">
    <h2 class="main-title">Compartilhar Iniciativas</h2>
    
    <form action="index.php?page=salvar_compartilhamento" method="post">
        <label for="usuario" class="label">Nome do Usuário (REDE):</label>
        <input type="text" name="usuario" id="usuario" placeholder="Digite o nome do usuário da rede" required>

        <h3 style="margin-top: 20px;">Selecione as iniciativas a compartilhar:</h3>
        <div style="margin: 6px 0;">
            <input type="checkbox" id="selecionar_tudo">
            <label for="selecionar_tudo">Selecionar Todas</label>
        </div>
        <?php if ($res_iniciativas->num_rows > 0): ?>
            <?php while ($linha = $res_iniciativas->fetch_assoc()): ?>
                <div style="margin: 6px 0;">
                    <input type="checkbox" name="iniciativas[]" value="<?= $linha['id'] ?>" id="inic<?= $linha['id'] ?>">
                    <label for="inic<?= $linha['id'] ?>"><?= htmlspecialchars($linha['iniciativa']) ?></label>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Você não possui iniciativas para compartilhar.</p>
        <?php endif; ?>

        <br>
        <button type="submit" class="btn">Compartilhar</button>
        <a href="index.php?page=visualizar" class="texto-login">Cancelar</a>
    </form>

    <hr>

    <h3 style="margin-top: 30px;">Já Compartilhado com:</h3>
    <ul class="lista-compartilhados">
        <?php if ($res_compartilhados->num_rows > 0): ?>
            <?php while ($linha = $res_compartilhados->fetch_assoc()): ?>
                <li>
                    <img src="perfil.png" alt="Foto de perfil" class="icone-usuario">
                    <span><?= htmlspecialchars($linha['nome_usuario']) ?></span>
                    <button class="btn-remover" data-id="<?= $linha['id_usuario'] ?>" title="Remover compartilhamento">🗑️</button>
                </li>

                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhum usuário ainda.</p>
        <?php endif; ?>
    </ul>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const inputUsuario = document.getElementById("usuario");

    const listaSugestoes = document.createElement("div");
    listaSugestoes.style.position = "absolute";
    listaSugestoes.style.background = "#fff";
    listaSugestoes.style.border = "1px solid #ccc";
    listaSugestoes.style.zIndex = "999";
    listaSugestoes.style.width = inputUsuario.offsetWidth + "px";
    listaSugestoes.style.maxHeight = "150px";
    listaSugestoes.style.overflowY = "auto";
    listaSugestoes.style.display = "none";
    inputUsuario.parentNode.appendChild(listaSugestoes);

    inputUsuario.addEventListener("input", function () {
        const termo = this.value;
        if (termo.length < 2) {
            listaSugestoes.style.display = "none";
            return;
        }

        fetch(`templates/compartilhar_buscar_usuario.php?termo=${encodeURIComponent(termo)}`)
            .then(res => res.json())
            .then(data => {
                listaSugestoes.innerHTML = "";
                data.forEach(usuario => {
                    const div = document.createElement("div");
                    div.textContent = usuario;
                    div.style.padding = "8px";
                    div.style.cursor = "pointer";
                    div.addEventListener("click", function () {
                        inputUsuario.value = usuario;
                        listaSugestoes.style.display = "none";
                    });
                    listaSugestoes.appendChild(div);
                });
                listaSugestoes.style.display = "block";
            });
    });

    document.addEventListener("click", function (e) {
        if (!inputUsuario.contains(e.target)) {
            listaSugestoes.style.display = "none";
        }
    });
});

document.querySelectorAll(".btn-remover").forEach(button => {
    button.addEventListener("click", function () {
        const id = this.getAttribute("data-id");
        if (confirm("Deseja remover este compartilhamento?")) {
            fetch("templates/remover_compartilhamento.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `id_compartilhado=${id}`
            })
            .then(res => res.text())
            .then(data => {
                if (data.trim() === "OK") {
                    location.reload();
                } else {
                    alert("Erro ao remover compartilhamento.");
                }
            });
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
  const selecionarTudo = document.getElementById("selecionar_tudo");
  const checkboxes = document.querySelectorAll("input[name='iniciativas[]']");

  selecionarTudo.addEventListener("change", function () {
    checkboxes.forEach(cb => cb.checked = selecionarTudo.checked);
  });

  checkboxes.forEach(cb => {
    cb.addEventListener("change", () => {
      const todosMarcados = Array.from(checkboxes).every(c => c.checked);
      selecionarTudo.checked = todosMarcados;
    });
  });
});

</script>
