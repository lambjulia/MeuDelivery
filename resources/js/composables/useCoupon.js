import { ref } from 'vue'

export function useCoupon(companySlug) {
  const loading = ref(false)
  const error   = ref('')
  const applied = ref(null)

  async function validate(code, subtotal) {
    if (! code?.trim()) return

    loading.value = true
    error.value   = ''

    try {
      const res = await fetch(`/api/store/${companySlug}/validate-coupon`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-XSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({ code: code.trim().toUpperCase(), subtotal }),
      })

      const data = await res.json()

      if (data.valid) {
        applied.value = data.coupon
        error.value   = ''
      } else {
        applied.value = null
        error.value   = data.message ?? 'Invalid coupon.'
      }

      return data
    } catch {
      error.value = 'Could not validate coupon.'
      return { valid: false }
    } finally {
      loading.value = false
    }
  }

  function clear() {
    applied.value = null
    error.value   = ''
  }

  return { loading, error, applied, validate, clear }
}

function getCsrfToken() {
  return document.cookie
    .split('; ')
    .find(row => row.startsWith('XSRF-TOKEN='))
    ?.split('=')[1]
    ?.replace(/%3D/g, '=') ?? ''
}
