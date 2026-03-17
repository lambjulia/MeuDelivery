<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useI18n } from 'vue-i18n'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'

const { t } = useI18n()
const confirm = useConfirm()
const toast = useToast()

const props = defineProps({
  customers: { type: Object, required: true },
  filters:   { type: Object, default: () => ({}) },
})

const search = ref(props.filters.search ?? '')

function applySearch() {
  router.get(route('customers.index'), { search: search.value || undefined }, { preserveState: true, replace: true })
}

function deleteCustomer(c) {
  confirm.require({
    header: `Excluir ${c.name}?`,
    message: 'Esta ação não pode ser desfeita.',
    icon: 'pi pi-trash',
    acceptLabel: 'Sim, excluir',
    rejectLabel: 'Não',
    acceptSeverity: 'danger',
    accept: () => router.delete(route('customers.destroy', c.id), {
      onSuccess: () => toast.add({ severity: 'success', summary: 'Cliente excluído', life: 3000 }),
    }),
  })
}
</script>

<template>
  <AppLayout :title="t('nav.customers')">
    <Head :title="t('nav.customers')" />

    <PageHeader :title="t('nav.customers')" :subtitle="`${customers.total ?? 0} clientes`">
      <Link :href="route('customers.create')">
        <Button label="Novo cliente" icon="pi pi-plus" severity="contrast" size="small" />
      </Link>
    </PageHeader>

    <!-- Search -->
    <div class="bg-surface-0 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-xl p-4 mb-4 flex gap-3">
      <InputText v-model="search" placeholder="Nome, telefone, email..." size="small" class="flex-1" @keyup.enter="applySearch" />
      <Button label="Buscar" icon="pi pi-search" size="small" @click="applySearch" />
    </div>

    <!-- Table -->
    <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 overflow-hidden">
      <DataTable :value="customers.data" size="small" stripedRows>
        <template #empty>
          <EmptyState :title="t('common.noData')" icon="pi-users" />
        </template>
        <Column header="Nome" field="name">
          <template #body="{ data }">
            <Link :href="route('customers.edit', data.id)" class="font-medium text-surface-800 dark:text-surface-100 hover:text-orange-600 dark:hover:text-orange-400">
              {{ data.name }}
            </Link>
          </template>
        </Column>
        <Column field="phone" header="Telefone" />
        <Column field="email" header="E-mail">
          <template #body="{ data }">
            <span class="text-surface-500">{{ data.email ?? '—' }}</span>
          </template>
        </Column>
        <Column header="Endereços">
          <template #body="{ data }">
            <span class="text-surface-500">{{ data.addresses_count ?? 0 }} end.</span>
          </template>
        </Column>
        <Column header="Pedidos">
          <template #body="{ data }">
            <span class="text-surface-500">{{ data.orders_count ?? 0 }}</span>
          </template>
        </Column>
        <Column header="" style="width:100px">
          <template #body="{ data }">
            <div class="flex gap-1 justify-end">
              <Link :href="route('customers.edit', data.id)">
                <Button icon="pi pi-pencil" size="small" text rounded severity="secondary" />
              </Link>
              <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="deleteCustomer(data)" />
            </div>
          </template>
        </Column>
      </DataTable>

      <div v-if="customers.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-surface-200 dark:border-surface-700 text-sm text-surface-500">
        <span>Página {{ customers.current_page }} de {{ customers.last_page }}</span>
        <div class="flex gap-2">
          <Button icon="pi pi-chevron-left" size="small" text severity="secondary" :disabled="customers.current_page === 1" @click="router.get(customers.prev_page_url)" />
          <Button icon="pi pi-chevron-right" size="small" text severity="secondary" :disabled="customers.current_page === customers.last_page" @click="router.get(customers.next_page_url)" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
