const http = require('http');

function fetch(url) {
  return new Promise((resolve, reject) => {
    http.get(url, res => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => resolve(data));
    }).on('error', reject);
  });
}

async function verify() {
  console.log('--- Verifying HTML Structure ---');

  // 1. Relatorios
  const htmlRel = await fetch('http://localhost/investtrack/relatorios.php');
  const checksRel = [
    'tab-content-desempenho',
    'tab-content-renda',
    'tab-content-irpf',
    'tabela-resultado-ativo',
    'tabela-relatorio-proventos',
    'grafico-renda-comparativa',
    'tabela-matriz-sazonalidade',
    'tabela-irpf-bens',
    'tabela-irpf-isentos',
    'tabela-irpf-exclusivos',
    'exportarTabelaCSV',
  ];
  checksRel.forEach(c => {
    console.log(`relatorios.php includes '${c}':`, htmlRel.includes(c));
  });

  // 2. Proventos
  const htmlProv = await fetch('http://localhost/investtrack/proventos.php');
  const checksProv = [
    'filtro-tipo-container',
    'filtro-periodo-container',
    'filtro-classe-container',
    'tabela-proventos',
    'data-tipo',
    'data-data',
    'data-classe',
    'exportarTabelaCSV',
  ];
  checksProv.forEach(c => {
    console.log(`proventos.php includes '${c}':`, htmlProv.includes(c));
  });

  // 3. Ativos
  const htmlAtiv = await fetch('http://localhost/investtrack/ativos.php');
  const checksAtiv = [
    'filtro-classe-container',
    'toggle-mostrar-zerados',
    'tabela-ativos',
    'data-classe',
    'exportarTabelaCSV',
  ];
  checksAtiv.forEach(c => {
    console.log(`ativos.php includes '${c}':`, htmlAtiv.includes(c));
  });

  // 4. Dashboard
  const htmlDash = await fetch('http://localhost/investtrack/dashboard.php');
  const checksDash = [
    'Retorno Global',
    'Yield on Cost',
    'Média 12m',
    'Maior alocação',
    'YoC %',
  ];
  checksDash.forEach(c => {
    console.log(`dashboard.php includes '${c}':`, htmlDash.includes(c));
  });

  console.log('--- Verification Done ---');
}

verify();
