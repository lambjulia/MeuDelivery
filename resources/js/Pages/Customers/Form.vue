<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { useI18n } from 'vue-i18n'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import { useToast } from 'primevue/usetoast'

const { t } = useI18n()
const toast = useToast()

const props = defineProps({
  customer: { type: Object, default: null },
  errors:   { type: Object, default: () => ({}) },
})

const isEdit = computed(() => !!props.customer)
const title  = computed(() => isEdit.value ? 'Editar Cliente' : 'Novo Cliente')

const form = ref({
  name:    props.customer?.name ?? '',
  phone:   props.customer?.phone ?? '',
  email:   props.customer?.email ?? '',
  notes:   props.customer?.notes ?? '',
  addresses: props.customer?.addresses ?? [],
})

function addAddress() {
  form.value.addresses.push({
    street: '', number: '', complement: '', district: '', city: '', state: '', zip_code: '', is_default: form.value.addresses.length === 0,
  })
}

function removeAddress(idx) { form.value.addresses.splice(idx, 1) }

function setDefault(idx) {
  form.value.addresses.forEach((a, i) => { a.is_default = i === idx })
}

function submit() {
  if (isEdit.value) {
    router.put(route('customers.update', props.customer.id), form.value, {
      onSuccess: () => toast.add({ severity: 'success', summary: 'Cliente atualizado!', life: 3000 }),
      onError: () => toast.add({ severity: 'error', summary: 'Verifique os campos', life: 4000 }),
    })
  } else {
    router.post(route('customers.store'), form.value, {
      onError: () => toast.add({ severity: 'error', summary: 'Verifique os campos', life: 4000 }),
    })
  }
}
</script>

<template>
  <AppLayout :title="title">
    <Head :title="title" />

    <PageHeader :title="title" :back="route('customers.index')">
      <Button :label="isEdit ? 'Salvar' : 'Criar cliente'" icon="pi pi-check" severity="contrast" size="small" @click="submit" />
    </PageHeader>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Basic info -->
      <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
        <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Informações básicas</h3>
        <div class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Nome *</label>
            <InputText v-model="form.name" size="small" class="w-full" :class="errors.name ? 'border-red-500' : ''" />
            <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Telefone *</label>
            <InputText v-model="form.phone" size="small" class="w-full" placeholder="(11) 99999-9999" :class="errors.phone ? 'border-red-500' : ''" />
            <p v-if="errors.phone" class="text-xs text-red-500 mt-1">{{ errors.phone }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">E-mail</label>
            <InputText v-model="form.email" size="small" class="w-full" type="email" />
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Observações</label>
            <InputText v-model="form.notes" size="small" class="w-full" />
          </div>
        </div>
      </div>

      <!-- Addresses -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-surface-800 dark:text-surface-100">Endereços</h3>
          <Button label="Adicionar" icon="pi pi-plus" size="small" severity="secondary" outlined @click="addAddress" />
        </div>
        <div class="space-y-4">
          <div
            v-for="(addr, idx) in form.addresses"
            :key="idx"
            class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-4"
          >
            <div class="flex items-center justify-between mb-3">
              <span class="text-sm font-medium text-surface-700 dark:text-surface-200">Endereço {{ idx + 1 }}</span>
              <div class="flex gap-2 items-center">
                <button
                  v-if="!addr.is_default"
                  class="text-xs text-orange-500 hover:underline"
                  @click="setDefault(idx)"
                >Definir como padrão</button>
                <span v-else class="text-xs bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400 px-2 py-0.5 rounded-full">Padrão</span>
                <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="removeAddress(idx)" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div class="col-span-2">
                <label class="block text-xs text-surface-400 mb-1">Rua *</label>
                <InputText v-model="addr.street" size="small" class="w-full" />
              </div>
              <div>
                <label class="block text-xs text-surface-400 mb-1">Número *</label>
                <InputText v-model="addr.number" size="small" class="w-full" />
              </div>
              <div>
                <label class="block text-xs text-surface-400 mb-1">Complemento</label>
                <InputText v-model="addr.complement" size="small" class="w-full" />
              </div>
              <div>
                <label class="block text-xs text-surface-400 mb-1">Bairro *</label>
                <InputText v-model="addr.district" size="small" class="w-full" />
              </div>
              <div>
                <label class="block text-xs text-surface-400 mb-1">CEP</label>
                <InputText v-model="addr.zip_code" size="small" class="w-full" />
              </div>
              <div>
                <label class="block text-xs text-surface-400 mb-1">Cidade *</label>
                <InputText v-model="addr.city" size="small" class="w-full" />
              </div>
              <div>
                <label class="block text-xs text-surface-400 mb-1">Estado *</label>
                <InputText v-model="addr.state" size="small" class="w-full" maxlength="2" />
              </div>
            </div>
          </div>
          <div v-if="!form.addresses.length" class="text-center py-8 text-surface-400 text-sm border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-xl">
            <i class="pi pi-map-marker text-2xl block mb-2" />
            Nenhum endereço cadastrado
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
