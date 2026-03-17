import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useCurrency() {
  const page = usePage()
  const locale = computed(() => page.props.auth?.user?.company?.default_locale || 'pt_BR')

  function format(value, options = {}) {
    const localeMap = {
      pt_BR: 'pt-BR',
      en: 'en-US',
      es: 'es-ES',
    }

    const currencyMap = {
      pt_BR: 'BRL',
      en: 'USD',
      es: 'EUR',
    }

    const jsLocale = localeMap[locale.value] || 'pt-BR'
    const currency = options.currency || currencyMap[locale.value] || 'BRL'

    return new Intl.NumberFormat(jsLocale, {
      style: 'currency',
      currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
      ...options,
    }).format(Number(value) || 0)
  }

  function formatNumber(value) {
    return new Intl.NumberFormat('pt-BR').format(Number(value) || 0)
  }

  return { format, formatNumber }
}
