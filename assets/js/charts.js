document.addEventListener('DOMContentLoaded', () => {
  const gridColor = '#f1f5f9';
  const textColor = '#64748b';

  Chart.defaults.font.family = '"Plus Jakarta Sans", system-ui, -apple-system, sans-serif';
  Chart.defaults.font.weight = '500';
  Chart.defaults.color = textColor;

  const alocacao = window.__ALOCACAO__ || { labels: [], valores: [] };
  const evolucao = window.__EVOLUCAO__ || { labels: [], investido_acumulado: [], proventos_acumulado: [] };

  const paleta = ['#1d4ed8', '#0284c7', '#0d9488', '#10b981', '#6366f1', '#0ea5e9', '#f59e0b', '#8b5cf6'];

  const ctxAlocacao = document.getElementById('chart-alocacao');
  if (ctxAlocacao) {
    new Chart(ctxAlocacao, {
      type: 'bar',
      data: {
        labels: alocacao.labels,
        datasets: [{
          label: 'Valor atual',
          data: alocacao.valores,
          backgroundColor: paleta,
          borderWidth: 0,
          borderRadius: 6,
          barThickness: 20,
        }],
      },
      options: {
        maintainAspectRatio: false,
        layout: {
          padding: {
            top: 20,
          },
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#0f172a',
            padding: 12,
            cornerRadius: 8,
            titleFont: { weight: '700' },
            callbacks: {
              label: (ctx) => {
                const val = Number(ctx.raw) || 0;
                const total = ctx.dataset.data.reduce((acc, v) => acc + (Number(v) || 0), 0);
                const pct = total > 0 ? ((val / total) * 100).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) : '0';
                return `${ctx.label}: R$ ${val.toLocaleString('pt-BR', { minimumFractionDigits: 2 })} (${pct}%)`;
              },
            },
          },
        },
        scales: {
          x: {
            beginAtZero: true,
            grid: { display: false },
            ticks: { font: { weight: '600' } }
          },
          y: {
            grace: '10%',
            grid: { color: gridColor },
            ticks: {
              callback: (value) => `R$ ${Number(value).toLocaleString('pt-BR')}`,
            },
          },
        },
      },
      plugins: [{
        id: 'barPercentageLabels',
        afterDatasetsDraw(chart) {
          const { ctx, data } = chart;
          const dataset = data.datasets[0];
          if (!dataset || !dataset.data || dataset.data.length === 0) return;

          const meta = chart.getDatasetMeta(0);
          if (!meta || !meta.data) return;

          const total = dataset.data.reduce((acc, val) => acc + (Number(val) || 0), 0);
          if (total <= 0) return;

          ctx.save();
          ctx.font = '700 11px "Plus Jakarta Sans", system-ui, -apple-system, sans-serif';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'bottom';
          ctx.fillStyle = '#334155'; // slate-700

          meta.data.forEach((bar, index) => {
            const val = Number(dataset.data[index]) || 0;
            if (val <= 0) return;
            const pct = ((val / total) * 100).toLocaleString('pt-BR', {
              minimumFractionDigits: 1,
              maximumFractionDigits: 1,
            }) + '%';

            ctx.fillText(pct, bar.x, bar.y - 5);
          });

          ctx.restore();
        },
      }],
    });
  }

  const ctxEvolucao = document.getElementById('chart-evolucao');
  if (ctxEvolucao) {
    new Chart(ctxEvolucao, {
      type: 'line',
      data: {
        labels: evolucao.labels,
        datasets: [
          {
            label: 'Total Investido',
            data: evolucao.investido_acumulado,
            borderColor: '#1d4ed8',
            backgroundColor: 'rgba(29, 78, 216, 0.08)',
            tension: 0.35,
            fill: true,
            pointRadius: 3,
            pointBackgroundColor: '#1d4ed8',
            borderWidth: 2.5,
          },
          {
            label: 'Proventos Acumulados',
            data: evolucao.proventos_acumulado,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.08)',
            tension: 0.35,
            fill: true,
            pointRadius: 3,
            pointBackgroundColor: '#10b981',
            borderWidth: 2.5,
          },
        ],
      },
      options: {
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, font: { weight: '600' } } },
          tooltip: {
            backgroundColor: '#0f172a',
            padding: 12,
            cornerRadius: 8,
            titleFont: { weight: '700' },
            callbacks: {
              label: (ctx) => ` ${ctx.dataset.label}: R$ ${Number(ctx.raw || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`,
            },
          },
        },
        scales: {
          x: { grid: { color: gridColor } },
          y: {
            grid: { color: gridColor },
            ticks: {
              callback: (value) => `R$ ${Number(value).toLocaleString('pt-BR')}`,
            },
          },
        },
      },
    });
  }
});
