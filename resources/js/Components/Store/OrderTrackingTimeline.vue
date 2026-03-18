<script setup>
const STEPS = [
  { status: 'pending',         label: 'Recebido',     icon: 'pi-check' },
  { status: 'confirmed',       label: 'Confirmado',   icon: 'pi-check-circle' },
  { status: 'preparing',       label: 'Preparando',   icon: 'pi-box' },
  { status: 'ready',           label: 'Pronto',       icon: 'pi-check-circle' },
  { status: 'out_for_delivery', label: 'Saiu para entrega', icon: 'pi-truck' },
  { status: 'delivered',       label: 'Entregue',     icon: 'pi-home' },
]

const STATUS_ORDER = STEPS.map(s => s.status)

const props = defineProps({
  currentStatus: { type: String, required: true },
  history:       { type: Array,  default: () => [] },
  orderType:     { type: String, default: 'delivery' },
})

import { computed } from 'vue'
import { useDateFormat } from '@/composables/useDateFormat'

const { format } = useDateFormat()

const visibleSteps = computed(() => {
  if (props.orderType === 'pickup') {
    return STEPS.filter(s => s.status !== 'out_for_delivery')
  }
  return STEPS
})

const currentIndex = computed(() =>
  STATUS_ORDER.indexOf(props.currentStatus)
)

function isCanceled() {
  return props.currentStatus === 'canceled'
}

function historyEntry(status) {
  return props.history.find(h => h.status === status)
}

function stepState(step) {
  const idx = STATUS_ORDER.indexOf(step.status)
  if (isCanceled()) return 'canceled'
  if (idx < currentIndex.value)  return 'done'
  if (idx === currentIndex.value) return 'active'
  return 'pending'
}
</script>

<template>
  <!-- Canceled state -->
  <div
    v-if="isCanceled()"
    class="flex flex-col items-center gap-3 py-6 text-center"
  >
    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
      <i class="pi pi-times-circle text-3xl text-red-500" />
    </div>
    <p class="font-semibold text-gray-900">Pedido cancelado</p>
    <p class="text-sm text-gray-500">Esse pedido foi cancelado.</p>
  </div>

  <!-- Normal timeline -->
  <div v-else class="space-y-0">
    <div
      v-for="(step, idx) in visibleSteps"
      :key="step.status"
      class="flex gap-4 pb-6 last:pb-0"
    >
      <!-- Connector line + dot -->
      <div class="flex flex-col items-center">
        <div
          :class="[
            'w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-all z-10',
            stepState(step) === 'done'   ? 'bg-green-500 text-white' :
            stepState(step) === 'active' ? 'text-white shadow-lg'    :
            'bg-gray-100 text-gray-400',
          ]"
          :style="stepState(step) === 'active' ? { background: 'var(--store-primary,#F97316)' } : {}"
        >
          <i
            :class="[
              'pi text-sm',
              stepState(step) === 'done'   ? 'pi-check' :
              stepState(step) === 'active' ? 'pi-spin pi-spinner' :
              step.icon,
            ]"
          />
        </div>
        <div
          v-if="idx < visibleSteps.length - 1"
          :class="[
            'w-0.5 flex-1 mt-1 transition-colors',
            stepState(step) === 'done' ? 'bg-green-300' : 'bg-gray-200',
          ]"
        />
      </div>

      <!-- Label -->
      <div class="pt-1">
        <p
          :class="[
            'text-sm font-medium leading-tight',
            stepState(step) === 'done'   ? 'text-green-700' :
            stepState(step) === 'active' ? 'text-gray-900'  :
            'text-gray-400',
          ]"
        >
          {{ step.label }}
        </p>
        <p v-if="historyEntry(step.status)" class="text-xs text-gray-400 mt-0.5">
          {{ format(historyEntry(step.status).created_at) }}
        </p>
      </div>
    </div>
  </div>
</template>
