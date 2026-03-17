<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import MoneyDisplay from '@/Components/MoneyDisplay.vue'
import { useI18n } from 'vue-i18n'
import { useDateFormat } from '@/composables/useDateFormat'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

const { t } = useI18n()
const { formatDateTime } = useDateFormat()
const toast = useToast()
const confirm = useConfirm()

const props = defineProps({
  board: { type: Object, required: true }, // { pending: [], confirmed: [], preparing: [], ... }
})

const columns = [
  { key: 'pending',          label: 'Pendentes',    color: 'bg-yellow-400' },
  { key: 'confirmed',        label: 'Confirmados',  color: 'bg-blue-400' },
  { key: 'preparing',        label: 'Preparando',   color: 'bg-orange-400' },
  { key: 'ready',            label: 'Prontos',      color: 'bg-teal-400' },
  { key: 'out_for_delivery', label: 'Em entrega',   color: 'bg-purple-400' },
]

const statusTransitions = {
  pending:          'confirmed',
  confirmed:        'preparing',
  preparing:        'ready',
  ready:            'out_for_delivery',
  out_for_delivery: 'delivered',
}

function advance(order) {
  const nextStatus = statusTransitions[order.status]
  if (!nextStatus) return
  confirm.require({
    header: `Avançar para "${nextStatus}"?`,
    message: `Pedido #${order.code}`,
    icon: 'pi pi-arrow-right',
    acceptLabel: 'Sim',
    rejectLabel: 'Não',
    accept: () => {
      router.patch(route('orders.update-status', order.id), { status: nextStatus }, {
        onSuccess: () => toast.add({ severity: 'success', summary: 'Status atualizado!', life: 2000 }),
        preserveScroll: true,
      })
    },
  })
}

function openOrder(id) {
  router.get(route('orders.show', id))
}

const autoRefresh = ref(false)
let timer = null

function toggleAutoRefresh() {
  autoRefresh.value = !autoRefresh.value
  if (autoRefresh.value) {
    timer = setInterval(() => router.reload({ only: ['board'] }), 30000)
  } else {
    clearInterval(timer)
  }
}
</script>

<template>
  <AppLayout :title="t('nav.dispatchBoard')">
    <Head :title="t('nav.dispatchBoard')" />

    <PageHeader :title="t('nav.dispatchBoard')">
      <Button
        :label="autoRefresh ? 'Auto-refresh ON' : 'Auto-refresh OFF'"
        :icon="autoRefresh ? 'pi pi-pause' : 'pi pi-play'"
        :severity="autoRefresh ? 'success' : 'secondary'"
        size="small"
        outlined
        @click="toggleAutoRefresh"
      />
      <Button label="Atualizar" icon="pi pi-refresh" size="small" severity="secondary" outlined @click="router.reload({ only: ['board'] })" />
    </PageHeader>

    <!-- Kanban board -->
    <div class="flex gap-4 overflow-x-auto pb-4">
      <div
        v-for="col in columns"
        :key="col.key"
        class="min-w-[240px] w-[240px] flex-shrink-0 flex flex-col"
      >
        <!-- Column header -->
        <div class="flex items-center gap-2 mb-3">
          <div :class="['w-2.5 h-2.5 rounded-full shrink-0', col.color]" />
          <span class="font-semibold text-surface-700 dark:text-surface-200 text-sm">{{ col.label }}</span>
          <span class="ml-auto bg-surface-200 dark:bg-surface-700 text-surface-600 dark:text-surface-300 text-xs font-bold px-2 py-0.5 rounded-full">
            {{ (board[col.key] ?? []).length }}
          </span>
        </div>

        <!-- Cards -->
        <div class="space-y-3 min-h-24">
          <div
            v-for="order in (board[col.key] ?? [])"
            :key="order.id"
            class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-4 cursor-pointer hover:shadow-md dark:hover:shadow-surface-900/40 transition-shadow"
            @click="openOrder(order.id)"
          >
            <div class="flex items-center justify-between mb-2">
              <span class="font-mono text-xs font-bold text-orange-600 dark:text-orange-400">#{{ order.code }}</span>
              <span class="text-xs text-surface-400">{{ formatDateTime(order.created_at) }}</span>
            </div>
            <p class="text-sm font-medium text-surface-800 dark:text-surface-100 truncate mb-1">
              {{ order.customer?.name ?? '—' }}
            </p>
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">
                <MoneyDisplay :value="order.total_amount" />
              </span>
              <span class="text-xs px-1.5 py-0.5 rounded" :class="order.order_type === 'delivery' ? 'bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'bg-purple-100 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400'">
                {{ order.order_type === 'delivery' ? '🛵' : '🏪' }}
              </span>
            </div>
            <div v-if="statusTransitions[col.key]" class="mt-3 pt-3 border-t border-surface-100 dark:border-surface-700">
              <button
                class="w-full text-xs text-center text-orange-600 dark:text-orange-400 font-medium hover:underline"
                @click.stop="advance(order)"
              >
                Avançar → {{ statusTransitions[col.key] }}
              </button>
            </div>
          </div>
          <div v-if="!(board[col.key] ?? []).length" class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-xl p-6 text-center text-xs text-surface-400">
            Vazio
          </div>
        </div>
      </div>

      <!-- Delivered preview -->
      <div class="min-w-[240px] w-[240px] flex-shrink-0 flex flex-col opacity-60">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-2.5 h-2.5 rounded-full shrink-0 bg-green-400" />
          <span class="font-semibold text-surface-700 dark:text-surface-200 text-sm">Entregues (hoje)</span>
          <span class="ml-auto bg-surface-200 dark:bg-surface-700 text-surface-600 dark:text-surface-300 text-xs font-bold px-2 py-0.5 rounded-full">
            {{ (board.delivered ?? []).length }}
          </span>
        </div>
        <div class="space-y-2">
          <div
            v-for="order in (board.delivered ?? []).slice(0,5)"
            :key="order.id"
            class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-3 cursor-pointer"
            @click="openOrder(order.id)"
          >
            <div class="flex justify-between items-center">
              <span class="font-mono text-xs font-bold text-surface-500">#{{ order.code }}</span>
              <span class="text-xs font-semibold text-green-600 dark:text-green-400"><MoneyDisplay :value="order.total_amount" /></span>
            </div>
            <p class="text-xs text-surface-500 truncate mt-0.5">{{ order.customer?.name }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
