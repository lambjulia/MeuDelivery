<script setup>
const props = defineProps({
  modelValue:     { type: String, required: true },
  pickupEnabled:  { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="grid gap-3" :class="pickupEnabled ? 'grid-cols-2' : 'grid-cols-1'">
    <button
      :class="[
        'flex flex-col items-center gap-2 p-4 rounded-2xl border-2 transition-all font-medium',
        modelValue === 'delivery'
          ? 'border-orange-400 bg-orange-50 text-orange-700'
          : 'border-gray-200 bg-gray-50 text-gray-600 hover:border-gray-300',
      ]"
      :style="modelValue === 'delivery' ? { borderColor: 'var(--store-primary,#F97316)', color: 'var(--store-primary,#F97316)' } : {}"
      @click="emit('update:modelValue', 'delivery')"
    >
      <i class="pi pi-truck text-2xl" />
      <span class="text-sm">Entrega</span>
    </button>

    <button
      v-if="pickupEnabled"
      :class="[
        'flex flex-col items-center gap-2 p-4 rounded-2xl border-2 transition-all font-medium',
        modelValue === 'pickup'
          ? 'border-orange-400 bg-orange-50 text-orange-700'
          : 'border-gray-200 bg-gray-50 text-gray-600 hover:border-gray-300',
      ]"
      :style="modelValue === 'pickup' ? { borderColor: 'var(--store-primary,#F97316)', color: 'var(--store-primary,#F97316)' } : {}"
      @click="emit('update:modelValue', 'pickup')"
    >
      <i class="pi pi-shop text-2xl" />
      <span class="text-sm">Retirada</span>
    </button>
  </div>
</template>
