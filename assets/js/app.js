document.addEventListener('DOMContentLoaded', () => {
  // 1. Data atual no cabeçalho
  const dateEl = document.getElementById('current-date');
  if (dateEl) {
    dateEl.textContent = new Date().toLocaleDateString('pt-BR', {
      day: '2-digit', month: 'long', year: 'numeric',
    });
  }

  // 2. Atualização de Cotações com feedback visual
  const btnRefresh = document.getElementById('btn-atualizar-cotacoes');
  if (btnRefresh) {
    btnRefresh.addEventListener('click', async () => {
      btnRefresh.disabled = true;
      const original = btnRefresh.innerHTML;
      btnRefresh.innerHTML = '<i class="ph ph-spinner animate-spin text-sm"></i> Atualizando...';

      try {
        const res = await fetch('api/quotes.php?action=refresh_all');
        const data = await res.json();
        if (data.success) {
          window.location.reload();
        } else {
          alert('Não foi possível atualizar as cotações. Verifique sua conexão com a internet.');
        }
      } catch (err) {
        alert('Erro ao conectar à API de cotações.');
      } finally {
        btnRefresh.disabled = false;
        btnRefresh.innerHTML = original;
      }
    });
  }

  // 3. Atalho de teclado global '/' para focar no campo de busca
  document.addEventListener('keydown', (e) => {
    if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
      const searchInput = document.getElementById('filtro-ticker-input')
        || document.getElementById('filtro-ticker-ativos');
      if (searchInput) {
        e.preventDefault();
        searchInput.focus();
        searchInput.select?.();
      }
    }
  });

  // 4. Auto-dismiss suave para alertas flash após 4 segundos
  document.querySelectorAll('.flash-alert').forEach((el) => {
    setTimeout(() => {
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 300);
    }, 4000);
  });
});
