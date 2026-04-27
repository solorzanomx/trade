<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Win Rate Chart -->
      <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-bold mb-4">Win Rate Over Time</h3>
        <canvas id="winRateChart"></canvas>
      </div>

      <!-- P&L Chart -->
      <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-bold mb-4">Daily P&L</h3>
        <canvas id="pnlChart"></canvas>
      </div>

      <!-- Trade Distribution -->
      <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-bold mb-4">Wins vs Losses</h3>
        <canvas id="distributionChart"></canvas>
      </div>

      <!-- Monthly P&L -->
      <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-bold mb-4">Monthly Performance</h3>
        <canvas id="monthlyChart"></canvas>
      </div>
    </div>
  </div>
</template>

<script>
import { Chart as ChartJS, ArcElement, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend } from 'chart.js'

ChartJS.register(ArcElement, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend)

export default {
  props: {
    dailyMetrics: Array,
    stats: Object,
    monthlyMetrics: Array,
  },
  mounted() {
    this.initCharts()
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
      if (!ctx || this.dailyMetrics.length === 0) return

      const labels = this.dailyMetrics.map(m => new Date(m.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }))
      const data = this.dailyMetrics.map(m => parseFloat(m.win_rate) || 0)

      new ChartJS(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Win Rate %',
            data,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#3b82f6',
            pointRadius: 4,
          }],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: true },
          },
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              title: { display: true, text: 'Win Rate %' },
            },
          },
        },
      })
    },
    createPnLChart() {
      const ctx = document.getElementById('pnlChart')
      if (!ctx || this.dailyMetrics.length === 0) return

      const labels = this.dailyMetrics.map(m => new Date(m.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }))
      const data = this.dailyMetrics.map(m => parseFloat(m.daily_pnl) || 0)

      new ChartJS(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'Daily P&L $',
            data,
            backgroundColor: data.map(val => val >= 0 ? '#10b981' : '#ef4444'),
            borderRadius: 4,
          }],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
          },
          scales: {
            y: {
              title: { display: true, text: 'P&L ($)' },
            },
          },
        },
      })
    },
    createDistributionChart() {
      const ctx = document.getElementById('distributionChart')
      if (!ctx) return

      const wins = this.stats.wins || 0
      const losses = this.stats.losses || 0

      new ChartJS(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Wins', 'Losses'],
          datasets: [{
            data: [wins, losses],
            backgroundColor: ['#10b981', '#ef4444'],
            borderColor: ['#059669', '#dc2626'],
            borderWidth: 2,
          }],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'bottom' },
          },
        },
      })
    },
    createMonthlyChart() {
      const ctx = document.getElementById('monthlyChart')
      if (!ctx || this.monthlyMetrics.length === 0) return

      const labels = this.monthlyMetrics.map(m => m.month)
      const pnl = this.monthlyMetrics.map(m => parseFloat(m.pnl) || 0)

      new ChartJS(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Monthly P&L $',
            data: pnl,
            borderColor: '#8b5cf6',
            backgroundColor: 'rgba(139, 92, 246, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#8b5cf6',
            pointRadius: 5,
          }],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: true },
          },
          scales: {
            y: {
              title: { display: true, text: 'P&L ($)' },
            },
          },
        },
      })
    },
  },
}
</script>
