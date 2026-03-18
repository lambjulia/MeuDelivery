<script setup>
const METHOD_ICONS = {
  cash:        'pi-money-bill',
  credit_card: 'pi-credit-card',
  debit_card:  'pi-credit-card',
  pix:         'pi-qrcode',
  voucher:     'pi-id-card',
}

const METHOD_LABELS = {
  cash:        'Dinheiro',
  credit_card: 'Cartão de crédito',
  debit_card:  'Cartão de débito',
  pix:         'PIX',
  voucher:     'Vale',
}

const props = defineProps({
  methods:    { type: Array,  required: true },
  modelValue: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="grid grid-cols-2 gap-2">
    <button
      v-for="method in methods"
      :key="method"
      :class="[
        'flex items-center gap-2.5 p-3 rounded-xl border-2 text-left transition-all',
        modelValue === method
          ? 'bg-orange-50 border-orange-400 text-orange-700 font-medium'
          : 'border-gray-200 bg-gray-50 text-gray-600 hover:border-gray-300',
      ]"
      :style="modelValue === method ? { borderColor: 'var(--store-primary,#F97316)', color: 'var(--store-primary,#F97316)' } : {}"
      @click="emit('update:modelValue', method)"
    >
      <i :class="['pi text-lg', METHOD_ICONS[method] ?? 'pi-credit-card']" />
      <span class="text-sm">{{ METHOD_LABELS[method] ?? method }}</span>
    </button>
  </div>
</template>
