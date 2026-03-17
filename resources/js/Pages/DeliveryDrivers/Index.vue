<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useI18n } from 'vue-i18n'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Dialog from 'primevue/dialog'
import Tag from 'primevue/tag'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

const { t } = useI18n()
const toast = useToast()
const confirm = useConfirm()

const props = defineProps({
  drivers: { type: Array, default: () => [] },
  errors:  { type: Object, default: () => ({}) },
})

const showDialog = ref(false)
const editItem   = ref(null)

const emptyForm = () => ({ id: null, name: '', phone: '', email: '', vehicle_type: '', license_plate: '', employment_type: 'employee', status: 'offline' })
const form = ref(emptyForm())

const statusOptions = [
  { value: 'available', label: 'Disponível' },
  { value: 'busy',      label: 'Ocupado' },
  { value: 'offline',   label: 'Offline' },
]

const statusSeverity = { available: 'success', busy: 'warn', offline: 'secondary' }
const statusLabel    = { available: 'Disponível', busy: 'Ocupado', offline: 'Offline' }

const employmentOptions = [
  { value: 'employee',   label: 'CLT' },
  { value: 'freelancer', label: 'Freelancer' },
]

function openCreate() { editItem.value = null; form.value = emptyForm(); showDialog.value = true }

function openEdit(d) {
  editItem.value = d
  form.value = { id: d.id, name: d.name ?? '', phone: d.phone ?? '', email: d.email ?? '', vehicle_type: d.vehicle_type ?? '', license_plate: d.license_plate ?? '', employment_type: d.employment_type ?? 'employee', status: d.status ?? 'offline' }
  showDialog.value = true
}

function submit() {
  if (editItem.value) {
    router.put(route('delivery-drivers.update', editItem.value.id), form.value, {
      onSuccess: () => { toast.add({ severity: 'success', summary: 'Entregador atualizado!', life: 3000 }); showDialog.value = false },
      onError: () => toast.add({ severity: 'error', summary: 'Verifique os campos', life: 4000 }),
    })
  } else {
    router.post(route('delivery-drivers.store'), form.value, {
      onSuccess: () => { toast.add({ severity: 'success', summary: 'Entregador criado!', life: 3000 }); showDialog.value = false },
      onError: () => toast.add({ severity: 'error', summary: 'Verifique os campos', life: 4000 }),
    })
  }
}

function deleteDriver(d) {
  confirm.require({
    header: `Excluir entregador?`,
    message: `${d.user?.name}`,
    icon: 'pi pi-trash',
    acceptLabel: 'Sim',
    rejectLabel: 'Não',
    acceptSeverity: 'danger',
    accept: () => router.delete(route('delivery-drivers.destroy', d.id), {
      onSuccess: () => toast.add({ severity: 'success', summary: 'Excluído', life: 3000 }),
    }),
  })
}
</script>

<template>
  <AppLayout :title="t('nav.deliveryDrivers')">
    <Head :title="t('nav.deliveryDrivers')" />

    <PageHeader :title="t('nav.deliveryDrivers')" :subtitle="`${drivers.length} entregadores`">
      <Button label="Novo entregador" icon="pi pi-plus" severity="contrast" size="small" @click="openCreate" />
    </PageHeader>

    <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 overflow-hidden">
      <DataTable :value="drivers" size="small" stripedRows>
        <template #empty>
          <EmptyState :title="t('common.noData')" icon="pi-truck" />
        </template>
        <Column header="Nome">
          <template #body="{ data }">
            <span class="font-medium text-surface-800 dark:text-surface-100">{{ data.name }}</span>
          </template>
        </Column>
        <Column header="Telefone">
          <template #body="{ data }">
            <span class="text-surface-500">{{ data.phone }}</span>
          </template>
        </Column>
        <Column header="Veículo">
          <template #body="{ data }">
            <span class="text-surface-600 dark:text-surface-300">{{ data.vehicle_type }}</span>
            <span v-if="data.license_plate" class="text-xs text-surface-400 ml-1">({{ data.license_plate }})</span>
          </template>
        </Column>
        <Column header="Tipo">
          <template #body="{ data }">
            <span class="text-sm text-surface-500 capitalize">{{ data.employment_type }}</span>
          </template>
        </Column>
        <Column header="Status">
          <template #body="{ data }">
            <Tag :value="statusLabel[data.status]" :severity="statusSeverity[data.status]" />
          </template>
        </Column>
        <Column header="" style="width:80px">
          <template #body="{ data }">
            <div class="flex gap-1 justify-end">
              <Button icon="pi pi-pencil" size="small" text rounded severity="secondary" @click="openEdit(data)" />
              <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="deleteDriver(data)" />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Dialog -->
    <Dialog v-model:visible="showDialog" :header="editItem ? 'Editar Entregador' : 'Novo Entregador'" modal class="w-full max-w-md">
      <div class="space-y-3 pt-2">
        <div>
          <label class="block text-xs font-medium text-surface-500 mb-1">Nome *</label>
          <InputText v-model="form.name" size="small" class="w-full" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Telefone</label>
            <InputText v-model="form.phone" size="small" class="w-full" />
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">E-mail</label>
            <InputText v-model="form.email" size="small" class="w-full" type="email" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Tipo de veículo</label>
            <InputText v-model="form.vehicle_type" size="small" class="w-full" placeholder="Moto, Carro..." />
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Placa</label>
            <InputText v-model="form.license_plate" size="small" class="w-full" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Vínculo</label>
            <Select v-model="form.employment_type" :options="employmentOptions" optionLabel="label" optionValue="value" size="small" class="w-full" />
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Status</label>
            <Select v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" size="small" class="w-full" />
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
