<script setup>
import { computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import StoreHeader from '@/Components/Store/StoreHeader.vue'
import CartItemCard from '@/Components/Store/CartItemCard.vue'
import CartSummary from '@/Components/Store/CartSummary.vue'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  company: { type: Object, required: true },
})

const cart = useCartStore()
onMounted(() => cart.init(props.company.slug))

const isEmpty = computed(() => cart.count === 0)
</script>

<template>
  <StoreLayout :company="company" title="Carrinho">
    <StoreHeader
      :company="company"
      :show-cart="false"
      show-back
      :back-href="route('store.home', company.slug)"
      back-label="Cardápio"
    />

    <div class="max-w-3xl mx-auto px-4 py-5 pb-24">
      <h1 class="font-bold text-xl text-gray-900 mb-4">Seu carrinho</h1>

      <!-- Empty state -->
      <div v-if="isEmpty" class="flex flex-col items-center gap-4 py-16 text-center">
        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
          <i class="pi pi-shopping-cart text-4xl text-gray-400" />
        </div>
        <p class="font-semibold text-gray-700">Seu carrinho está vazio</p>
        <p class="text-sm text-gray-500">Adicione produtos para continuar</p>
        <Link
          :href="route('store.home', company.slug)"
          class="mt-2 px-5 py-2.5 rounded-xl text-white text-sm font-medium"
          :style="{ background: 'var(--store-primary, #F97316)' }"
        >
          Ver cardápio
        </Link>
      </div>

      <!-- Items -->
      <div v-else>
        <div class="space-y-3 mb-5">
          <CartItemCard
            v-for="item in cart.items"
            :key="item.key"
            :item="item"
          />
        </div>

        <!-- Coupon teaser -->
        <div class="mb-5">
          <p class="text-sm text-gray-500 text-center">
            <i class="pi pi-ticket mr-1" />
            Tem um cupom? Aplique no checkout.
          </p>
        </div>

        <!-- Summary -->
        <CartSummary
          :subtotal="cart.subtotal"
          :delivery-fee="0"
          :discount="0"
          :total="cart.subtotal"
          order-type="delivery"
          class="mb-5"
        />

        <!-- Checkout CTA -->
        <Link
          :href="route('store.checkout', company.slug)"
          class="flex items-center justify-center gap-2 w-full py-4 rounded-2xl text-white font-bold text-base transition-opacity active:opacity-80"
          :style="{ background: 'var(--store-primary, #F97316)' }"
        >
          <i class="pi pi-arrow-right" />
          Ir para o checkout
        </Link>
      </div>
    </div>
  </StoreLayout>
</template>
