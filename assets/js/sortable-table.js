/**
 * Ordenação client-side ao clicar no título das colunas das tabelas com a classe "js-sortable".
 * Detecta automaticamente números, moeda (R$), percentuais, datas (dd/mm/yyyy) e texto alfanumérico (tickers).
 */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('table.js-sortable').forEach(initSortableTable);
});

function initSortableTable(table) {
  const thead = table.querySelector('thead');
  const tbody = table.querySelector('tbody');
  if (!thead || !tbody) {
    return;
  }

  const headers = Array.from(thead.querySelectorAll('th'));
  headers.forEach((th, index) => {
    if (th.hasAttribute('data-no-sort')) {
      return;
    }

    th.classList.add('cursor-pointer', 'select-none', 'hover:text-zinc-900');
    const defaultSort = th.getAttribute('data-default-sort');
    const icon = document.createElement('i');
    if (defaultSort === 'desc') {
      th.dataset.sortDir = 'desc';
      icon.className = 'ph ph-caret-down text-[10px] ml-1 opacity-80 text-zinc-900 inline-block align-middle';
    } else if (defaultSort === 'asc') {
      th.dataset.sortDir = 'asc';
      icon.className = 'ph ph-caret-up text-[10px] ml-1 opacity-80 text-zinc-900 inline-block align-middle';
    } else {
      icon.className = 'ph ph-caret-up-down text-[10px] ml-1 opacity-40 inline-block align-middle';
    }
    th.appendChild(icon);

    th.addEventListener('click', () => sortTableByColumn(table, index, th, headers));
  });
}

function sortTableByColumn(table, columnIndex, th, headers) {
  const tbody = table.querySelector('tbody');
  const rows = Array.from(tbody.querySelectorAll('tr')).filter((row) => row.children.length === headers.length);
  if (rows.length === 0) {
    return;
  }

  let direction;
  if (th.dataset.sortDir === 'desc') {
    direction = 'asc';
  } else if (th.dataset.sortDir === 'asc') {
    direction = 'desc';
  } else {
    direction = th.getAttribute('data-default-sort') || 'asc';
  }

  headers.forEach((h) => {
    delete h.dataset.sortDir;
    const icon = h.querySelector('i');
    if (icon) {
      icon.className = 'ph ph-caret-up-down text-[10px] ml-1 opacity-40 inline-block align-middle';
    }
  });

  th.dataset.sortDir = direction;
  const activeIcon = th.querySelector('i');
  if (activeIcon) {
    activeIcon.className = `ph ${direction === 'asc' ? 'ph-caret-up' : 'ph-caret-down'} text-[10px] ml-1 opacity-80 text-zinc-900 inline-block align-middle`;
  }

  const columnType = th.getAttribute('data-sort-type');
  const parsedRows = rows.map((row) => {
    const cell = row.children[columnIndex];
    const explicitSortValue = cell?.getAttribute('data-sort-value');
    return {
      row,
      value: explicitSortValue !== null && explicitSortValue !== undefined ? explicitSortValue : parseCellValue(cell ? cell.textContent : '', columnType),
    };
  });

  parsedRows.sort((a, b) => {
    let result;
    if (typeof a.value === 'number' && typeof b.value === 'number') {
      result = a.value - b.value;
    } else {
      result = String(a.value).localeCompare(String(b.value), 'pt-BR', { numeric: true, sensitivity: 'base' });
    }
    return direction === 'asc' ? result : -result;
  });

  parsedRows.forEach(({ row }) => tbody.appendChild(row));
}

function parseCellValue(text, columnType) {
  text = text.trim();

  if (columnType === 'text') {
    return text.toLowerCase();
  }

  // 1. Data dd/mm/yyyy
  const dateMatch = text.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
  if (dateMatch) {
    return `${dateMatch[3]}${dateMatch[2]}${dateMatch[1]}`;
  }

  // 2. Remove prefixos de moeda e sufixo de percentual
  const cleanCurrency = text.replace(/^(?:R\$|US\$|\$|€)\s*/i, '').replace(/%$/, '').trim();

  // 3. Se contiver letras alfabéticas (como tickers: PETR4, BBAS3, VALE3), deve ser tratado como TEXTO
  if (/[a-zA-Z\u00C0-\u017F]/.test(cleanCurrency)) {
    return text.toLowerCase();
  }

  // 4. Somente se for estritamente número/moeda formatado (ex: 1.250,50 ou -100 ou 42)
  if (/^[-+]?\s*[\d.,]+$/.test(cleanCurrency)) {
    const normalized = cleanCurrency.includes(',')
      ? cleanCurrency.replace(/\./g, '').replace(',', '.')
      : cleanCurrency;
    const parsed = parseFloat(normalized);
    if (!isNaN(parsed)) {
      return parsed;
    }
  }

  return text.toLowerCase();
}
