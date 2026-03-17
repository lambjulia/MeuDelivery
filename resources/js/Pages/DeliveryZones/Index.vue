<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import EmptyState from '@/Components/EmptyState.vue'
import MoneyDisplay from '@/Components/MoneyDisplay.vue'
import { useI18n } from 'vue-i18n'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Dialog from 'primevue/dialog'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

const { t } = useI18n()
const toast = useToast()
const confirm = useConfirm()

const props = defineProps({
  zones:  { type: Array, default: () => [] },
  errors: { type: Object, default: () => ({}) },
})

const showDialog = ref(false)
const editItem   = ref(null)

const emptyForm = () => ({ id: null, name: '', neighborhoods: '', delivery_fee: 0, estimated_time: 30, is_active: true })
const form = ref(emptyForm())

function openCreate() { editItem.value = null; form.value = emptyForm(); showDialog.value = true }

function openEdit(z) {
  editItem.value = z
  form.value = {
    id: z.id, name: z.name,
    neighborhoods: Array.isArray(z.neighborhoods) ? z.neighborhoods.join(', ') : (z.neighborhoods ?? ''),
    delivery_fee: z.delivery_fee, estimated_time: z.estimated_time, is_active: z.is_active,
  }
  showDialog.value = true
}

function submit() {
  const payload = {
    ...form.value,
    neighborhoods: form.value.neighborhoods.split(',').map(s => s.trim()).filter(Boolean),
  }
  if (editItem.value) {
    router.put(route('delivery-zones.update', editItem.value.id), payload, {
      onSuccess: () => { toast.add({ severity: 'success', summary: 'Zona atualizada!', life: 3000 }); showDialog.value = false },
    })
  } else {
    router.post(route('delivery-zones.store'), payload, {
      onSuccess: () => { toast.add({ severity: 'success', summary: 'Zona criada!', life: 3000 }); showDialog.value = false },
    })
  }
}

function deleteZone(z) {
  confirm.require({
    header: `Excluir ${z.name}?`,
    message: 'Esta zona será removida.',
    acceptLabel: 'Sim',
    rejectLabel: 'Não',
    acceptSeverity: 'danger',
    accept: () => router.delete(route('delivery-zones.destroy', z.id), {
      onSuccess: () => toast.add({ severity: 'success', summary: 'Zona excluída', life: 3000 }),
    }),
  })
}
</script>

<template>
  <AppLayout :title="t('nav.deliveryZones')">
    <Head :title="t('nav.deliveryZones')" />

    <PageHeader :title="t('nav.deliveryZones')" :subtitle="`${zones.length} zonas`">
      <Button label="Nova zona" icon="pi pi-plus" severity="contrast" size="small" @click="openCreate" />
    </PageHeader>

    <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 overflow-hidden">
      <DataTable :value="zones" size="small" stripedRows>
        <template #empty>
          <EmptyState :title="t('common.noData')" icon="pi-map-marker" />
        </template>
        <Column field="name" header="Nome">
          <template #body="{ data }">
            <span class="font-medium text-surface-800 dark:text-surface-100">{{ data.name }}</span>
          </template>
        </Column>
        <Column header="Bairros">
          <template #body="{ data }">
            <span class="text-sm text-surface-500">
              {{ Array.isArray(data.neighborhoods) ? data.neighborhoods.slice(0,3).join(', ') + (data.neighborhoods.length > 3 ? '...' : '') : data.neighborhoods }}
            </span>
          </template>
        </Column>
        <Column header="Taxa">
          <template #body="{ data }">
            <MoneyDisplay :value="data.delivery_fee" class="font-semibold text-orange-600 dark:text-orange-400" />
          </template>
        </Column>
        <Column header="Tempo est.">
          <template #body="{ data }">
            <span class="text-surface-500">{{ data.estimated_time }} min</span>
          </template>
        </Column>
        <Column header="" style="width:80px">
          <template #body="{ data }">
            <div class="flex gap-1 justify-end">
              <Button icon="pi pi-pencil" size="small" text rounded severity="secondary" @click="openEdit(data)" />
              <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="deleteZone(data)" />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Dialog -->
    <Dialog v-model:visible="showDialog" :header="editItem ? 'Editar Zona' : 'Nova Zona de Entrega'" modal class="w-full max-w-md">
      <div class="space-y-3 pt-2">
        <div>
          <label class="block text-xs font-medium text-surface-500 mb-1">Nome *</label>
          <InputText v-model="form.name" size="small" class="w-full" placeholder="Centro, Zona Sul..." />
        </div>
        <div>
          <label class="block text-xs font-medium text-surface-500 mb-1">Bairros (separados por vírgula)</label>
          <Textarea v-model="form.neighborhoods" rows="3" class="w-full text-sm" placeholder="Jardim América, Vila Nova, Centro..." />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Taxa de entrega *</label>
            <InputNumber v-model="form.delivery_fee" mode="currency" currency="BRL" locale="pt-BR" size="small" class="w-full" />
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Tempo est. (min)</label>
            <InputNumber v-model="form.estimated_time" size="small" class="w-full" :min="0" />
          </div>
        </div>
      </div>
      <template #footer>
        <Button label="Cancelar" severity="secondary" outlined size="small" @click="showDialog = false" />
        <Button :label="editItem ? 'Salvar' : 'Criar'" icon="pi pi-check" severity="contrast" size="small" @click="submit" />
      </template>
    </Dialog>
  </AppLayout>
</template>
