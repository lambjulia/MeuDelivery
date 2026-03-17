import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

export function useFilters(initialFilters = {}, routeName = null) {
  const filters = ref({ ...initialFilters })

  function applyFilters() {
    if (routeName) {
      router.get(route(routeName), filters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      })
    }
  }

  function resetFilters() {
    Object.keys(filters.value).forEach((key) => {
      filters.value[key] = ''
    })
    applyFilters()
  }

  return { filters, applyFilters, resetFilters }
}
