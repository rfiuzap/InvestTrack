/**
 * Utilitário de exportação de tabelas HTML para CSV compatível com Excel brasileiro.
 * Usa ponto-e-vírgula (;) como separador e inclui BOM UTF-8 (\uFEFF) para acentuação correta.
 */
function exportarTabelaCSV(tableId, filename) {
  const table = document.getElementById(tableId);
  if (!table) return;

  const rows = [];
  const headers = [];
  const skipIndices = new Set();

  // Cabeçalhos (thead)
  const headerCells = table.querySelectorAll('thead th');
  headerCells.forEach((th, index) => {
    if (th.hasAttribute('data-no-sort') || th.hasAttribute('data-no-export') || th.textContent.trim() === 'Ações' || th.textContent.trim() === '') {
      skipIndices.add(index);
      return;
    }
    const cleanText = th.innerText.replace(/[\r\n]+/g, ' ').replace(/"/g, '""').trim();
    headers.push(`"${cleanText}"`);
  });
  rows.push(headers.join(';'));

  // Linhas de dados visíveis (tbody tr:not(.hidden))
  const bodyRows = table.querySelectorAll('tbody tr:not(.hidden)');
  bodyRows.forEach((tr) => {
    // Ignora linhas de "nenhum registro encontrado"
    if (tr.id === 'sem-operacoes' || tr.id === 'sem-resultados-filtro') return;

    const rowData = [];
    const cells = tr.querySelectorAll('td');
    if (cells.length === 0) return;

    cells.forEach((td, index) => {
      if (skipIndices.has(index)) return;
      const cleanText = td.innerText.replace(/[\r\n]+/g, ' ').replace(/"/g, '""').trim();
      rowData.push(`"${cleanText}"`);
    });

    if (rowData.length > 0) {
      rows.push(rowData.join(';'));
    }
  });

  const csvContent = '\uFEFF' + rows.join('\r\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = (filename || 'exportacao') + '_' + new Date().toISOString().slice(0, 10) + '.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}
