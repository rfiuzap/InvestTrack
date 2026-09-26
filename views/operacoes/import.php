<?php require BASE_PATH . '/views/layout/header.php'; ?>

<?php
  $carteiraAtual = (int)($carteiraSelecionada ?? ($wallets[0]['id'] ?? 1));
?>

<div class="max-w-3xl mx-auto space-y-6">
  <!-- Top Bar / Voltar -->
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="<?= BASE_URL ?>/operacoes.php" class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 hover:text-slate-900 hover:border-slate-300 shadow-sm transition-all">
        <i class="ph ph-arrow-left text-lg"></i>
      </a>
      <div>
        <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Importação de Operações em Lote</h2>
        <p class="text-xs text-slate-500 font-medium">Importe transações de corretoras, CEI/B3 ou planilhas Excel via arquivo CSV.</p>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/operacoes.php?action=template_csv"
      class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50/80 px-3.5 py-2 text-xs font-bold text-blue-700 hover:bg-blue-100 hover:border-blue-300 transition-all shadow-sm">
      <i class="ph ph-download-simple text-sm"></i> Baixar Planilha Modelo (.csv)
    </a>
  </div>

  <!-- Card Principal do Formulário -->
  <div class="bg-white border border-slate-200/90 rounded-2xl shadow-sm p-6 md:p-8">
    <form method="POST" enctype="multipart/form-data" id="form-importar-csv">
      <?= csrf_field() ?>

      <div class="space-y-6">
        <!-- Carteira Destino Padrão -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
            Carteira de Destino <span class="text-slate-400 font-normal lowercase">(usada se a planilha não indicar a coluna Carteira)</span>
          </label>
          <input type="hidden" name="carteira_id" id="carteira_id_input" value="<?= $carteiraAtual ?>">
          <div class="flex items-center rounded-xl border border-slate-200 bg-slate-100/80 p-1 select-none" id="carteira-slide-container">
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
                class="carteira-slide-btn flex-1 text-center py-2 px-3 rounded-lg text-xs font-bold transition-all duration-150 <?= $isSelected ? $corAtiva : 'text-slate-600 hover:text-slate-900' ?>">
                <span class="inline-flex items-center gap-1.5 justify-center">
                  <span class="wallet-dot w-2 h-2 rounded-full <?= $isSelected ? 'bg-white' : ($isRenato ? 'bg-purple-600' : 'bg-emerald-500') ?>"></span>
                  <?= e($w['nome']) ?>
                </span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Dropzone de Arquivo CSV -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Arquivo CSV</label>
          <div id="dropzone"
            class="group relative flex flex-col items-center justify-center p-8 border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-2xl bg-slate-50/50 hover:bg-blue-50/30 transition-all cursor-pointer text-center">
            
            <input type="file" name="arquivo_csv" id="arquivo_csv" accept=".csv, text/csv, application/vnd.ms-excel" required
              class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

            <div class="w-14 h-14 rounded-2xl bg-blue-100/80 flex items-center justify-center text-blue-600 mb-3 group-hover:scale-110 transition-transform">
              <i class="ph ph-file-csv text-3xl" id="upload-icon"></i>
            </div>

            <p class="text-sm font-bold text-slate-800" id="upload-title">
              Clique para selecionar ou arraste o arquivo CSV aqui
            </p>
            <p class="text-xs text-slate-500 mt-1" id="upload-subtitle">
              Formatos aceitos: arquivos CSV separados por ponto e vírgula (;) ou vírgula (,)
            </p>

            <!-- Preview do arquivo selecionado -->
            <div id="file-info" class="hidden mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
              <i class="ph ph-check-circle text-emerald-600"></i>
              <span id="file-name">arquivo.csv</span>
              <span id="file-size" class="text-emerald-600 text-[11px]">(0 KB)</span>
            </div>
          </div>
        </div>

        <!-- Instruções e Regras de Compatibilidade -->
        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 text-xs text-slate-600 space-y-2.5">
          <div class="font-bold text-slate-800 flex items-center gap-1.5">
            <i class="ph ph-info text-blue-600 text-sm"></i>
            Colunas aceitas e reconhecimento inteligente:
          </div>
          <ul class="list-disc list-inside space-y-1 text-slate-600 pl-1 leading-relaxed">
            <li><strong>Colunas obrigatórias:</strong> <code class="bg-white px-1.5 py-0.5 rounded border text-slate-800">Ticker</code> (ou Código), <code class="bg-white px-1.5 py-0.5 rounded border text-slate-800">Tipo</code> (Compra ou Venda), <code class="bg-white px-1.5 py-0.5 rounded border text-slate-800">Quantidade</code> e <code class="bg-white px-1.5 py-0.5 rounded border text-slate-800">Preço</code>.</li>
            <li><strong>Colunas opcionais:</strong> <code class="bg-white px-1.5 py-0.5 rounded border text-slate-800">Data</code> (DD/MM/AAAA ou AAAA-MM-DD), <code class="bg-white px-1.5 py-0.5 rounded border text-slate-800">Taxas</code>, <code class="bg-white px-1.5 py-0.5 rounded border text-slate-800">Carteira</code> e <code class="bg-white px-1.5 py-0.5 rounded border text-slate-800">Observação</code>.</li>
            <li><strong>Cadastro automático de ativos:</strong> Tickers não encontrados serão cadastrados automaticamente com a classe detectada (Ação, FII final 11, BDR final 34/35 ou ETF final 39).</li>
            <li><strong>Formatação numérica flexível:</strong> Aceita padrões brasileiros (<code class="bg-white px-1 py-0.5 rounded border text-slate-700">R$ 34,50</code>) ou internacionais (<code class="bg-white px-1 py-0.5 rounded border text-slate-700">34.50</code>).</li>
          </ul>
        </div>

        <!-- Ações do Formulário -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
          <a href="<?= BASE_URL ?>/operacoes.php"
            class="btn-secondary py-2.5 px-4 text-xs font-bold shadow-sm">
            Cancelar
          </a>
          <button type="submit" id="btn-submit-import"
            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-600 py-2.5 px-5 text-xs font-bold uppercase tracking-wider text-white shadow-[0_4px_14px_rgba(29,78,216,0.3)] hover:shadow-[0_8px_24px_rgba(29,78,216,0.45)] hover:-translate-y-0.5 active:translate-y-0 transition-all duration-150">
            <i class="ph ph-upload-simple text-base"></i> Processar Importação
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Controle de seleção da Carteira Padrão
  const carteiraInput = document.getElementById('carteira_id_input');
  const slideBtns = document.querySelectorAll('.carteira-slide-btn');

  const classesRoxo = ['bg-gradient-to-r', 'from-purple-700', 'via-purple-600', 'to-indigo-600', 'text-white', 'shadow-[0_2px_8px_rgba(126,34,206,0.28)]'];
  const classesVerde = ['bg-gradient-to-r', 'from-emerald-600', 'via-teal-600', 'to-teal-700', 'text-white', 'shadow-[0_2px_8px_rgba(13,148,136,0.28)]'];

  slideBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      slideBtns.forEach(b => {
        b.className = 'carteira-slide-btn flex-1 text-center py-2 px-3 rounded-lg text-xs font-bold transition-all duration-150 text-slate-600 hover:text-slate-900';
        const dot = b.querySelector('.wallet-dot');
        if (dot) {
          const isR = b.dataset.walletColor === 'purple';
          dot.className = `wallet-dot w-2 h-2 rounded-full ${isR ? 'bg-purple-600' : 'bg-emerald-500'}`;
        }
      });
      const isRenato = btn.dataset.walletColor === 'purple';
      const activeClasses = isRenato ? classesRoxo : classesVerde;
      btn.className = `carteira-slide-btn flex-1 text-center py-2 px-3 rounded-lg text-xs font-bold transition-all duration-150 ${activeClasses.join(' ')}`;
      const activeDot = btn.querySelector('.wallet-dot');
      if (activeDot) {
        activeDot.className = 'wallet-dot w-2 h-2 rounded-full bg-white';
      }
      carteiraInput.value = btn.getAttribute('data-wallet-id');
    });
  });

  // Atualização visual ao selecionar arquivo
  const fileInput = document.getElementById('arquivo_csv');
  const fileInfo = document.getElementById('file-info');
  const fileName = document.getElementById('file-name');
  const fileSize = document.getElementById('file-size');
  const uploadTitle = document.getElementById('upload-title');
  const dropzone = document.getElementById('dropzone');
  const uploadIcon = document.getElementById('upload-icon');

  function updateFileInfo(file) {
    if (file) {
      fileName.textContent = file.name;
      const kb = (file.size / 1024).toFixed(1);
      fileSize.textContent = `(${kb} KB)`;
      fileInfo.classList.remove('hidden');
      uploadTitle.textContent = 'Arquivo pronto para importação';
      uploadIcon.className = 'ph ph-check-circle text-3xl text-emerald-600';
      dropzone.classList.add('border-emerald-400', 'bg-emerald-50/20');
      dropzone.classList.remove('border-slate-300');
    }
  }

  fileInput.addEventListener('change', (e) => {
    if (e.target.files && e.target.files[0]) {
      updateFileInfo(e.target.files[0]);
    }
  });

  // Drag and drop feedback
  ['dragenter', 'dragover'].forEach(eventName => {
    dropzone.addEventListener(eventName, (e) => {
      e.preventDefault();
      dropzone.classList.add('border-blue-500', 'bg-blue-50/40');
    });
  });

  ['dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, (e) => {
      e.preventDefault();
      dropzone.classList.remove('border-blue-500', 'bg-blue-50/40');
    });
  });

  // Loading state no submit
  const form = document.getElementById('form-importar-csv');
  const submitBtn = document.getElementById('btn-submit-import');

  form.addEventListener('submit', () => {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ph ph-spinner animate-spin text-base"></i> Processando...';
    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
  });
});
</script>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>
