import { useTrackingStore } from '@/stores/tracking'
import { onUnmounted } from 'vue'

export function useOrderTracking() {
  const store = useTrackingStore()

  function start(companySlug, orderCode) {
    store.startPolling(companySlug, orderCode)
  }

  onUnmounted(() => store.stopPolling())

  return { order: store.order, polling: store.polling, start, stop: store.stopPolling }
}
