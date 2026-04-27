<template>
  <div class="bg-white p-6 rounded shadow">
    <form @submit.prevent="submitForm" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-2">Symbol</label>
          <input v-model="form.symbol" type="text" placeholder="AAPL" class="w-full px-3 py-2 border rounded" required>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Trade Type</label>
          <select v-model="form.trade_type" class="w-full px-3 py-2 border rounded">
            <option value="stock">Stock</option>
            <option value="call">Call Option</option>
            <option value="put">Put Option</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Position</label>
          <select v-model="form.position_direction" class="w-full px-3 py-2 border rounded">
            <option value="long">Long</option>
            <option value="short">Short</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Quantity</label>
          <input v-model.number="form.quantity" type="number" placeholder="100" class="w-full px-3 py-2 border rounded">
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Entry Price</label>
          <input v-model.number="form.entry_price" type="number" step="0.01" @input="calculateRiskReward" class="w-full px-3 py-2 border rounded">
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Entry Date</label>
          <input v-model="form.entry_date" type="date" class="w-full px-3 py-2 border rounded">
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Capital Used</label>
          <input v-model.number="form.capital_used" type="number" step="0.01" class="w-full px-3 py-2 border rounded">
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Stop Loss</label>
          <input v-model.number="form.stop_loss" type="number" step="0.01" @input="calculateRiskReward" class="w-full px-3 py-2 border rounded">
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Take Profit</label>
          <input v-model.number="form.take_profit" type="number" step="0.01" @input="calculateRiskReward" class="w-full px-3 py-2 border rounded">
        </div>
      </div>

      <!-- Risk/Reward Calculation -->
      <div v-if="riskRewardRatio" class="p-4 bg-blue-50 border border-blue-200 rounded">
        <div class="flex justify-between">
          <span class="font-semibold">Risk/Reward Ratio:</span>
          <span class="text-lg font-bold text-blue-600">1:{{ riskRewardRatio.toFixed(2) }}</span>
        </div>
        <div class="text-sm text-gray-600 mt-2">
          Risk: ${{ riskAmount.toFixed(2) }} | Reward: ${{ rewardAmount.toFixed(2) }}
        </div>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-2">Entry Reason</label>
        <textarea v-model="form.entry_reason" placeholder="Why did you enter?" class="w-full px-3 py-2 border rounded h-20"></textarea>
      </div>

      <div class="flex gap-4">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-semibold">
          {{ isLoading ? 'Saving...' : 'Create Trade' }}
        </button>
        <button type="button" @click="resetForm" class="border px-6 py-2 rounded hover:bg-gray-50">
          Reset
        </button>
      </div>

      <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded text-red-700">
        {{ error }}
      </div>

      <div v-if="success" class="p-4 bg-green-50 border border-green-200 rounded text-green-700">
        Trade created successfully!
      </div>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      form: {
        symbol: '',
        trade_type: 'stock',
        position_direction: 'long',
        entry_price: null,
        entry_date: new Date().toISOString().split('T')[0],
        entry_reason: '',
        quantity: null,
        capital_used: null,
        stop_loss: null,
        take_profit: null,
      },
      riskRewardRatio: null,
      riskAmount: 0,
      rewardAmount: 0,
      isLoading: false,
      error: null,
      success: false,
    }
  },
  methods: {
    calculateRiskReward() {
      if (!this.form.entry_price || !this.form.stop_loss || !this.form.take_profit) {
        this.riskRewardRatio = null
        return
      }

      this.riskAmount = Math.abs(this.form.entry_price - this.form.stop_loss)
      this.rewardAmount = Math.abs(this.form.take_profit - this.form.entry_price)

      if (this.riskAmount > 0) {
        this.riskRewardRatio = this.rewardAmount / this.riskAmount
      }
    },
    async submitForm() {
      this.isLoading = true
      this.error = null
      this.success = false

      try {
        const response = await fetch('/trades', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify(this.form),
        })

        if (!response.ok) {
          throw new Error('Failed to create trade')
        }

        this.success = true
        this.resetForm()

        setTimeout(() => {
          window.location.reload()
        }, 1500)
      } catch (err) {
        this.error = err.message
      } finally {
        this.isLoading = false
      }
    },
    resetForm() {
      this.form = {
        symbol: '',
        trade_type: 'stock',
        position_direction: 'long',
        entry_price: null,
        entry_date: new Date().toISOString().split('T')[0],
        entry_reason: '',
        quantity: null,
        capital_used: null,
        stop_loss: null,
        take_profit: null,
      }
      this.riskRewardRatio = null
    },
  },
}
</script>
