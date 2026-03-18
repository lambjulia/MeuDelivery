import { ref, computed } from 'vue'
import { useCheckoutStore } from '@/stores/checkout'
import { useCartStore } from '@/stores/cart'

export function useCheckout() {
  const checkout = useCheckoutStore()
  const cart     = useCartStore()
  const loading  = ref(false)
  const errors   = ref({})

  const totalAmount = computed(() => checkout.total(cart.subtotal))

  async function placeOrder(companySlug) {
    loading.value = true
    errors.value  = {}

    const payload = {
      customer:       checkout.customer,
      order_type:     checkout.orderType,
      payment_method: checkout.paymentMethod,
      notes:          checkout.notes || null,
      change_for:     checkout.changeFor || null,
      coupon_code:    checkout.coupon?.code || null,
      delivery_fee:   checkout.deliveryFee,
      items:          cart.toApiPayload(),
      ...(checkout.orderType === 'delivery' ? { address: checkout.address } : {}),
    }

    try {
      const res = await fetch(`/api/store/${companySlug}/orders`, {
        method:  'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-XSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify(payload),
      })

      const data = await res.json()

      if (! res.ok) {
        errors.value = data.errors ?? { order: [data.message ?? 'An error occurred.'] }
        return { success: false }
      }

      cart.clear()
      checkout.reset()
      return { success: true, orderCode: data.order_code, redirect: data.redirect }
    } catch (e) {
      errors.value = { order: ['Network error. Please try again.'] }
      return { success: false }
    } finally {
      loading.value = false
    }
  }

  return { checkout, cart, loading, errors, totalAmount, placeOrder }
}

function getCsrfToken() {
  return document.cookie
    .split('; ')
    .find(row => row.startsWith('XSRF-TOKEN='))
    ?.split('=')[1]
    ?.replace(/%3D/g, '=') ?? ''
}
