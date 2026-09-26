<?php require BASE_PATH . '/views/layout/header.php'; ?>

<?php
  $isEdit = !empty($transacao['id']);
  $carteiraAtual = (int)($transacao['carteira_id'] ?? $carteiraSelecionada ?? ($wallets[0]['id'] ?? 1));
  $tipoAtual = $transacao['tipo'] ?? 'COMPRA';
  $qtdInicial = (float)($transacao['quantidade'] ?? 0);
  $precoInicial = (float)($transacao['preco_unitario'] ?? 0);
  $taxaInicial = (float)($transacao['taxas'] ?? 0);
  $valorOpInicial = ($qtdInicial * $precoInicial) - $taxaInicial;
  $valorOpFormatado = ($qtdInicial > 0 || $precoInicial > 0) ? number_format($valorOpInicial, 2, ',', '.') : '';
  $alertaPosicaoServidor = flash('alerta_posicao');
?>

<div class="max-w-2xl bg-white border border-slate-200/90 rounded-2xl shadow-sm p-7">
  <div class="mb-6 pb-4 border-b border-slate-100 flex items-center justify-between">
    <div>
      <h2 class="text-base font-extrabold text-slate-900 tracking-tight"><?= $isEdit ? 'Editar Operação' : 'Nova Operação' ?></h2>
      <p class="text-xs text-slate-500 font-medium mt-0.5"><?= $isEdit ? 'Atualize os dados da operação selecionada.' : 'Preencha os dados para registrar a operação.' ?></p>
    </div>
    <?php if ($isEdit): ?>
      <button type="button" onclick="abrirModalExclusaoForm()" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-100 transition-colors shadow-sm" title="Excluir esta operação">
        <i class="ph ph-trash text-sm"></i> Excluir
      </button>
    <?php endif; ?>
  </div>

  <form method="POST" id="form-operacao">
    <?= csrf_field() ?>
    <input type="hidden" name="bypass_posicao" id="bypass_posicao" value="0">

    <div class="space-y-4">

      <!-- Card de Alerta: Venda sem posse do ativo -->
      <div id="card-alerta-posicao" class="<?= $alertaPosicaoServidor ? '' : 'hidden' ?> rounded-xl border border-amber-200 bg-amber-50/90 p-4 transition-all shadow-sm">
        <div class="flex items-start gap-3">
          <div class="rounded-lg bg-amber-100 p-2 text-amber-700 shrink-0">
            <i class="ph ph-warning-circle text-xl"></i>
          </div>
          <div class="flex-1 min-w-0">
            <h4 class="text-sm font-bold text-amber-900">Atenção: Venda sem posse do ativo</h4>
            <p id="msg-alerta-posicao" class="text-xs text-amber-700 mt-1 leading-relaxed">
              <?= $alertaPosicaoServidor ? e($alertaPosicaoServidor) : 'Você não possui este ativo nesta carteira para realizar a venda.' ?>
            </p>
            <div class="mt-3 flex items-center gap-2">
              <button type="button" onclick="abrirModalBypass()" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-700 shadow-sm transition-colors">
                <i class="ph ph-shield-warning"></i> Seguir com a operação assim mesmo
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Linha 1: Primeiro campo Ativo, na mesma linha campo slide para definir a carteira -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ativo</label>
          <select name="ativo_id" id="ativo_id_select" required class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
            <option value="">Selecione um ativo...</option>
            <?php foreach ($ativos as $a): ?>
              <option value="<?= (int)$a['id'] ?>" <?= (isset($transacao['ativo_id']) && (int)$transacao['ativo_id'] === (int)$a['id']) ? 'selected' : '' ?>>
                <?= e($a['ticker']) ?> - <?= e($a['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Carteira</label>
          <input type="hidden" name="carteira_id" id="carteira_id_input" value="<?= $carteiraAtual ?>">
          <div class="relative flex items-center rounded-xl border border-slate-200 bg-slate-100/80 p-1 select-none h-[42px]" id="carteira-slide-container">
            <?php foreach ($wallets as $w): 
              $isSelected = ($carteiraAtual === (int)$w['id']);
              $wNome = strtolower(trim($w['nome']));
              $isRenato = str_contains($wNome, 'renato');
              $corAtiva = $isRenato 
                ? 'bg-gradient-to-r from-purple-700 via-purple-600 to-indigo-600 text-white shadow-[0_2px_8px_rgba(126,34,206,0.28)]' 
                : 'bg-gradient-to-r from-emerald-600 via-teal-600 to-teal-700 text-white shadow-[0_2px_8px_rgba(13,148,136,0.28)]';
            ?>
              <button type="button"
                data-wallet-id="<?= (int)$w['id'] ?>"
                data-wallet-color="<?= $isRenato ? 'purple' : 'emerald' ?>"
                class="carteira-slide-btn flex-1 text-center py-1.5 px-3 rounded-lg text-xs font-bold transition-all duration-150 z-10 <?= $isSelected ? $corAtiva : 'text-slate-600 hover:text-slate-900' ?>">
                <span class="inline-flex items-center gap-1.5 justify-center">
                  <span class="wallet-dot w-2 h-2 rounded-full <?= $isSelected ? 'bg-white' : ($isRenato ? 'bg-purple-600' : 'bg-emerald-500') ?>"></span>
                  <?= e($w['nome']) ?>
                </span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Linha 2: Tipo de Operação em Slide e Data da Operação -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tipo de Operação</label>
          <input type="hidden" name="tipo" id="tipo_operacao_input" value="<?= e($tipoAtual) ?>">
          <div class="relative flex items-center rounded-xl border border-slate-200 bg-slate-100/80 p-1 select-none h-[42px]" id="tipo-slide-container">
            <button type="button"
              data-tipo="COMPRA"
              class="tipo-slide-btn flex-1 text-center py-1.5 px-3 rounded-lg text-xs font-bold transition-all duration-150 z-10 <?= $tipoAtual === 'COMPRA' ? 'bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-[0_2px_8px_rgba(29,78,216,0.25)]' : 'text-slate-600 hover:text-slate-900' ?>">
              Compra
            </button>
            <button type="button"
              data-tipo="VENDA"
              class="tipo-slide-btn flex-1 text-center py-1.5 px-3 rounded-lg text-xs font-bold transition-all duration-150 z-10 <?= $tipoAtual === 'VENDA' ? 'bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-[0_2px_8px_rgba(29,78,216,0.25)]' : 'text-slate-600 hover:text-slate-900' ?>">
              Venda
            </button>
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Data da Operação</label>
          <input type="date" name="data_operacao" required
            value="<?= e($transacao['data_operacao'] ?? date('Y-m-d')) ?>"
            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
        </div>
      </div>

      <!-- Linha 3: Campo quantidade e preço unitário na mesma linha -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Quantidade</label>
            <span id="badge-posicao-disponivel" class="text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200/60 hidden"></span>
          </div>
          <input type="number" name="quantidade" id="input_quantidade" min="1" required
            value="<?= e((string)($transacao['quantidade'] ?? '')) ?>"
            placeholder="Ex: 100"
            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Preço Unitário (R$)</label>
          <input type="text" inputmode="decimal" name="preco_unitario" id="input_preco_unitario" required
            value="<?= e(formatar_preco_unitario($transacao['preco_unitario'] ?? null)) ?>"
            placeholder="Ex: 35,50 ou 10,123456"
            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
          <span class="text-[11px] text-slate-400 mt-1 block">Permite cálculo com até 6 casas decimais (separador: vírgula)</span>
        </div>
      </div>

      <!-- Linha 4: Campo Taxa e Valor da operação na mesma linha (Qtd * Preço Unitário - Taxa) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Taxas (R$)</label>
          <input type="text" inputmode="decimal" name="taxas" id="input_taxas"
            value="<?= !empty($transacao['taxas']) ? number_format((float)$transacao['taxas'], 2, ',', '.') : '0,00' ?>"
            placeholder="0,00"
            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Valor da Operação (R$)</label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">R$</span>
            <input type="text" inputmode="decimal" id="input_valor_operacao"
              value="<?= $valorOpFormatado ?>"
              placeholder="0,00"
              title="Digite o valor total para calcular o preço unitário ou preencha quantidade e preço unitário"
              class="w-full rounded-lg border border-slate-300 bg-white pl-11 pr-3.5 py-2 text-sm font-extrabold text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
          </div>
          <span class="text-[11px] text-slate-500 mt-1 block">Editável: digite o valor total para calcular o preço unitário ou vice-versa</span>
        </div>
      </div>

      <!-- Linha 5: Observação -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Observação</label>
        <input type="text" name="observacao"
          value="<?= e($transacao['observacao'] ?? '') ?>"
          placeholder="Observação opcional..."
          class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
      </div>

    </div>

    <!-- Botões de Ação (Apenas Salvar e Cancelar) -->
    <div class="flex items-center gap-3 pt-5 mt-6 border-t border-slate-100">
      <button type="submit" id="btn-salvar-operacao" class="btn-primary inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider px-5 py-2.5">
        <i class="ph ph-check text-sm"></i> Salvar Operação
      </button>
      <a href="<?= BASE_URL ?>/operacoes.php" class="btn-secondary inline-flex items-center gap-2 text-xs font-semibold px-4 py-2.5">
        Cancelar
      </a>
    </div>
  </form>
</div>

<!-- Modal Popup de Bypass para Venda sem Posição -->
<div id="modal-bypass-posicao" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4 transition-opacity">
  <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/80 shadow-2xl max-w-sm w-full p-6 text-center transform transition-all animate-in fade-in zoom-in-95 duration-150">
    <div class="mx-auto w-12 h-12 rounded-full bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mb-4">
      <i class="ph ph-warning-circle text-2xl"></i>
    </div>
    <h3 class="text-base font-bold text-slate-900 mb-2">Venda sem Posse do Ativo</h3>
    <p id="modal-bypass-msg" class="text-xs text-slate-600 mb-6 leading-relaxed">
      Você está tentando registrar uma venda de um ativo que não possui nesta carteira.<br><br>
      Deseja <strong>seguir com a operação assim mesmo</strong>?
    </p>
    <div class="flex items-center justify-center gap-3">
      <button type="button" onclick="fecharModalBypass()" class="btn-secondary flex-1 py-2 text-xs">
        Revisar
      </button>
      <button type="button" onclick="confirmarBypassESalvar()" class="flex-1 rounded-lg bg-amber-600 hover:bg-amber-700 px-4 py-2 text-xs font-bold text-white shadow-sm transition-colors">
        Sim, seguir
      </button>
    </div>
  </div>
</div>

<?php if ($isEdit): ?>
<!-- Modal Popup de Certeza para Exclusão dentro do Formulário -->
<div id="modal-exclusao-form" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
  <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/80 shadow-2xl max-w-sm w-full p-6 text-center transform transition-all animate-in fade-in zoom-in-95 duration-150">
    <div class="mx-auto w-12 h-12 rounded-full bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center mb-4">
      <i class="ph ph-warning text-2xl"></i>
    </div>
    <h3 class="text-base font-bold text-slate-900 mb-2">Excluir Operação?</h3>
    <p class="text-xs text-slate-500 mb-6 leading-relaxed">
      Tem certeza que deseja excluir esta operação permanentemente?<br>Esta ação não poderá ser desfeita.
    </p>
    <div class="flex items-center justify-center gap-3">
      <button type="button" onclick="fecharModalExclusaoForm()" class="btn-secondary flex-1 py-2 text-xs">
        Cancelar
      </button>
      <form method="POST" action="<?= BASE_URL ?>/operacoes.php?action=delete" class="flex-1 m-0">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$transacao['id'] ?>">
        <button type="submit" class="w-full rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs py-2.5 shadow-sm transition-colors">
          Sim, excluir
        </button>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
(function () {
  const form = document.getElementById('form-operacao');
  const ativoSelect = document.getElementById('ativo_id_select');
  const carteiraInput = document.getElementById('carteira_id_input');
  const tipoInput = document.getElementById('tipo_operacao_input');
  const quantidadeInput = document.getElementById('input_quantidade');
  const precoUnitarioInput = document.getElementById('input_preco_unitario');
  const taxasInput = document.getElementById('input_taxas');
  const valorOperacaoInput = document.getElementById('input_valor_operacao');
  const carteiraBtns = document.querySelectorAll('.carteira-slide-btn');
  const tipoBtns = document.querySelectorAll('.tipo-slide-btn');
  const bypassInput = document.getElementById('bypass_posicao');
  const alertaCard = document.getElementById('card-alerta-posicao');
  const alertaMsg = document.getElementById('msg-alerta-posicao');
  const badgePosicao = document.getElementById('badge-posicao-disponivel');

  let posicaoDisponivel = null;
  let bypassConfirmado = false;
  let origemUltimoCalculo = 'preco'; // 'preco' ou 'valor'

  // Parser robusto para números no padrão brasileiro (vírgula como separador decimal)
  function parseValorInput(val) {
    if (val === null || val === undefined) return 0;
    let s = String(val).trim();
    if (!s) return 0;

    // Remove R$, espaços e caracteres não numéricos exceto vírgulas e pontos
    s = s.replace(/R\$\s*/gi, '').replace(/\s+/g, '');

    // Se possui ponto e vírgula (ex: 1.530,45 ou 1,530.45)
    if (s.includes('.') && s.includes(',')) {
      if (s.lastIndexOf(',') > s.lastIndexOf('.')) {
        // Padrão brasileiro: 1.530,45 -> remove pontos de milhar e vírgula vira ponto
        s = s.replace(/\./g, '').replace(',', '.');
      } else {
        // Padrão internacional: 1,530.45
        s = s.replace(/,/g, '');
      }
    } else if (s.includes(',')) {
      // Apenas vírgula decimal (ex: 1530,45 ou 35,50 ou 10,123456)
      s = s.replace(',', '.');
    } else if (s.includes('.')) {
      // Apenas pontos
      if ((s.match(/\./g) || []).length > 1) {
        // Múltiplos pontos (ex: 1.500.000)
        s = s.replace(/\./g, '');
      }
    }

    const num = parseFloat(s);
    return isNaN(num) ? 0 : num;
  }

  // Helper para formatar o preço unitário em pt-BR com até 6 casas decimais (usando vírgula)
  function formatUnitaryPrice(preco) {
    if (!preco || isNaN(preco) || preco <= 0) return '';
    const fixed = Number(preco).toFixed(6);
    let [inteiro, decimais] = fixed.split('.');
    decimais = (decimais || '').replace(/0+$/, '');
    if (decimais.length < 2) {
      decimais = decimais.padEnd(2, '0');
    }
    return `${inteiro},${decimais}`;
  }

  // 1. Cálculo a partir do Preço Unitário: Valor da Operação = (Quantidade * Preço Unitário) - Taxas
  function calcularValorOperacao() {
    origemUltimoCalculo = 'preco';
    const qtd = parseFloat(quantidadeInput?.value) || 0;
    const preco = parseValorInput(precoUnitarioInput?.value);
    const taxa = parseValorInput(taxasInput?.value);
    const valorOp = (qtd * preco) - taxa;

    if (valorOperacaoInput) {
      if (qtd > 0 && preco > 0) {
        valorOperacaoInput.value = valorOp.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const valorExatoStr = valorOp.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 6 });
        valorOperacaoInput.title = `Cálculo: (${qtd} × ${preco.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 6 })}) - Taxas = R$ ${valorExatoStr}`;
      } else if (preco === 0 && qtd === 0) {
        valorOperacaoInput.value = '';
        valorOperacaoInput.title = 'Digite o valor total para calcular o preço unitário ou preencha quantidade e preço unitário';
      }
    }
  }

  // 2. Cálculo inverso a partir do Valor da Operação: Preço Unitário = (Valor da Operação + Taxas) / Quantidade
  function calcularPrecoUnitario() {
    origemUltimoCalculo = 'valor';
    const qtd = parseFloat(quantidadeInput?.value) || 0;
    const valorOp = parseValorInput(valorOperacaoInput?.value);
    const taxa = parseValorInput(taxasInput?.value);

    if (precoUnitarioInput && qtd > 0 && valorOp > 0) {
      const preco = (valorOp + taxa) / qtd;
      precoUnitarioInput.value = formatUnitaryPrice(preco);
      precoUnitarioInput.title = `Preço calculado: (R$ ${valorOp.toLocaleString('pt-BR', { minimumFractionDigits: 2 })} + Taxas R$ ${taxa.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}) / ${qtd} = R$ ${preco.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 6 })}`;
    }
  }

  function sincronizarPrecoAntesDeEnviar() {
    const qtd = parseFloat(quantidadeInput?.value) || 0;
    const valorOp = parseValorInput(valorOperacaoInput?.value);
    const taxa = parseValorInput(taxasInput?.value);
    if (origemUltimoCalculo === 'valor' && qtd > 0 && valorOp > 0 && precoUnitarioInput) {
      const preco = (valorOp + taxa) / qtd;
      precoUnitarioInput.value = formatUnitaryPrice(preco);
    }
  }

  // 3. Verificação de Posse do Ativo para Venda
  function verificarPosseAtivo(abrirPopupSeErro = false) {
    if (bypassConfirmado || bypassInput?.value === '1') {
      if (alertaCard) alertaCard.classList.add('hidden');
      return true;
    }

    const tipo = tipoInput?.value || 'COMPRA';
    const qtd = parseInt(quantidadeInput?.value, 10) || 0;

    if (tipo !== 'VENDA' || posicaoDisponivel === null) {
      if (alertaCard && !<?= $alertaPosicaoServidor ? 'true' : 'false' ?>) {
        alertaCard.classList.add('hidden');
      }
      return true;
    }

    const tickerNome = ativoSelect?.options[ativoSelect.selectedIndex]?.text || 'este ativo';

    if (posicaoDisponivel <= 0) {
      const mensagem = `Você não possui ações de <strong>${tickerNome}</strong> nesta carteira para realizar a venda (Posição atual: 0).`;
      if (alertaMsg) alertaMsg.innerHTML = mensagem;
      const modalMsg = document.getElementById('modal-bypass-msg');
      if (modalMsg) {
        modalMsg.innerHTML = `Você não possui ações de <strong>${tickerNome}</strong> nesta carteira (Posição atual: 0).<br><br>Deseja <strong>seguir com a operação assim mesmo</strong>?`;
      }
      if (alertaCard) alertaCard.classList.remove('hidden');
      if (abrirPopupSeErro) abrirModalBypass();
      return false;
    }

    if (qtd > posicaoDisponivel) {
      const mensagem = `Você possui <strong>${posicaoDisponivel}</strong> ações de <strong>${tickerNome}</strong> nesta carteira, mas está tentando vender <strong>${qtd}</strong>.`;
      if (alertaMsg) alertaMsg.innerHTML = mensagem;
      const modalMsg = document.getElementById('modal-bypass-msg');
      if (modalMsg) {
        modalMsg.innerHTML = `Você possui apenas <strong>${posicaoDisponivel}</strong> ações de <strong>${tickerNome}</strong> em carteira e está tentando vender <strong>${qtd}</strong>.<br><br>Deseja <strong>seguir com a operação assim mesmo</strong>?`;
      }
      if (alertaCard) alertaCard.classList.remove('hidden');
      if (abrirPopupSeErro) abrirModalBypass();
      return false;
    }

    if (alertaCard) alertaCard.classList.add('hidden');
    return true;
  }

  quantidadeInput?.addEventListener('input', () => {
    if (origemUltimoCalculo === 'valor' && valorOperacaoInput?.value) {
      calcularPrecoUnitario();
    } else {
      calcularValorOperacao();
    }
    verificarPosseAtivo(false);
  });
  precoUnitarioInput?.addEventListener('input', calcularValorOperacao);
  precoUnitarioInput?.addEventListener('blur', () => {
    const val = parseValorInput(precoUnitarioInput.value);
    if (val > 0) {
      precoUnitarioInput.value = formatUnitaryPrice(val);
    }
  });

  taxasInput?.addEventListener('input', () => {
    if (origemUltimoCalculo === 'valor' && valorOperacaoInput?.value) {
      calcularPrecoUnitario();
    } else {
      calcularValorOperacao();
    }
  });
  taxasInput?.addEventListener('blur', () => {
    const val = parseValorInput(taxasInput.value);
    taxasInput.value = val.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  });

  valorOperacaoInput?.addEventListener('input', calcularPrecoUnitario);
  valorOperacaoInput?.addEventListener('blur', () => {
    const val = parseValorInput(valorOperacaoInput.value);
    if (val > 0) {
      valorOperacaoInput.value = val.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
  });

  // 3. Controle deslizante/slide da Carteira (Roxo para Renato, Verde para Vicente)
  const classesRoxo = ['bg-gradient-to-r', 'from-purple-700', 'via-purple-600', 'to-indigo-600', 'text-white', 'shadow-[0_2px_8px_rgba(126,34,206,0.28)]'];
  const classesVerde = ['bg-gradient-to-r', 'from-emerald-600', 'via-teal-600', 'to-teal-700', 'text-white', 'shadow-[0_2px_8px_rgba(13,148,136,0.28)]'];

  carteiraBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      carteiraBtns.forEach(b => {
        b.classList.remove(...classesRoxo, ...classesVerde);
        b.classList.add('text-slate-600');
        const dot = b.querySelector('.wallet-dot');
        if (dot) {
          const isR = b.dataset.walletColor === 'purple';
          dot.className = `wallet-dot w-2 h-2 rounded-full ${isR ? 'bg-purple-600' : 'bg-emerald-500'}`;
        }
      });
      const isRenato = btn.dataset.walletColor === 'purple';
      btn.classList.remove('text-slate-600');
      btn.classList.add(...(isRenato ? classesRoxo : classesVerde));
      const activeDot = btn.querySelector('.wallet-dot');
      if (activeDot) {
        activeDot.className = 'wallet-dot w-2 h-2 rounded-full bg-white';
      }

      if (carteiraInput) {
        carteiraInput.value = btn.dataset.walletId;
        consultarPosicaoEAtualizar();
      }
    });
  });

  // 4. Controle deslizante/slide do Tipo de Operação (Compra / Venda)
  tipoBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      tipoBtns.forEach(b => {
        b.classList.remove('bg-gradient-to-r', 'from-blue-700', 'to-blue-600', 'text-white', 'shadow-[0_2px_8px_rgba(29,78,216,0.25)]');
        b.classList.add('text-slate-600');
      });
      btn.classList.add('bg-gradient-to-r', 'from-blue-700', 'to-blue-600', 'text-white', 'shadow-[0_2px_8px_rgba(29,78,216,0.25)]');
      btn.classList.remove('text-slate-600');

      if (tipoInput) {
        tipoInput.value = btn.dataset.tipo;
        verificarPosseAtivo(false);
      }
    });
  });

  // 5. Consulta posição na carteira
  async function consultarPosicaoEAtualizar() {
    if (!ativoSelect || !carteiraInput) return;

    const ativoId = ativoSelect.value;
    const carteiraId = carteiraInput.value;
    if (!ativoId || !carteiraId) {
      posicaoDisponivel = null;
      if (badgePosicao) badgePosicao.classList.add('hidden');
      return;
    }

    try {
      const excludeParam = <?= $isEdit ? "'&exclude_id=" . (int)$transacao['id'] . "'" : "''" ?>;
      const url = `<?= BASE_URL ?>/api/position.php?ativo_id=${encodeURIComponent(ativoId)}&carteira_id=${encodeURIComponent(carteiraId)}${excludeParam}`;
      const res = await fetch(url);
      const data = await res.json();
      if (data.success) {
        posicaoDisponivel = parseInt(data.quantidade, 10);
        if (badgePosicao) {
          badgePosicao.textContent = `Em carteira: ${posicaoDisponivel}`;
          badgePosicao.classList.remove('hidden');
        }

        // Se for nova operação e não houver quantidade digitada e for venda, preenche com o total
        <?php if (!$isEdit): ?>
        if (tipoInput?.value === 'VENDA' && posicaoDisponivel > 0 && !quantidadeInput.value) {
          quantidadeInput.value = posicaoDisponivel;
          calcularValorOperacao();
        }
        <?php endif; ?>

        verificarPosseAtivo(false);
      }
    } catch (err) {
      // Falha silenciosa
    }
  }

  ativoSelect?.addEventListener('change', consultarPosicaoEAtualizar);

  // Consulta inicial de posição
  if (ativoSelect?.value && carteiraInput?.value) {
    consultarPosicaoEAtualizar();
  }

  // Interceptar envio do formulário para verificar posição se for Venda e sincronizar preço
  form?.addEventListener('submit', (e) => {
    sincronizarPrecoAntesDeEnviar();

    if (bypassConfirmado || bypassInput?.value === '1') {
      return;
    }

    const tipo = tipoInput?.value || 'COMPRA';
    if (tipo === 'VENDA') {
      const ok = verificarPosseAtivo(true);
      if (!ok) {
        e.preventDefault();
      }
    }
  });

  window.confirmarBypassESalvar = function() {
    bypassConfirmado = true;
    if (bypassInput) bypassInput.value = '1';
    fecharModalBypass();
    sincronizarPrecoAntesDeEnviar();
    form.submit();
  };
})();

function abrirModalBypass() {
  const modal = document.getElementById('modal-bypass-posicao');
  if (modal) modal.classList.remove('hidden');
}

function fecharModalBypass() {
  const modal = document.getElementById('modal-bypass-posicao');
  if (modal) modal.classList.add('hidden');
}

function abrirModalExclusaoForm() {
  const modal = document.getElementById('modal-exclusao-form');
  if (modal) modal.classList.remove('hidden');
}

function fecharModalExclusaoForm() {
  const modal = document.getElementById('modal-exclusao-form');
  if (modal) modal.classList.add('hidden');
}

document.getElementById('modal-bypass-posicao')?.addEventListener('click', (e) => {
  if (e.target.id === 'modal-bypass-posicao') fecharModalBypass();
});

document.getElementById('modal-exclusao-form')?.addEventListener('click', (e) => {
  if (e.target.id === 'modal-exclusao-form') fecharModalExclusaoForm();
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    fecharModalBypass();
    fecharModalExclusaoForm();
  }
});
</script>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>
