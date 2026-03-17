<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import EmptyState from '@/Components/EmptyState.vue'
import MoneyDisplay from '@/Components/MoneyDisplay.vue'
import { useI18n } from 'vue-i18n'
import { useDateFormat } from '@/composables/useDateFormat'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'

const { t } = useI18n()
const { format: formatDate } = useDateFormat()
const confirm = useConfirm()
const toast = useToast()

const props = defineProps({
  orders:  { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const search     = ref(props.filters.search ?? '')
const status     = ref(props.filters.status ?? '')
const orderType  = ref(props.filters.order_type ?? '')

const statuses = [
  { value: '',                label: 'Todos os status' },
  { value: 'pending',         label: 'Pendente' },
  { value: 'confirmed',       label: 'Confirmado' },
  { value: 'preparing',       label: 'Preparando' },
  { value: 'ready',           label: 'Pronto' },
  { value: 'out_for_delivery',label: 'Em entrega' },
  { value: 'delivered',       label: 'Entregue' },
  { value: 'canceled',        label: 'Cancelado' },
]

function applyFilters() {
  router.get(route('orders.index'), {
    search: search.value || undefined,
    status: status.value || undefined,
    order_type: orderType.value || undefined,
  }, { preserveState: true, replace: true })
}

function resetFilters() {
  search.value = ''
  status.value = ''
  orderType.value = ''
  applyFilters()
}

function cancelOrder(id) {
  confirm.require({
    header: 'Cancelar pedido',
    message: 'Tem certeza que deseja cancelar este pedido?',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Sim, cancelar',
    rejectLabel: 'Não',
    acceptSeverity: 'danger',
    accept: () => {
      router.patch(route('orders.cancel', id), {}, {
        onSuccess: () => toast.add({ severity: 'success', summary: 'Pedido cancelado', life: 3000 }),
      })
    },
  })
}
</script>

<template>
  <AppLayout :title="t('nav.orders')">
    <Head :title="t('nav.orders')" />

    <PageHeader :title="t('nav.orders')" :subtitle="`${orders.total ?? 0} pedidos`">
      <Link :href="route('orders.create')">
        <Button :label="t('orders.new')" icon="pi pi-plus" severity="contrast" size="small" />
      </Link>
      <Link :href="route('orders.dispatch-board')">
        <Button :label="t('nav.dispatchBoard')" icon="pi pi-map" severity="secondary" size="small" outlined />
      </Link>
    </PageHeader>

    <!-- Filters -->
    <div class="bg-surface-0 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-xl p-4 mb-4 flex flex-wrap items-end gap-3">
      <div class="flex-1 min-w-48">
        <label class="block text-xs font-medium text-surface-500 mb-1">Buscar</label>
        <InputText
          v-model="search"
          placeholder="Cód, cliente, telefone..."
          size="small"
          class="w-full"
          @keyup.enter="applyFilters"
        />
      </div>
      <div class="min-w-44">
        <label class="block text-xs font-medium text-surface-500 mb-1">Status</label>
        <Select v-model="status" :options="statuses" optionLabel="label" optionValue="value" size="small" class="w-full" />
      </div>
      <Button label="Filtrar" icon="pi pi-filter" size="small" @click="applyFilters" />
      <Button label="Limpar" icon="pi pi-times" size="small" severity="secondary" outlined @click="resetFilters" />
    </div>

    <!-- Table -->
    <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 overflow-hidden">
      <DataTable
        :value="orders.data"
        size="small"
        stripedRows
        class="text-sm"
        :pt="{ root: { class: 'dark:[&_th]:bg-surface-700 dark:[&_td]:border-surface-700' } }"
      >
        <template #empty>
          <EmptyState :title="t('common.noData')" icon="pi-shopping-cart" />
        </template>
        <Column field="code" header="Código" style="width:110px">
          <template #body="{ data }">
            <Link :href="route('orders.show', data.id)" class="font-mono text-orange-600 dark:text-orange-400 hover:underline font-semibold">
              #{{ data.code }}
            </Link>
          </template>
        </Column>
        <Column header="Cliente" field="customer.name">
          <template #body="{ data }">
            <span class="font-medium text-surface-800 dark:text-surface-100">{{ data.customer?.name ?? '—' }}</span>
          </template>
        </Column>
        <Column header="Tipo">
          <template #body="{ data }">
            <span class="text-xs px-2 py-0.5 rounded-full" :class="data.order_type === 'delivery' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' : 'bg-purple-100 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400'">
              {{ data.order_type === 'delivery' ? 'Entrega' : 'Retirada' }}
            </span>
          </template>
        </Column>
        <Column header="Status">
          <template #body="{ data }">
            <StatusBadge :status="data.status" size="small" />
          </template>
        </Column>
        <Column header="Total" style="text-align:right">
          <template #body="{ data }">
            <MoneyDisplay :value="data.total_amount" />
          </template>
        </Column>
        <Column header="Data">
          <template #body="{ data }">
            <span class="text-surface-500">{{ formatDate(data.created_at) }}</span>
          </template>
        </Column>
        <Column header="" style="width:120px">
          <template #body="{ data }">
            <div class="flex items-center gap-1 justify-end">
              <Link :href="route('orders.show', data.id)">
                <Button icon="pi pi-eye" size="small" text rounded severity="secondary" />
              </Link>
              <Button
                v-if="!['delivered','canceled'].includes(data.status)"
                icon="pi pi-times"
                size="small"
                text
                rounded
                severity="danger"
                @click="cancelOrder(data.id)"
              />
            </div>
          </template>
        </Column>
      </DataTable>

      <!-- Pagination -->
      <div v-if="orders.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-surface-200 dark:border-surface-700 text-sm text-surface-500">
        <span>Página {{ orders.current_page }} de {{ orders.last_page }}</span>
        <div class="flex gap-2">
          <Button
            icon="pi pi-chevron-left"
            size="small"
            text
            severity="secondary"
            :disabled="orders.current_page === 1"
            @click="router.get(orders.prev_page_url)"
          />
          <Button
            icon="pi pi-chevron-right"
            size="small"
            text
            severity="secondary"
            :disabled="orders.current_page === orders.last_page"
            @click="router.get(orders.next_page_url)"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
