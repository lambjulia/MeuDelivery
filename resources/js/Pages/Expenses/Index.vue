<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import EmptyState from '@/Components/EmptyState.vue'
import MoneyDisplay from '@/Components/MoneyDisplay.vue'
import { useI18n } from 'vue-i18n'
import { useDateFormat } from '@/composables/useDateFormat'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import DatePicker from 'primevue/datepicker'
import Dialog from 'primevue/dialog'
import Select from 'primevue/select'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

const { t } = useI18n()
const { format: fmtDate } = useDateFormat()
const toast = useToast()
const confirm = useConfirm()

const props = defineProps({
  expenses: { type: Object, required: true },
  filters:  { type: Object, default: () => ({}) },
})

const showDialog = ref(false)
const editItem   = ref(null)

const emptyForm = () => ({
  id: null, category: '', description: '', amount: 0,
  occurred_at: new Date(), notes: '',
})
const form = ref(emptyForm())

const categoryOptions = [
  { value: 'supplies',    label: 'Insumos' },
  { value: 'rent',        label: 'Aluguel' },
  { value: 'utilities',   label: 'Serviços (água, luz...)' },
  { value: 'salaries',    label: 'Salários' },
  { value: 'equipment',   label: 'Equipamentos' },
  { value: 'marketing',   label: 'Marketing' },
  { value: 'maintenance', label: 'Manutenção' },
  { value: 'other',       label: 'Outros' },
]

function openCreate() { editItem.value = null; form.value = emptyForm(); showDialog.value = true }

function openEdit(e) {
  editItem.value = e
  form.value = {
    id: e.id, category: e.category, description: e.description ?? '', amount: e.amount,
    occurred_at: new Date(e.occurred_at), notes: e.notes ?? '',
  }
  showDialog.value = true
}

function submit() {
  const payload = {
    ...form.value,
    occurred_at: form.value.occurred_at ? form.value.occurred_at.toISOString().split('T')[0] : null,
  }
  if (editItem.value) {
    router.put(route('expenses.update', editItem.value.id), payload, {
      onSuccess: () => { toast.add({ severity: 'success', summary: 'Despesa atualizada!', life: 3000 }); showDialog.value = false },
    })
  } else {
    router.post(route('expenses.store'), payload, {
      onSuccess: () => { toast.add({ severity: 'success', summary: 'Despesa registrada!', life: 3000 }); showDialog.value = false },
    })
  }
}

function deleteExpense(e) {
  confirm.require({
    header: 'Excluir despesa?',
    acceptLabel: 'Sim',
    rejectLabel: 'Não',
    acceptSeverity: 'danger',
    accept: () => router.delete(route('expenses.destroy', e.id), {
      onSuccess: () => toast.add({ severity: 'success', summary: 'Excluída', life: 3000 }),
    }),
  })
}
</script>

<template>
  <AppLayout :title="t('nav.expenses')">
    <Head :title="t('nav.expenses')" />

    <PageHeader :title="t('nav.expenses')" :subtitle="`${expenses.total ?? 0} lançamentos`">
      <Button label="Nova despesa" icon="pi pi-plus" severity="contrast" size="small" @click="openCreate" />
    </PageHeader>

    <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 overflow-hidden">
      <DataTable :value="expenses.data" size="small" stripedRows>
        <template #empty>
          <EmptyState :title="t('common.noData')" icon="pi-wallet" />
        </template>
        <Column header="Categoria">
          <template #body="{ data }">
            <span class="text-sm font-medium text-surface-700 dark:text-surface-200 capitalize">
              {{ categoryOptions.find(c => c.value === data.category)?.label ?? data.category }}
            </span>
          </template>
        </Column>
        <Column field="description" header="Descrição">
          <template #body="{ data }">
            <span class="text-surface-600 dark:text-surface-300">{{ data.description ?? '—' }}</span>
          </template>
        </Column>
        <Column header="Valor">
          <template #body="{ data }">
            <MoneyDisplay :value="data.amount" class="font-semibold text-red-600 dark:text-red-400" />
          </template>
        </Column>
        <Column header="Data">
          <template #body="{ data }">
            <span class="text-surface-500">{{ data.occurred_at?.split('T')[0] }}</span>
          </template>
        </Column>
        <Column header="" style="width:80px">
          <template #body="{ data }">
            <div class="flex gap-1 justify-end">
              <Button icon="pi pi-pencil" size="small" text rounded severity="secondary" @click="openEdit(data)" />
              <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="deleteExpense(data)" />
            </div>
          </template>
        </Column>
      </DataTable>

      <div v-if="expenses.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-surface-200 dark:border-surface-700 text-sm text-surface-500">
        <span>Página {{ expenses.current_page }} de {{ expenses.last_page }}</span>
        <div class="flex gap-2">
          <Button icon="pi pi-chevron-left" size="small" text severity="secondary" :disabled="expenses.current_page === 1" @click="router.get(expenses.prev_page_url)" />
          <Button icon="pi pi-chevron-right" size="small" text severity="secondary" :disabled="expenses.current_page === expenses.last_page" @click="router.get(expenses.next_page_url)" />
        </div>
      </div>
    </div>

    <Dialog v-model:visible="showDialog" :header="editItem ? 'Editar Despesa' : 'Nova Despesa'" modal class="w-full max-w-md">
      <div class="space-y-3 pt-2">
        <div>
          <label class="block text-xs font-medium text-surface-500 mb-1">Categoria *</label>
          <Select v-model="form.category" :options="categoryOptions" optionLabel="label" optionValue="value" size="small" class="w-full" />
        </div>
        <div>
          <label class="block text-xs font-medium text-surface-500 mb-1">Descrição</label>
          <InputText v-model="form.description" size="small" class="w-full" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Valor *</label>
            <InputNumber v-model="form.amount" mode="currency" currency="BRL" locale="pt-BR" size="small" class="w-full" />
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Data *</label>
            <DatePicker v-model="form.occurred_at" dateFormat="dd/mm/yy" size="small" class="w-full" showIcon />
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-surface-500 mb-1">Observações</label>
          <Textarea v-model="form.notes" rows="2" class="w-full text-sm" />
        </div>
      </div>
      <template #footer>
        <Button label="Cancelar" severity="secondary" outlined size="small" @click="showDialog = false" />
        <Button :label="editItem ? 'Salvar' : 'Registrar'" icon="pi pi-check" severity="contrast" size="small" @click="submit" />
      </template>
    </Dialog>
  </AppLayout>
</template>
