<template>
  <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
    <div style="background:#131722; border:1px solid #2a2e39; border-radius:8px; padding:20px;">
      <div style="font-size:12px; color:#787b86; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">% Efectividad por Día</div>
      <canvas id="winRateChart" height="160"></canvas>
    </div>
    <div style="background:#131722; border:1px solid #2a2e39; border-radius:8px; padding:20px;">
      <div style="font-size:12px; color:#787b86; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">G/P Diario</div>
      <canvas id="pnlChart" height="160"></canvas>
    </div>
    <div style="background:#131722; border:1px solid #2a2e39; border-radius:8px; padding:20px;">
      <div style="font-size:12px; color:#787b86; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">Ganadoras vs Perdedoras</div>
      <div style="display:flex; justify-content:center;">
        <canvas id="distributionChart" height="160" width="160"></canvas>
      </div>
    </div>
    <div style="background:#131722; border:1px solid #2a2e39; border-radius:8px; padding:20px;">
      <div style="font-size:12px; color:#787b86; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">G/P Mensual</div>
      <canvas id="monthlyChart" height="160"></canvas>
    </div>
  </div>
</template>

<script>
import { Chart as ChartJS, ArcElement, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend } from 'chart.js'

ChartJS.register(ArcElement, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend)

const DARK = {
  grid: 'rgba(42,46,57,0.8)',
  text: '#787b86',
  green: '#26a69a',
  greenBg: 'rgba(38,166,154,0.15)',
  red: '#ef5350',
  redBg: 'rgba(239,83,80,0.15)',
  blue: '#5b8af5',
  blueBg: 'rgba(91,138,245,0.15)',
}

const baseScales = {
  x: { grid: { color: DARK.grid }, ticks: { color: DARK.text, font: { size: 11 } } },
  y: { grid: { color: DARK.grid }, ticks: { color: DARK.text, font: { size: 11 } } },
}

export default {
  props: {
    dailyMetrics: Array,
    stats: Object,
    monthlyMetrics: Array,
  },
  mounted() {
    this.$nextTick(() => this.initCharts())
  },
  methods: {
    initCharts() {
      this.createWinRateChart()
      this.createPnLChart()
      this.createDistributionChart()
      this.createMonthlyChart()
    },
    createWinRateChart() {
      const ctx = document.getElementById('winRateChart')
      if (!ctx || !this.dailyMetrics.length) return
      const labels = this.dailyMetrics.map(m => {
        const d = new Date(m.date)
        return (d.getMonth()+1) + '/' + d.getDate()
      })
      new ChartJS(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [{ label: '% Efectividad', data: this.dailyMetrics.map(m => parseFloat(m.win_rate)||0),
            borderColor: DARK.blue, backgroundColor: DARK.blueBg, borderWidth: 2,
            fill: true, tension: 0.3, pointBackgroundColor: DARK.blue, pointRadius: 4 }],
        },
        options: { responsive: true, plugins: { legend: { display: false } },
          scales: { ...baseScales, y: { ...baseScales.y, min: 0, max: 100 } } },
      })
    },
    createPnLChart() {
      const ctx = document.getElementById('pnlChart')
      if (!ctx || !this.dailyMetrics.length) return
      const labels = this.dailyMetrics.map(m => {
        const d = new Date(m.date)
        return (d.getMonth()+1) + '/' + d.getDate()
      })
      const data = this.dailyMetrics.map(m => parseFloat(m.daily_pnl)||0)
      new ChartJS(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [{ label: 'G/P $', data,
            backgroundColor: data.map(v => v >= 0 ? DARK.greenBg : DARK.redBg),
            borderColor: data.map(v => v >= 0 ? DARK.green : DARK.red),
            borderWidth: 1, borderRadius: 4 }],
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: baseScales },
      })
    },
    createDistributionChart() {
      const ctx = document.getElementById('distributionChart')
      if (!ctx) return
      new ChartJS(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Ganadoras', 'Perdedoras'],
          datasets: [{ data: [this.stats.wins||0, this.stats.losses||0],
            backgroundColor: [DARK.greenBg, DARK.redBg],
            borderColor: [DARK.green, DARK.red], borderWidth: 2 }],
        },
        options: { responsive: false, cutout: '65%',
          plugins: { legend: { position: 'bottom', labels: { color: DARK.text, font: { size: 12 }, padding: 16 } } } },
      })
    },
    createMonthlyChart() {
      const ctx = document.getElementById('monthlyChart')
      if (!ctx || !this.monthlyMetrics.length) return
      const pnl = this.monthlyMetrics.map(m => parseFloat(m.pnl)||0)
      new ChartJS(ctx, {
        type: 'bar',
        data: {
          labels: this.monthlyMetrics.map(m => m.month),
          datasets: [{ label: 'G/P $', data: pnl,
            backgroundColor: pnl.map(v => v >= 0 ? DARK.greenBg : DARK.redBg),
            borderColor: pnl.map(v => v >= 0 ? DARK.green : DARK.red),
            borderWidth: 1, borderRadius: 4 }],
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: baseScales },
      })
    },
  },
}
</script>
