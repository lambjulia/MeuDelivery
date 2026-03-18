import { computed } from 'vue'

const DAY_MAP = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']

export function useBusinessHours(company) {
  const isOpen = computed(() => {
    if (! company.value) return false
    const hours = company.value.business_hours
    if (! hours || Object.keys(hours).length === 0) return true

    const now    = new Date()
    const dayKey = DAY_MAP[now.getDay()]
    const day    = hours[dayKey]

    if (! day?.open) return false

    const pad    = s => s.padStart(5, '0')
    const nowStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`

    return nowStr >= pad(day.from ?? '00:00') && nowStr <= pad(day.to ?? '23:59')
  })

  const todayHours = computed(() => {
    const hours = company.value?.business_hours
    if (! hours) return null
    const now    = new Date()
    const dayKey = DAY_MAP[now.getDay()]
    return hours[dayKey] ?? null
  })

  return { isOpen, todayHours }
}
