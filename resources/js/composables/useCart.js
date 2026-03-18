import { useCartStore } from '@/stores/cart'
import { ref } from 'vue'

export function useCart() {
  const cart = useCartStore()
  const showAddedFeedback = ref(false)

  function add(product, options, quantity = 1, notes = '') {
    cart.addItem(product, options, quantity, notes)

    showAddedFeedback.value = true
    setTimeout(() => { showAddedFeedback.value = false }, 1500)
  }

  return { cart, showAddedFeedback, add }
}
