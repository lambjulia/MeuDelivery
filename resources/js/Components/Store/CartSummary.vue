<script setup>
import { useCurrency } from '@/composables/useCurrency'

const props = defineProps({
  subtotal:    { type: Number, default: 0 },
  deliveryFee: { type: Number, default: 0 },
  discount:    { type: Number, default: 0 },
  total:       { type: Number, default: 0 },
  orderType:   { type: String, default: 'delivery' },
})

const { format } = useCurrency()
</script>

<template>
  <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 space-y-3">
    <h3 class="font-semibold text-gray-900">Resumo</h3>

    <div class="space-y-1.5 text-sm">
      <div class="flex justify-between text-gray-600">
        <span>Subtotal</span>
        <span>{{ format(subtotal) }}</span>
      </div>
      <div v-if="orderType === 'delivery'" class="flex justify-between text-gray-600">
        <span>Entrega</span>
        <span :class="deliveryFee === 0 ? 'text-green-600 font-medium' : ''">
          {{ deliveryFee === 0 ? 'Grátis' : format(deliveryFee) }}
        </span>
      </div>
      <div v-if="discount > 0" class="flex justify-between text-green-600">
        <span>Desconto</span>
        <span>−{{ format(discount) }}</span>
      </div>
    </div>

    <div class="border-t border-gray-100 pt-3 flex justify-between font-bold text-gray-900">
      <span>Total</span>
      <span :style="{ color: 'var(--store-primary, #F97316)' }">{{ format(total) }}</span>
    </div>
  </div>
</template>
