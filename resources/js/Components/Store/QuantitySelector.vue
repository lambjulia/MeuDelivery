<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: Number, default: 1 },
  min:        { type: Number, default: 1 },
  max:        { type: Number, default: 99 },
})

const emit = defineEmits(['update:modelValue'])

const canDecrease = computed(() => props.modelValue > props.min)
const canIncrease = computed(() => props.modelValue < props.max)

function dec() { if (canDecrease.value) emit('update:modelValue', props.modelValue - 1) }
function inc() { if (canIncrease.value) emit('update:modelValue', props.modelValue + 1) }
</script>

<template>
  <div class="flex items-center gap-1 select-none">
    <button
      :disabled="!canDecrease"
      class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition-colors disabled:opacity-30"
      :style="{ borderColor: 'var(--store-primary, #F97316)', color: 'var(--store-primary, #F97316)' }"
      @click="dec"
    >
      <i class="pi pi-minus text-xs" />
    </button>
    <span class="w-8 text-center font-bold text-gray-900 text-sm">{{ modelValue }}</span>
    <button
      :disabled="!canIncrease"
      class="w-8 h-8 rounded-full text-white flex items-center justify-center transition-opacity disabled:opacity-30"
      :style="{ background: 'var(--store-primary, #F97316)' }"
      @click="inc"
    >
      <i class="pi pi-plus text-xs" />
    </button>
  </div>
</template>
