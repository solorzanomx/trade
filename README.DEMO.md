# 🎬 Demo Data Setup

## Quick Demo Login

Para probar la aplicación con datos pre-cargados:

### 1. Seed Demo Data
```bash
php artisan db:seed
```

### 2. Demo Credentials
```
Email: demo@trading.local
Password: password123
```

### 3. What's Included

La demo incluye:
- ✅ 5 trades cerrados (AAPL, MSFT, TSLA, QQQ, SPY)
- ✅ P&L automático calculado
- ✅ 5 noticias diarias AI-generadas
- ✅ Métricas de performance
- ✅ Gráficos interactivos

### 4. Demo Dashboard

Verás:
- P&L Total: ~$725.50
- Win Rate: 80% (4 ganancias, 1 pérdida)
- Current Streak: 1 Win
- Recent Trades: AAPL, MSFT, TSLA, QQQ, SPY

### 5. Explore Features

**Trades Page:**
- Filtrar por símbolo
- Ver detalles de cada trade
- Editar trades abiertos

**Metrics Page:**
- 4 gráficos interactivos
- Análisis histórico
- Breakdown mensual

**News Page:**
- Noticias por activo
- Filtrar por fecha
- Ver sentimiento

---

## Reset Demo Data

Para limpiar y resetear:

```bash
php artisan migrate:refresh --seed
```

---

## Datos incluidos en Demo

### Trades
| Symbol | Entry | Exit | Quantity | P&L | Status |
|--------|-------|------|----------|-----|--------|
| AAPL | 150.25 | 152.50 | 100 | +$225 | ✓ |
| MSFT | 380.50 | 378.20 | 50 | -$115 | ✓ |
| TSLA | 240.00 | 248.50 | 25 | +$212.50 | ✓ |
| QQQ | 420.75 | 425.30 | 20 | +$91 | ✓ |
| SPY | 510.20 | 508.50 | 15 | -$25.50 | ✓ |

### Assets Monitored
- AAPL (Apple)
- MSFT (Microsoft)
- TSLA (Tesla)
- QQQ (Nasdaq)
- SPY (S&P 500)

---

## Vue.js Components (New!)

La demo incluye componentes Vue interactivos:

### MetricsChart
- Win Rate Over Time (línea)
- Daily P&L (barras)
- Wins vs Losses (doughnut)
- Monthly Performance (línea)

### TradeForm
- Real-time Risk/Reward calculation
- Form validation
- Instant feedback

---

## Next Steps

1. **Explore Trades** → Ver detalles y editar
2. **Check Metrics** → Ver gráficos interactivos
3. **Configure APIs** → Para noticias reales
4. **Create Your Trades** → Empezar a registrar

---

Disfruta explorando la plataforma! 🚀
