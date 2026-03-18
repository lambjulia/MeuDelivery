<script setup>
import { computed } from 'vue'
import { useCurrency } from '@/composables/useCurrency'

const props = defineProps({
  group:     { type: Object, required: true },
  modelValue: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue'])
const { format } = useCurrency()

const selected = computed({
  get: () => props.modelValue ?? [],
  set: v => emit('update:modelValue', v),
})

function isSelected(option) {
  return selected.value.some(o => o.id === option.id)
}

function toggle(option) {
  const current = [...(selected.value ?? [])]

  if (props.group.is_multiple) {
    const idx = current.findIndex(o => o.id === option.id)
    if (idx >= 0) {
      current.splice(idx, 1)
    } else {
      const max = props.group.max_selections ?? Infinity
      if (current.length < max) {
        current.push(option)
      }
    }
    selected.value = current
  } else {
    // Single selection (radio-like)
    selected.value = isSelected(option) ? [] : [option]
  }
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-2">
      <h3 class="font-semibold text-gray-900 text-sm">{{ group.name }}</h3>
      <div class="flex items-center gap-1.5">
        <span
          v-if="group.is_required"
          class="text-[11px] px-2 py-0.5 rounded-full bg-red-50 text-red-600 font-medium"
        >
          Obrigatório
        </span>
        <span
          v-if="group.is_multiple && group.max_selections"
          class="text-[11px] text-gray-400"
        >
          Até {{ group.max_selections }}
        </span>
      </div>
    </div>

    <p v-if="group.description" class="text-xs text-gray-500 mb-2">{{ group.description }}</p>

    <div class="space-y-1">
      <button
        v-for="option in group.options"
        :key="option.id"
        :class="[
          'w-full flex items-center justify-between px-3 py-2.5 rounded-xl border text-left transition-all',
          isSelected(option)
            ? 'bg-orange-50 border-orange-300'
            : 'bg-gray-50 border-gray-100 hover:border-gray-200',
        ]"
        @click="toggle(option)"
      >
        <div>
          <p class="text-sm font-medium text-gray-800">{{ option.name }}</p>
          <p v-if="option.description" class="text-xs text-gray-400">{{ option.description }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0 ml-2">
          <span v-if="option.additional_price > 0" class="text-sm font-medium text-gray-600">
            +{{ format(option.additional_price) }}
          </span>
          <div
            :class="[
              'w-5 h-5 rounded flex items-center justify-center border-2 transition-colors',
              isSelected(option)
                ? 'border-orange-400 bg-orange-400'
                : 'border-gray-300',
            ]"
            :style="isSelected(option) ? { borderColor: 'var(--store-primary,#F97316)', background: 'var(--store-primary,#F97316)' } : {}"
          >
            <i v-if="isSelected(option)" class="pi pi-check text-white text-[10px]" />
          </div>
        </div>
      </button>
    </div>
  </div>
</template>
