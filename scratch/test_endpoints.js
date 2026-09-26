const http = require('http');

const urls = [
  'http://localhost/investtrack/dashboard.php',
  'http://localhost/investtrack/operacoes.php',
  'http://localhost/investtrack/proventos.php',
  'http://localhost/investtrack/ativos.php',
  'http://localhost/investtrack/relatorios.php',
  'http://localhost/investtrack/relatorios.php?tab=renda',
  'http://localhost/investtrack/relatorios.php?tab=irpf',
];

async function checkUrl(url) {
  return new Promise((resolve) => {
    http.get(url, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        const hasPhpError = data.includes('Fatal error') || data.includes('Parse error') || data.includes('Warning:');
        console.log(`[${res.statusCode}] ${url} - Error: ${hasPhpError ? 'YES' : 'NONE'} - Length: ${data.length}`);
        if (hasPhpError) {
          console.error(data.slice(0, 500));
        }
        resolve(!hasPhpError && res.statusCode === 200);
      });
    }).on('error', (err) => {
      console.error(`FAILED: ${url}`, err.message);
      resolve(false);
    });
  });
}

async function run() {
  console.log('Testing endpoints...');
  let allOk = true;
  for (const url of urls) {
    const ok = await checkUrl(url);
    if (!ok) allOk = false;
  }
  console.log('Finished. All OK:', allOk);
  process.exit(allOk ? 0 : 1);
}

run();
