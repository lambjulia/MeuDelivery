<script setup>
import { useCurrency } from '@/composables/useCurrency'

const props = defineProps({
  value:    { type: Number, required: true },
  positive: { type: Boolean, default: false }, // green when > 0
  negative: { type: Boolean, default: false }, // red when < 0
})

const { format } = useCurrency()
</script>

<template>
  <span
    :class="{
      'text-green-600 dark:text-green-400': positive && value > 0,
      'text-red-600   dark:text-red-400':   negative && value < 0,
    }"
  >{{ format(value) }}</span>
</template>
