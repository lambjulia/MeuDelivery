import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useTrackingStore = defineStore('tracking', () => {
  const order       = ref(null)
  const polling     = ref(false)
  let   pollInterval = null

  function setOrder(data) {
    order.value = data
  }

  function startPolling(companySlug, orderCode, intervalMs = 30000) {
    stopPolling()
    polling.value = true

    pollInterval = setInterval(async () => {
      try {
        const res = await fetch(`/api/store/${companySlug}/track/${orderCode}`)
        if (res.ok) {
          order.value = await res.json()
        }
      } catch {
        // Silently ignore network errors during polling
      }
    }, intervalMs)
  }

  function stopPolling() {
    if (pollInterval) {
      clearInterval(pollInterval)
      pollInterval = null
    }
    polling.value = false
  }

  return { order, polling, setOrder, startPolling, stopPolling }
})
