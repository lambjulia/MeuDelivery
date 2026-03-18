import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

const CART_KEY = 'meudelivery_cart'

function loadCart(companySlug) {
  try {
    const raw = localStorage.getItem(`${CART_KEY}_${companySlug}`)
    return raw ? JSON.parse(raw) : []
  } catch {
    return []
  }
}

function saveCart(companySlug, items) {
  localStorage.setItem(`${CART_KEY}_${companySlug}`, JSON.stringify(items))
}

export const useCartStore = defineStore('cart', () => {
  const companySlug = ref('')
  const items = ref([])

  function init(slug) {
    companySlug.value = slug
    items.value = loadCart(slug)
  }

  const count = computed(() => items.value.reduce((sum, i) => sum + i.quantity, 0))

  const subtotal = computed(() =>
    items.value.reduce((sum, i) => sum + i.itemTotal * i.quantity, 0)
  )

  function addItem(product, selectedOptions, quantity = 1, notes = '') {
    // Build a deterministic key based on product + options combination
    const optionKey = [...selectedOptions].map(o => o.option_id).sort().join('-')
    const key = `${product.id}-${optionKey}`

    const existing = items.value.find(i => i.key === key)

    const optionExtra = selectedOptions.reduce((s, o) => s + (o.additional_price || 0), 0)
    const itemTotal   = (product.current_price || product.base_price) + optionExtra

    if (existing) {
      existing.quantity += quantity
      existing.notes = notes || existing.notes
    } else {
      items.value.push({
        key,
        product_id:   product.id,
        product_name: product.name,
        image_url:    product.image_url,
        base_price:   product.current_price || product.base_price,
        options:      selectedOptions.map(o => ({
          option_id:                   o.id || o.option_id,
          product_option_name:         o.name,
          product_option_group_name:   o.group_name,
          additional_price:            o.additional_price || 0,
        })),
        itemTotal,
        quantity,
        notes,
      })
    }

    persist()
  }

  function updateQuantity(key, qty) {
    const item = items.value.find(i => i.key === key)
    if (item) {
      item.quantity = Math.max(1, qty)
      persist()
    }
  }

  function removeItem(key) {
    items.value = items.value.filter(i => i.key !== key)
    persist()
  }

  function clear() {
    items.value = []
    persist()
  }

  function persist() {
    saveCart(companySlug.value, items.value)
  }

  /** Serialize cart items to the format the API expects. */
  function toApiPayload() {
    return items.value.map(item => ({
      product_id: item.product_id,
      quantity:   item.quantity,
      notes:      item.notes || null,
      options:    item.options.map(o => ({ option_id: o.option_id })),
    }))
  }

  return {
    items, count, subtotal, companySlug,
    init, addItem, updateQuantity, removeItem, clear, toApiPayload,
  }
})
