import { createApp } from 'vue'
import MetricsChart from './components/MetricsChart.vue'
import TradeForm from './components/TradeForm.vue'

const app = createApp({})

app.component('metrics-chart', MetricsChart)
app.component('trade-form', TradeForm)

if (document.getElementById('app')) {
    app.mount('#app')
}
