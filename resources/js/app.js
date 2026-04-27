import { createApp } from 'vue'
import MetricsChart from './components/MetricsChart.vue'
import TradeForm from './components/TradeForm.vue'

const app = createApp({})

app.component('MetricsChart', MetricsChart)
app.component('TradeForm', TradeForm)

app.mount('#app')
