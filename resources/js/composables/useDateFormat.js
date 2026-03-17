import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useDateFormat() {
  const page = usePage()
  const locale = computed(() => page.props.auth?.user?.company?.default_locale || 'pt_BR')

  const localeMap = {
    pt_BR: 'pt-BR',
    en: 'en-US',
    es: 'es-ES',
  }

  function format(date, options = {}) {
    if (!date) return '—'
    const jsLocale = localeMap[locale.value] || 'pt-BR'
    const defaultOptions = { day: '2-digit', month: '2-digit', year: 'numeric' }
    return new Intl.DateTimeFormat(jsLocale, { ...defaultOptions, ...options }).format(new Date(date))
  }

  function formatDateTime(date) {
    return format(date, {
      day: '2-digit', month: '2-digit', year: 'numeric',
      hour: '2-digit', minute: '2-digit',
    })
  }

  function fromNow(date) {
    if (!date) return '—'
    const diff = Date.now() - new Date(date).getTime()
    const minutes = Math.floor(diff / 60000)
    if (minutes < 1) return 'just now'
    if (minutes < 60) return `${minutes}m ago`
    const hours = Math.floor(minutes / 60)
    if (hours < 24) return `${hours}h ago`
    return format(date)
  }

  return { format, formatDateTime, fromNow }
}
