import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCheckoutStore = defineStore('checkout', () => {
  const company       = ref(null)
  const deliveryZones = ref([])

  const orderType     = ref('delivery') // 'delivery' | 'pickup'
  const paymentMethod = ref('')
  const couponCode    = ref('')
  const coupon        = ref(null)        // validated coupon data
  const changeFor     = ref(null)
  const notes         = ref('')

  const customer = ref({
    name:  '',
    phone: '',
    email: '',
  })

  const address = ref({
    zip_code:    '',
    street:      '',
    number:      '',
    complement:  '',
    district:    '',
    city:        '',
    state:       '',
    reference:   '',
  })

  const deliveryFee   = ref(0)
  const matchedZone   = ref(null)

  // ─── Computed ──────────────────────────────────────────────────────────────
  const discount = computed(() => coupon.value?.discount ?? 0)

  function total(subtotal) {
    const fee = orderType.value === 'delivery' ? deliveryFee.value : 0
    return Math.max(0, subtotal - discount.value + fee)
  }

  // ─── Actions ───────────────────────────────────────────────────────────────
  function setCompany(c, zones = []) {
    company.value       = c
    deliveryZones.value = zones
  }

  function setOrderType(type) {
    orderType.value = type
    if (type === 'pickup') {
      deliveryFee.value = 0
      matchedZone.value = null
    }
  }

  function resolveDeliveryFee() {
    if (orderType.value !== 'delivery') {
      deliveryFee.value = 0
      matchedZone.value = null
      return
    }

    const district = address.value.district?.toLowerCase().trim()
    if (! district) {
      deliveryFee.value = company.value?.default_delivery_fee ?? 0
      matchedZone.value = null
      return
    }

    const zone = deliveryZones.value.find(z =>
      (z.neighborhoods ?? []).some(n => n.toLowerCase() === district)
    )

    if (zone) {
      deliveryFee.value = zone.delivery_fee
      matchedZone.value = zone
    } else {
      // Fall back to company default
      deliveryFee.value = company.value?.default_delivery_fee ?? 0
      matchedZone.value = null
    }
  }

  function applyCoupon(data) {
    coupon.value    = data
    couponCode.value = data?.code ?? couponCode.value
  }

  function clearCoupon() {
    coupon.value    = null
    couponCode.value = ''
  }

  function reset() {
    orderType.value     = 'delivery'
    paymentMethod.value = ''
    couponCode.value    = ''
    coupon.value        = null
    changeFor.value     = null
    notes.value         = ''
    customer.value      = { name: '', phone: '', email: '' }
    address.value       = { zip_code: '', street: '', number: '', complement: '', district: '', city: '', state: '', reference: '' }
    deliveryFee.value   = 0
    matchedZone.value   = null
  }

  return {
    company, deliveryZones,
    orderType, paymentMethod, couponCode, coupon, changeFor, notes,
    customer, address, deliveryFee, matchedZone, discount,
    total,
    setCompany, setOrderType, resolveDeliveryFee,
    applyCoupon, clearCoupon, reset,
  }
})
