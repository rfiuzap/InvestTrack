/**
 * Filtro por ticker: digitar no campo com [data-filter-rows] esconde as linhas
 * da tabela alvo cujo atributo data-ticker não corresponder ao termo digitado.
 */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-filter-rows]').forEach((input) => {
    const table = document.querySelector(input.dataset.filterRows);
    if (!table) {
      return;
    }

    input.addEventListener('input', () => {
      const term = input.value.trim().toLowerCase();
      table.querySelectorAll('tbody tr[data-ticker]').forEach((row) => {
        const match = row.dataset.ticker.toLowerCase().includes(term);
        row.classList.toggle('hidden', !match);
      });
    });
  });
});
