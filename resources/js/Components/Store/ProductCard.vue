<script setup>
const props = defineProps({
  product:     { type: Object, required: true },
  companySlug: { type: String, required: true },
})

const emit = defineEmits(['open'])

import { computed } from 'vue'
import { useCurrency } from '@/composables/useCurrency'
const { format } = useCurrency()

const hasPromo = computed(() =>
  props.product.promotional_price !== null &&
  props.product.promotional_price < props.product.base_price
)

// Summary for option groups (if available). Show up to 2 names, and +N otherwise.
const optionSummary = computed(() => {
  const groups = props.product.option_groups ?? []
  if (!groups.length) return null
  const names = groups.map(g => g.name).filter(Boolean)
  if (!names.length) return null
  const shown = names.slice(0, 2)
  const more = names.length - shown.length
  return { shown, more }
})
</script>

<template>
  <button
    class="w-full text-left bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex gap-3 p-3 active:scale-[0.99] transition-transform hover:shadow-md hover:translate-y-[-2px]"
    @click="emit('open', product)"
  >
    <!-- Product image -->
    <div class="shrink-0 w-28 h-28 rounded-xl overflow-hidden bg-gray-100 flex items-center justify-center">
      <img
        v-if="product.image_url"
        :src="product.image_url"
        :alt="product.name"
        class="w-full h-full object-cover"
        loading="lazy"
      />
      <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
        <i class="pi pi-image text-3xl" />
      </div>
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
      <p class="font-semibold text-gray-900 text-sm leading-snug line-clamp-2 mb-1">
        {{ product.name }}
      </p>
      <p v-if="product.description" class="text-xs text-gray-500 line-clamp-2 mb-2">
        {{ product.description }}
      </p>

      <div v-if="optionSummary" class="flex flex-wrap gap-2 mb-2">
        <template v-for="(name, i) in optionSummary.shown" :key="i">
          <span class="text-[11px] bg-indigo-50 text-indigo-700 px-2 py-1 rounded-full border border-indigo-100">{{ name }}</span>
        </template>
        <span v-if="optionSummary.more > 0" class="text-[11px] bg-indigo-50 text-indigo-700 px-2 py-1 rounded-full border border-indigo-100">+{{ optionSummary.more }} opções</span>
      </div>

      <div class="flex items-end gap-2 mt-auto">
        <span class="font-bold text-sm" :style="{ color: 'var(--store-primary, #F97316)' }">
          {{ format(product.current_price) }}
        </span>
        <span v-if="hasPromo" class="text-xs text-gray-400 line-through">
          {{ format(product.base_price) }}
        </span>
      </div>
    </div>

    <!-- Add indicator -->
    <div
      class="shrink-0 self-end w-7 h-7 rounded-full flex items-center justify-center text-white"
      :style="{ background: 'var(--store-primary, #F97316)' }"
    >
      <i class="pi pi-plus text-xs" />
    </div>
  </button>
</template>
