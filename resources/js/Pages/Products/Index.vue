<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import EmptyState from '@/Components/EmptyState.vue'
import MoneyDisplay from '@/Components/MoneyDisplay.vue'
import { useI18n } from 'vue-i18n'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'

const { t } = useI18n()
const confirm = useConfirm()
const toast = useToast()

const props = defineProps({
  products:   { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  filters:    { type: Object, default: () => ({}) },
})

const search     = ref(props.filters.search ?? '')
const categoryId = ref(props.filters.category_id ?? '')

const categoryOptions = [
  { value: '', label: 'Todas as categorias' },
  ...props.categories.map(c => ({ value: c.id, label: c.name })),
]

function applyFilters() {
  router.get(route('products.index'), {
    search: search.value || undefined,
    category_id: categoryId.value || undefined,
  }, { preserveState: true, replace: true })
}

function deleteProduct(p) {
  confirm.require({
    header: `Excluir ${p.name}?`,
    message: 'Esta ação não pode ser desfeita.',
    icon: 'pi pi-trash',
    acceptLabel: 'Sim, excluir',
    rejectLabel: 'Não',
    acceptSeverity: 'danger',
    accept: () => router.delete(route('products.destroy', p.id), {
      onSuccess: () => toast.add({ severity: 'success', summary: 'Produto excluído', life: 3000 }),
    }),
  })
}
</script>

<template>
  <AppLayout :title="t('nav.products')">
    <Head :title="t('nav.products')" />

    <PageHeader :title="t('nav.products')" :subtitle="`${products.total ?? 0} produtos`">
      <Link :href="route('products.create')">
        <Button label="Novo produto" icon="pi pi-plus" severity="contrast" size="small" />
      </Link>
    </PageHeader>

    <!-- Filters -->
    <div class="bg-surface-0 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-xl p-4 mb-4 flex flex-wrap gap-3 items-end">
      <div class="flex-1 min-w-48">
        <label class="block text-xs font-medium text-surface-500 mb-1">Buscar</label>
        <InputText v-model="search" placeholder="Nome do produto..." size="small" class="w-full" @keyup.enter="applyFilters" />
      </div>
      <div class="min-w-44">
        <label class="block text-xs font-medium text-surface-500 mb-1">Categoria</label>
        <Select v-model="categoryId" :options="categoryOptions" optionLabel="label" optionValue="value" size="small" class="w-full" />
      </div>
      <Button label="Filtrar" icon="pi pi-filter" size="small" @click="applyFilters" />
    </div>

    <!-- Table -->
    <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 overflow-hidden">
      <DataTable :value="products.data" size="small" stripedRows>
        <template #empty>
          <EmptyState :title="t('common.noData')" icon="pi-box" />
        </template>
        <Column header="Produto">
          <template #body="{ data }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-surface-100 dark:bg-surface-700 overflow-hidden shrink-0">
                <img v-if="data.image_url" :src="data.image_url" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <i class="pi pi-box text-surface-400 text-sm" />
                </div>
              </div>
              <div>
                <Link :href="route('products.edit', data.id)" class="font-medium text-surface-800 dark:text-surface-100 hover:text-orange-600 dark:hover:text-orange-400">
                  {{ data.name }}
                </Link>
                <p v-if="data.sku" class="text-xs text-surface-400">SKU: {{ data.sku }}</p>
              </div>
            </div>
          </template>
        </Column>
        <Column header="Categoria">
          <template #body="{ data }">
            <span class="text-sm text-surface-500">{{ data.category?.name ?? '—' }}</span>
          </template>
        </Column>
        <Column header="Preço">
          <template #body="{ data }">
            <div>
              <span v-if="data.promotional_price" class="text-xs line-through text-surface-400"><MoneyDisplay :value="data.base_price" /></span>
              <span class="font-semibold text-orange-600 dark:text-orange-400 block">
                <MoneyDisplay :value="data.effective_price ?? data.base_price" />
              </span>
            </div>
          </template>
        </Column>
        <Column header="Status">
          <template #body="{ data }">
            <Tag :value="data.is_active ? 'Ativo' : 'Inativo'" :severity="data.is_active ? 'success' : 'secondary'" />
          </template>
        </Column>
        <Column header="" style="width:100px">
          <template #body="{ data }">
            <div class="flex gap-1 justify-end">
              <Link :href="route('products.edit', data.id)">
                <Button icon="pi pi-pencil" size="small" text rounded severity="secondary" />
              </Link>
              <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="deleteProduct(data)" />
            </div>
          </template>
        </Column>
      </DataTable>

      <div v-if="products.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-surface-200 dark:border-surface-700 text-sm text-surface-500">
        <span>Página {{ products.current_page }} de {{ products.last_page }}</span>
        <div class="flex gap-2">
          <Button icon="pi pi-chevron-left" size="small" text severity="secondary" :disabled="products.current_page === 1" @click="router.get(products.prev_page_url)" />
          <Button icon="pi pi-chevron-right" size="small" text severity="secondary" :disabled="products.current_page === products.last_page" @click="router.get(products.next_page_url)" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
