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
import Select from 'primevue/select'
import ToggleSwitch from 'primevue/toggleswitch'
import Dialog from 'primevue/dialog'
import Tag from 'primevue/tag'
import DatePicker from 'primevue/datepicker'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

const { t } = useI18n()
const { formatDate } = useDateFormat()
const toast = useToast()
const confirm = useConfirm()

const props = defineProps({
  coupons: { type: Array, default: () => [] },
  errors:  { type: Object, default: () => ({}) },
})

const showDialog = ref(false)
const editItem   = ref(null)

const emptyForm = () => ({
  id: null, code: '', type: 'percentage', value: 10,
  min_order_amount: null, max_discount: null, max_uses: null, expires_at: null, is_active: true,
})
const form = ref(emptyForm())

const typeOptions = [
  { value: 'percentage', label: 'Percentual (%)' },
  { value: 'fixed',      label: 'Valor fixo (R$)' },
]

function openCreate() { editItem.value = null; form.value = emptyForm(); showDialog.value = true }

function openEdit(c) {
  editItem.value = c
  form.value = {
    id: c.id, code: c.code, type: c.type, value: c.value,
    min_order_amount: c.min_order_amount, max_discount: c.max_discount,
    max_uses: c.max_uses, expires_at: c.expires_at ? new Date(c.expires_at) : null, is_active: c.is_active,
  }
  showDialog.value = true
}

function submit() {
  const payload = {
    ...form.value,
    expires_at: form.value.expires_at ? form.value.expires_at.toISOString().split('T')[0] : null,
  }
  if (editItem.value) {
    router.put(route('coupons.update', editItem.value.id), payload, {
      onSuccess: () => { toast.add({ severity: 'success', summary: 'Cupom atualizado!', life: 3000 }); showDialog.value = false },
    })
  } else {
    router.post(route('coupons.store'), payload, {
      onSuccess: () => { toast.add({ severity: 'success', summary: 'Cupom criado!', life: 3000 }); showDialog.value = false },
    })
  }
}

function deleteCoupon(c) {
  confirm.require({
    header: `Excluir cupom ${c.code}?`,
    acceptLabel: 'Sim',
    rejectLabel: 'Não',
    acceptSeverity: 'danger',
    accept: () => router.delete(route('coupons.destroy', c.id), {
      onSuccess: () => toast.add({ severity: 'success', summary: 'Excluído', life: 3000 }),
    }),
  })
}
</script>

<template>
  <AppLayout :title="t('nav.coupons')">
    <Head :title="t('nav.coupons')" />

    <PageHeader :title="t('nav.coupons')" :subtitle="`${coupons.length} cupons`">
      <Button label="Novo cupom" icon="pi pi-plus" severity="contrast" size="small" @click="openCreate" />
    </PageHeader>

    <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 overflow-hidden">
      <DataTable :value="coupons" size="small" stripedRows>
        <template #empty>
          <EmptyState :title="t('common.noData')" icon="pi-ticket" />
        </template>
        <Column header="Código">
          <template #body="{ data }">
            <span class="font-mono font-bold text-orange-600 dark:text-orange-400">{{ data.code }}</span>
          </template>
        </Column>
        <Column header="Tipo / Valor">
          <template #body="{ data }">
            <span class="font-semibold text-surface-800 dark:text-surface-100">
              {{ data.type === 'percentage' ? data.value + '%' : '' }}
              <MoneyDisplay v-if="data.type === 'fixed'" :value="data.value" />
            </span>
          </template>
        </Column>
        <Column header="Usos">
          <template #body="{ data }">
            <span class="text-surface-500">{{ data.uses_count }} / {{ data.max_uses ?? '∞' }}</span>
          </template>
        </Column>
        <Column header="Expira em">
          <template #body="{ data }">
            <span class="text-surface-500">{{ data.expires_at ? data.expires_at.split('T')[0] : '—' }}</span>
          </template>
        </Column>
        <Column header="Status">
          <template #body="{ data }">
            <Tag :value="data.is_active ? 'Ativo' : 'Inativo'" :severity="data.is_active ? 'success' : 'secondary'" />
          </template>
        </Column>
        <Column header="" style="width:80px">
          <template #body="{ data }">
            <div class="flex gap-1 justify-end">
              <Button icon="pi pi-pencil" size="small" text rounded severity="secondary" @click="openEdit(data)" />
              <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="deleteCoupon(data)" />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <Dialog v-model:visible="showDialog" :header="editItem ? 'Editar Cupom' : 'Novo Cupom'" modal class="w-full max-w-md">
      <div class="space-y-3 pt-2">
        <div>
          <label class="block text-xs font-medium text-surface-500 mb-1">Código *</label>
          <InputText v-model="form.code" size="small" class="w-full uppercase" placeholder="PROMO10" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Tipo *</label>
            <Select v-model="form.type" :options="typeOptions" optionLabel="label" optionValue="value" size="small" class="w-full" />
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Valor *</label>
            <InputNumber
              v-model="form.value"
              :mode="form.type === 'fixed' ? 'currency' : 'decimal'"
              :currency="form.type === 'fixed' ? 'BRL' : undefined"
              :locale="form.type === 'fixed' ? 'pt-BR' : undefined"
              :suffix="form.type === 'percentage' ? '%' : ''"
              size="small"
              class="w-full"
            />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Pedido mínimo</label>
            <InputNumber v-model="form.min_order_amount" mode="currency" currency="BRL" locale="pt-BR" size="small" class="w-full" />
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Desconto máx.</label>
            <InputNumber v-model="form.max_discount" mode="currency" currency="BRL" locale="pt-BR" size="small" class="w-full" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Limite de usos</label>
            <InputNumber v-model="form.max_uses" size="small" class="w-full" :min="1" />
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Expira em</label>
            <DatePicker v-model="form.expires_at" dateFormat="dd/mm/yy" size="small" class="w-full" showIcon />
          </div>
        </div>
        <div class="flex items-center gap-2">
          <ToggleSwitch v-model="form.is_active" />
          <label class="text-sm text-surface-600 dark:text-surface-300">Ativo</label>
        </div>
      </div>
      <template #footer>
        <Button label="Cancelar" severity="secondary" outlined size="small" @click="showDialog = false" />
        <Button :label="editItem ? 'Salvar' : 'Criar'" icon="pi pi-check" severity="contrast" size="small" @click="submit" />
      </template>
    </Dialog>
  </AppLayout>
</template>
