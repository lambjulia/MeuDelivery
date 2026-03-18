<script setup>
import { ref } from 'vue'
import { useCoupon } from '@/composables/useCoupon'
import { useCheckoutStore } from '@/stores/checkout'

const props = defineProps({
  companySlug: { type: String, required: true },
  subtotal:    { type: Number, required: true },
})

const checkout = useCheckoutStore()
const code     = ref(checkout.coupon?.code ?? '')
const coupon   = useCoupon(props.companySlug)

async function apply() {
  const result = await coupon.validate(code.value, props.subtotal)
  if (result?.valid) {
    checkout.applyCoupon(result.coupon)
  }
}

function remove() {
  coupon.clear()
  checkout.clearCoupon()
  code.value = ''
}
</script>

<template>
  <div>
    <!-- Applied state -->
    <div
      v-if="checkout.coupon"
      class="flex items-center justify-between p-3 rounded-xl bg-green-50 border border-green-200"
    >
      <div class="flex items-center gap-2 text-green-700">
        <i class="pi pi-check-circle" />
        <span class="text-sm font-medium">{{ checkout.coupon.code }}</span>
        <span class="text-sm">descontou</span>
        <span class="font-bold text-sm">R$ {{ Number(checkout.coupon.discount).toFixed(2) }}</span>
      </div>
      <button class="text-green-600 hover:text-green-800" @click="remove">
        <i class="pi pi-times text-sm" />
      </button>
    </div>

    <!-- Input state -->
    <div v-else class="flex gap-2">
      <input
        v-model="code"
        type="text"
        placeholder="Cupom de desconto"
        class="flex-1 border border-gray-200 rounded-xl px-3 py-2.5 text-sm uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-orange-300"
        @keydown.enter="apply"
      />
      <button
        :disabled="coupon.loading.value || !code.trim()"
        class="px-4 py-2.5 rounded-xl text-white text-sm font-medium transition-opacity disabled:opacity-50"
        :style="{ background: 'var(--store-primary, #F97316)' }"
        @click="apply"
      >
        <i v-if="coupon.loading.value" class="pi pi-spinner pi-spin" />
        <span v-else>Aplicar</span>
      </button>
    </div>

    <p v-if="coupon.error.value" class="text-xs text-red-500 mt-1.5">
      <i class="pi pi-exclamation-circle mr-1" />{{ coupon.error.value }}
    </p>
  </div>
</template>
