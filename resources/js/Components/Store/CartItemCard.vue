<script setup>
import { useCurrency } from '@/composables/useCurrency'
import { useCartStore } from '@/stores/cart'
import QuantitySelector from './QuantitySelector.vue'

const props = defineProps({
  item: { type: Object, required: true },
})

const cart   = useCartStore()
const { format } = useCurrency()

function updateQty(qty) { cart.updateQuantity(props.item.key, qty) }
function remove()       { cart.removeItem(props.item.key) }
</script>

<template>
  <div class="bg-white rounded-2xl p-4 flex gap-3 shadow-sm border border-gray-100">
    <!-- Image -->
    <div class="shrink-0 w-16 h-16 rounded-xl overflow-hidden bg-gray-100">
      <img
        v-if="item.image_url"
        :src="item.image_url"
        :alt="item.product_name"
        class="w-full h-full object-cover"
        loading="lazy"
      />
      <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
        <i class="pi pi-image text-2xl" />
      </div>
    </div>

    <!-- Info -->
    <div class="flex-1 min-w-0">
      <div class="flex justify-between items-start gap-2">
        <p class="font-semibold text-gray-900 text-sm leading-snug">{{ item.product_name }}</p>
        <button
          class="shrink-0 text-gray-400 hover:text-red-400 transition-colors"
          @click="remove"
        >
          <i class="pi pi-trash text-sm" />
        </button>
      </div>

      <!-- Options list -->
      <ul v-if="item.options?.length" class="mt-0.5 space-y-0.5">
        <li
          v-for="opt in item.options"
          :key="opt.option_id"
          class="text-xs text-gray-400"
        >
          + {{ opt.product_option_name }}
          <span v-if="opt.additional_price > 0">(+{{ format(opt.additional_price) }})</span>
        </li>
      </ul>

      <p v-if="item.notes" class="text-xs text-gray-400 italic mt-0.5">"{{ item.notes }}"</p>

      <!-- Qty + price -->
      <div class="flex items-center justify-between mt-2">
        <QuantitySelector
          :model-value="item.quantity"
          :min="1"
          @update:model-value="updateQty"
        />
        <span class="font-bold text-sm" :style="{ color: 'var(--store-primary, #F97316)' }">
          {{ format(item.itemTotal * item.quantity) }}
        </span>
      </div>
    </div>
  </div>
</template>
