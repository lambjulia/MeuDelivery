<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  company: { type: Object, required: true },
})

const cart  = useCartStore()
const show  = computed(() => cart.count > 0)
</script>

<template>
  <Transition name="fab">
    <Link
      v-if="show"
      :href="route('store.cart', company.slug)"
      class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 px-5 py-3 rounded-full shadow-lg text-white font-semibold text-sm transition-all active:scale-95"
      :style="{ background: 'var(--store-primary, #F97316)' }"
    >
      <i class="pi pi-shopping-cart" />
      <span>Ver carrinho</span>
      <span class="bg-white/25 px-2 py-0.5 rounded-full text-xs font-bold">
        {{ cart.count }} {{ cart.count === 1 ? 'item' : 'itens' }}
      </span>
    </Link>
  </Transition>
</template>

<style scoped>
.fab-enter-active, .fab-leave-active { transition: all 0.3s cubic-bezier(.25,.46,.45,.94); }
.fab-enter-from, .fab-leave-to       { opacity: 0; transform: translate(-50%, 20px); }
</style>
