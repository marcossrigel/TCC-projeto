<?php
// templates/compartilhar.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

if (empty($_SESSION['id_usuario'])) {
  http_response_code(401);
  exit('Sem sessão ativa.');
}

require_once __DIR__ . '/config.php'; // garante $conexao

$id_usuario = (int)$_SESSION['id_usuario'];

/* ===========================
   1) Minhas iniciativas
   =========================== */
$sql_iniciativas = "SELECT id, iniciativa FROM iniciativas WHERE id_usuario = ? ORDER BY id DESC";
$stmtIni = $conexao->prepare($sql_iniciativas);
$stmtIni->bind_param('i', $id_usuario);
$stmtIni->execute();
$res_iniciativas = $stmtIni->get_result();

/* ===========================
   2) Já compartilhado (usuario + iniciativas)
   =========================== */
$sql_comp = "
  SELECT 
      u.id_usuario            AS id_usuario,
      u.nome                  AS nome_usuario,
      u.usuario_rede          AS usuario_rede,
      i.id                    AS id_iniciativa,
      i.iniciativa            AS nome_iniciativa
  FROM compartilhamentos c
  JOIN usuarios u    ON u.id_usuario = c.id_compartilhado
  JOIN iniciativas i ON i.id = c.id_iniciativa
  WHERE c.id_dono = ?
  ORDER BY u.nome, i.iniciativa
";
$stmtComp = $conexao->prepare($sql_comp);
$stmtComp->bind_param('i', $id_usuario);
$stmtComp->execute();
$res_comp = $stmtComp->get_result();

/* monta mapa: usuario => iniciativas[] */
$compart_map = [];
if ($res_comp && $res_comp->num_rows) {
  while ($r = $res_comp->fetch_assoc()) {
    $uid = (int)$r['id_usuario'];
    if (!isset($compart_map[$uid])) {
      $compart_map[$uid] = [
        'nome'         => $r['nome_usuario'],
        'usuario_rede' => $r['usuario_rede'],
        'iniciativas'  => []
      ];
    }
    $compart_map[$uid]['iniciativas'][] = [
      'id'   => (int)$r['id_iniciativa'],
      'nome' => $r['nome_iniciativa']
    ];
  }
}
?>

<div class="space-y-5">
  <h2 class="text-lg font-semibold text-slate-800">Compartilhar Iniciativas</h2>

  <form id="formCompartilhar" class="space-y-4">
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Nome do Usuário (REDE)</label>
      <div class="relative">
        <input type="text" name="usuario" id="cmp_usuario"
               placeholder="Digite o nome do usuário da rede"
               required
               class="w-full border rounded-lg px-3 py-2">
        <div id="cmp_sugestoes"
             class="absolute left-0 right-0 top-full bg-white border rounded-md shadow max-h-52 overflow-y-auto hidden z-10"></div>
      </div>
    </div>

    <div class="pt-2">
      <div class="font-medium text-slate-800 mb-2">Selecione as iniciativas a compartilhar:</div>

      <label class="inline-flex items-center gap-2 mb-2">
        <input type="checkbox" id="cmp_todos" class="rounded border-slate-300">
        <span>Selecionar todas</span>
      </label>

      <div class="grid sm:grid-cols-2 gap-2">
        <?php if ($res_iniciativas && $res_iniciativas->num_rows): ?>
          <?php while ($l = $res_iniciativas->fetch_assoc()): ?>
            <label class="flex items-start gap-2 p-2 border rounded-lg bg-white">
              <input type="checkbox" name="iniciativas[]" value="<?= (int)$l['id'] ?>" class="mt-1 rounded border-slate-300">
              <span class="text-sm"><?= htmlspecialchars($l['iniciativa'], ENT_QUOTES, 'UTF-8') ?></span>
            </label>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="text-slate-500">Você não possui iniciativas para compartilhar.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="flex items-center justify-end gap-2">
      <button type="button" data-close-compartilhar
              class="rounded-full px-4 py-2 border border-slate-300 text-slate-800 hover:bg-slate-50">Cancelar</button>
      <button type="submit"
              class="rounded-full px-5 py-2 bg-blue-600 text-white font-semibold hover:bg-blue-700">Compartilhar</button>
    </div>
  </form>

  <hr class="border-slate-200">

  <div>
    <div class="font-medium text-slate-800 mb-2">Já compartilhado com:</div>

    <?php if (!empty($compart_map)): ?>
      <ul class="divide-y rounded-lg border bg-white">
        <?php foreach ($compart_map as $uid => $user): ?>
          <li class="p-3">
            <div class="flex items-center gap-2">
              <img src="img/user.png" class="h-5 w-5 rounded-full shrink-0" alt="">
              <div class="flex-1 min-w-0">
                <div class="text-sm text-slate-800 truncate">
                  <?= htmlspecialchars($user['nome']) ?>
                  <?php if (!empty($user['usuario_rede'])): ?>
                    <span class="text-slate-500">(@<?= htmlspecialchars($user['usuario_rede']) ?>)</span>
                  <?php endif; ?>
                </div>

                <?php if (!empty($user['iniciativas'])): ?>
                  <div class="mt-1 flex flex-wrap gap-1">
                    <?php foreach ($user['iniciativas'] as $ini): ?>
                      <span class="text-xs bg-slate-100 border rounded px-2 py-0.5">
                        <?= htmlspecialchars($ini['nome']) ?>
                      </span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

              <button class="cmp-remover text-red-600 hover:underline text-sm"
                      title="Remover todos os compartilhamentos com este usuário"
                      data-id="<?= (int)$uid ?>">
                Remover
              </button>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="text-slate-500">Nenhum usuário ainda.</div>
    <?php endif; ?>
  </div>
</div>
