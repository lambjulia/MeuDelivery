<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import MoneyDisplay from '@/Components/MoneyDisplay.vue'
import { useI18n } from 'vue-i18n'
import { useDateFormat } from '@/composables/useDateFormat'
import { useOrderStatus } from '@/composables/useOrderStatus'
import Button from 'primevue/button'
import Select from 'primevue/select'
import Textarea from 'primevue/textarea'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

const { t } = useI18n()
const { formatDateTime } = useDateFormat()
const { getLabel, orderedStatuses } = useOrderStatus()
const toast = useToast()
const confirm = useConfirm()

const props = defineProps({
  order:   { type: Object, required: true },
  drivers: { type: Array,  default: () => [] },
})

const showStatusForm  = ref(false)
const showDriverForm  = ref(false)
const newStatus       = ref('')
const driverNote      = ref('')
const selectedDriver  = ref(props.order.delivery_driver_id)

const validNextStatuses = computed(() => {
  const all = orderedStatuses
  const currentIdx = all.findIndex(s => s === props.order.status)
  if (currentIdx === -1) return []
  return all.slice(currentIdx + 1).filter(s => s !== 'canceled')
})

const nextStatusOptions = computed(() =>
  validNextStatuses.value.map(s => ({ value: s, label: getLabel(s) }))
)

const driverOptions = computed(() =>
  props.drivers.map(d => ({ value: d.id, label: `${d.name} (${d.vehicle_type})` }))
)

function updateStatus() {
  if (!newStatus.value) return
  router.patch(route('orders.update-status', props.order.id), { status: newStatus.value }, {
    onSuccess: () => {
      toast.add({ severity: 'success', summary: 'Status atualizado', life: 3000 })
      showStatusForm.value = false
    },
    onError: (e) => toast.add({ severity: 'error', summary: Object.values(e)[0], life: 4000 }),
  })
}

function assignDriver() {
  router.patch(route('orders.assign-driver', props.order.id), { driver_id: selectedDriver.value }, {
    onSuccess: () => {
      toast.add({ severity: 'success', summary: 'Entregador atribuído', life: 3000 })
      showDriverForm.value = false
    },
  })
}

function cancelOrder() {
  confirm.require({
    header: 'Cancelar pedido',
    message: 'Tem certeza?',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Sim, cancelar',
    rejectLabel: 'Não',
    acceptSeverity: 'danger',
    accept: () => router.patch(route('orders.cancel', props.order.id), {}, {
      onSuccess: () => toast.add({ severity: 'success', summary: 'Pedido cancelado', life: 3000 }),
    }),
  })
}
</script>

<template>
  <AppLayout :title="`Pedido #${order.code}`">
    <Head :title="`Pedido #${order.code}`" />

    <PageHeader :title="`Pedido #${order.code}`" :back="route('orders.index')">
      <Button
        v-if="!['delivered','canceled'].includes(order.status)"
        label="Atualizar status"
        icon="pi pi-refresh"
        size="small"
        severity="contrast"
        @click="showStatusForm = !showStatusForm"
      />
      <Button label="Cancelar" icon="pi pi-times" size="small" severity="danger" outlined @click="cancelOrder" v-if="!['delivered','canceled'].includes(order.status)" />
    </PageHeader>

    <!-- Status update form -->
    <div v-if="showStatusForm" class="bg-orange-50 dark:bg-orange-500/10 border border-orange-200 dark:border-orange-500/30 rounded-xl p-4 mb-4 flex items-end gap-3">
      <div class="flex-1">
        <label class="block text-xs font-medium text-surface-500 mb-1">Novo status</label>
        <Select v-model="newStatus" :options="nextStatusOptions" optionLabel="label" optionValue="value" placeholder="Selecione..." size="small" class="w-full" />
      </div>
      <Button label="Salvar" icon="pi pi-check" size="small" @click="updateStatus" :disabled="!newStatus" />
      <Button label="Cancelar" size="small" severity="secondary" outlined @click="showStatusForm = false" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: order details -->
      <div class="lg:col-span-2 space-y-5">
        <!-- Items -->
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Itens do pedido</h3>
          <div class="space-y-4">
            <div v-for="item in order.items" :key="item.id" class="border-b border-surface-100 dark:border-surface-700 pb-3 last:border-0 last:pb-0">
              <div class="flex justify-between items-start">
                <div>
                  <p class="font-medium text-surface-800 dark:text-surface-100">
                    {{ item.quantity }}× {{ item.product_name }}
                  </p>
                  <div v-if="item.options?.length" class="mt-1 space-y-0.5">
                    <p v-for="opt in item.options" :key="opt.id" class="text-xs text-surface-500">
                      + {{ opt.option_group_name }}: {{ opt.option_name }}
                      <span v-if="opt.additional_price > 0">(+<MoneyDisplay :value="opt.additional_price" />)</span>
                    </p>
                  </div>
                  <p v-if="item.notes" class="text-xs text-surface-400 mt-1 italic">{{ item.notes }}</p>
                </div>
                <MoneyDisplay :value="(item.unit_price + (item.options?.reduce((s, o) => s + o.additional_price, 0) ?? 0)) * item.quantity" class="font-semibold text-surface-800 dark:text-surface-100" />
              </div>
            </div>
          </div>
          <!-- Totals -->
          <div class="mt-4 space-y-1 text-sm">
            <div class="flex justify-between text-surface-500">
              <span>Subtotal</span><MoneyDisplay :value="order.subtotal" />
            </div>
            <div v-if="order.delivery_fee" class="flex justify-between text-surface-500">
              <span>Taxa de entrega</span><MoneyDisplay :value="order.delivery_fee" />
            </div>
            <div v-if="order.discount_amount" class="flex justify-between text-green-600 dark:text-green-400">
              <span>Desconto ({{ order.coupon_code }})</span>
              <span>-<MoneyDisplay :value="order.discount_amount" /></span>
            </div>
            <div class="flex justify-between font-bold text-base border-t border-surface-200 dark:border-surface-700 pt-2 mt-2">
              <span>Total</span><MoneyDisplay :value="order.total_amount" />
            </div>
          </div>
        </div>

        <!-- Status history -->
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Histórico</h3>
          <div class="relative">
            <div class="absolute left-3 top-0 bottom-0 w-px bg-surface-200 dark:bg-surface-700" />
            <div v-for="h in order.statusHistories" :key="h.id" class="relative flex gap-4 mb-4 last:mb-0">
              <div class="w-6 h-6 rounded-full bg-surface-0 dark:bg-surface-800 border-2 border-orange-400 z-10 shrink-0 mt-0.5" />
              <div>
                <p class="text-sm font-medium text-surface-800 dark:text-surface-100">
                  <StatusBadge :status="h.status" size="small" />
                </p>
                <p class="text-xs text-surface-400 mt-0.5">
                  {{ formatDateTime(h.created_at) }}
                  <span v-if="h.changed_by">· {{ h.changedBy?.name }}</span>
                </p>
                <p v-if="h.notes" class="text-xs text-surface-500 italic mt-0.5">{{ h.notes }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: info panels -->
      <div class="space-y-5">
        <!-- Order summary -->
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-3">Resumo</h3>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between">
              <dt class="text-surface-500">Status</dt>
              <dd><StatusBadge :status="order.status" size="small" /></dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-surface-500">Tipo</dt>
              <dd class="font-medium">{{ order.order_type === 'delivery' ? 'Entrega' : 'Retirada' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-surface-500">Pagamento</dt>
              <dd class="font-medium capitalize">{{ order.payment_method?.replace(/_/g, ' ') }}</dd>
            </div>
            <div v-if="order.change_amount" class="flex justify-between">
              <dt class="text-surface-500">Troco para</dt>
              <dd><MoneyDisplay :value="order.change_amount" /></dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-surface-500">Criado em</dt>
              <dd class="text-surface-600 dark:text-surface-300">{{ formatDateTime(order.created_at) }}</dd>
            </div>
          </dl>
        </div>

        <!-- Customer -->
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-3">Cliente</h3>
          <p class="font-medium text-surface-800 dark:text-surface-100">{{ order.customer?.name }}</p>
          <p class="text-sm text-surface-500">{{ order.customer?.phone }}</p>
          <div v-if="order.deliveryAddress" class="mt-2 text-sm text-surface-500">
            <p>{{ order.deliveryAddress.street }}, {{ order.deliveryAddress.number }}</p>
            <p>{{ order.deliveryAddress.district }}</p>
            <p>{{ order.deliveryAddress.city }}/{{ order.deliveryAddress.state }}</p>
          </div>
        </div>

        <!-- Driver -->
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-surface-800 dark:text-surface-100">Entregador</h3>
            <Button
              v-if="order.order_type === 'delivery'"
              :label="showDriverForm ? 'Cancelar' : 'Atribuir'"
              size="small"
              text
              severity="secondary"
              @click="showDriverForm = !showDriverForm"
            />
          </div>
          <div v-if="!showDriverForm">
            <p v-if="order.driver" class="font-medium text-surface-800 dark:text-surface-100">{{ order.driver.name }}</p>
            <p v-else class="text-sm text-surface-400 italic">Nenhum entregador atribuído</p>
          </div>
          <div v-else class="space-y-2">
            <Select v-model="selectedDriver" :options="driverOptions" optionLabel="label" optionValue="value" size="small" class="w-full" placeholder="Selecione..." />
            <Button label="Confirmar" size="small" class="w-full" :disabled="!selectedDriver" @click="assignDriver" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
