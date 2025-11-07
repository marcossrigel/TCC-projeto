// ---- Auto-grow para textareas já presentes ----
document.querySelectorAll('.obs').forEach(autoGrow);

function autoGrow(textarea) {
  const grow = function () {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
  };
  textarea.addEventListener('input', grow);
  grow.call(textarea);
}

// ---- Cria a célula de ações (+ / -) ----
function criarCelulaAcoes(tr, id = null) {
  const td = tr.insertCell();
  td.className = 'celula-acoes';

  const botaoAdd = document.createElement('button');
  botaoAdd.type = 'button';
  botaoAdd.innerHTML = '➕';
  botaoAdd.className = 'botao-acao botao-mais';
  botaoAdd.onclick = () => inserirAntes(botaoAdd);

  const botaoDelete = document.createElement('button');
  botaoDelete.type = 'button';
  botaoDelete.innerHTML = '❌';
  botaoDelete.className = 'botao-acao botao-menos';
  botaoDelete.onclick = () => { id ? deletarLinha(id) : tr.remove(); };

  td.appendChild(botaoAdd);
  td.appendChild(botaoDelete);
}

// ---- Helper para criar campo conforme o tipo ----
function criarCampo(celula, campo) {
  if (campo.name === 'observacao[]') {
    const ta = document.createElement('textarea');
    ta.name = campo.name;
    ta.className = 'obs';
    ta.rows = 1;
    ta.wrap = 'soft';
    celula.appendChild(ta);
    autoGrow(ta);
  } else {
    const input = document.createElement('input');
    input.name = campo.name;
    input.type = campo.type;
    input.required = false;
    if (campo.name === 'ordem[]') {
      input.className = 'num';
      // hidden ids[] junto da Ordem
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'ids[]';
      hidden.value = '0';
      celula.appendChild(hidden);
    }
    celula.appendChild(input);
  }
}

// ---- Inserir linha antes da linha clicada ----
function inserirAntes(botao) {
  const linhaRef = botao.closest('tr');
  const tbody = document.getElementById('medicoes').tBodies[0];
  const idx = linhaRef.sectionRowIndex;
  const novaLinha = tbody.insertRow(idx);

  const campos = [
    { name: 'ordem[]',            type: 'text' },
    { name: 'etapa[]',            type: 'text' },
    { name: 'responsavel[]',      type: 'text' },
    { name: 'inicio_previsto[]',  type: 'date' },
    { name: 'termino_previsto[]', type: 'date' },
    { name: 'inicio_real[]',      type: 'date' },
    { name: 'termino_real[]',     type: 'date' },
    { name: 'status[]',           type: 'text' },
    { name: 'observacao[]',       type: 'textarea' }
  ];

  campos.forEach(campo => {
    const cel = novaLinha.insertCell();
    criarCampo(cel, campo);
  });

  criarCelulaAcoes(novaLinha);
}

// ---- Adicionar linha no final ----
function adicionarLinha() {
  const tbody = document.getElementById('medicoes').tBodies[0];
  const novaLinha = tbody.insertRow();

  const campos = [
    { name: 'ordem[]',            type: 'text' },
    { name: 'etapa[]',            type: 'text' },
    { name: 'responsavel[]',      type: 'text' },
    { name: 'inicio_previsto[]',  type: 'date' },
    { name: 'termino_previsto[]', type: 'date' },
    { name: 'inicio_real[]',      type: 'date' },
    { name: 'termino_real[]',     type: 'date' },
    { name: 'status[]',           type: 'text' },
    { name: 'observacao[]',       type: 'textarea' }
  ];

  campos.forEach(campo => {
    const celula = novaLinha.insertCell();
    criarCampo(celula, campo);
  });

  criarCelulaAcoes(novaLinha);
}

// ---- Deletar linha salva (backend) ----
function deletarLinha(id) {
  if (!confirm('Tem certeza que deseja excluir esta linha?')) return;

  fetch('templates/deletar_linha.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id=' + encodeURIComponent(id)
  })
  .then(r => r.text())
  .then(data => {
    if (data.includes('sucesso')) {
      const linha = document.querySelector(`tr[data-id='${id}']`);
      if (linha) linha.remove();
    } else {
      alert('Erro ao excluir: ' + data);
    }
  })
  .catch(err => alert('Erro de rede: ' + err));
}
