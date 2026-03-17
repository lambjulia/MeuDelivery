<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { useI18n } from 'vue-i18n'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import ToggleSwitch from 'primevue/toggleswitch'
import FileUpload from 'primevue/fileupload'
import { useToast } from 'primevue/usetoast'

const { t } = useI18n()
const toast = useToast()

const props = defineProps({
  company: { type: Object, required: true },
  errors:  { type: Object, default: () => ({}) },
})

const form = ref({
  name:                      props.company.name ?? '',
  phone:                     props.company.phone ?? '',
  email:                     props.company.email ?? '',
  address:                   props.company.address ?? '',
  description:               props.company.description ?? '',
  minimum_order_amount:      props.company.minimum_order_amount ?? 0,
  is_open:                   props.company.is_open ?? true,
  accepted_payment_methods:  props.company.accepted_payment_methods ?? [],
  logo: null,
})

const paymentMethods = [
  { value: 'cash',        label: 'Dinheiro' },
  { value: 'credit_card', label: 'Cartão de crédito' },
  { value: 'debit_card',  label: 'Cartão de débito' },
  { value: 'pix',         label: 'PIX' },
  { value: 'voucher',     label: 'Voucher' },
]

function togglePayment(method) {
  const idx = form.value.accepted_payment_methods.indexOf(method)
  if (idx === -1) form.value.accepted_payment_methods.push(method)
  else form.value.accepted_payment_methods.splice(idx, 1)
}

function onLogoSelect(e) { form.value.logo = e.files[0] }

function submit() {
  const data = new FormData()
  Object.entries(form.value).forEach(([k, v]) => {
    if (k === 'accepted_payment_methods') {
      data.append(k, JSON.stringify(v))
    } else if (k === 'logo' && v) {
      data.append(k, v)
    } else if (v !== null) {
      data.append(k, v)
    }
  })
  data.append('_method', 'PUT')

  router.post(route('settings.company.update'), data, {
    onSuccess: () => toast.add({ severity: 'success', summary: 'Configurações salvas!', life: 3000 }),
    onError: () => toast.add({ severity: 'error', summary: 'Verifique os campos', life: 4000 }),
  })
}
</script>

<template>
  <AppLayout :title="t('nav.settings')">
    <Head :title="t('nav.settings')" />

    <PageHeader :title="t('nav.settings')" subtitle="Configurações da empresa">
      <Button label="Salvar configurações" icon="pi pi-check" severity="contrast" size="small" @click="submit" />
    </PageHeader>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Basic info -->
      <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
        <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Informações da empresa</h3>
        <div class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Nome da empresa *</label>
            <InputText v-model="form.name" size="small" class="w-full" :class="errors.name ? 'border-red-500' : ''" />
            <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
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
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Endereço</label>
            <InputText v-model="form.address" size="small" class="w-full" />
          </div>
          <div>
            <label class="block text-xs font-medium text-surface-500 mb-1">Descrição</label>
            <Textarea v-model="form.description" rows="3" class="w-full text-sm" />
          </div>
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 flex-1">
              <ToggleSwitch v-model="form.is_open" />
              <label class="text-sm text-surface-700 dark:text-surface-200">
                {{ form.is_open ? 'Loja aberta' : 'Loja fechada' }}
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Logo & Payment -->
      <div class="space-y-5">
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Logo</h3>
          <div v-if="company.logo_url" class="mb-3 rounded-xl overflow-hidden border border-surface-200 dark:border-surface-700 w-32 h-32 flex items-center justify-center bg-surface-50 dark:bg-surface-900">
            <img :src="company.logo_url" class="max-w-full max-h-full object-contain" />
          </div>
          <FileUpload
            mode="basic"
            accept="image/*"
            :maxFileSize="2000000"
            chooseLabel="Trocar logo"
            size="small"
            class="w-full"
            @select="onLogoSelect"
          />
        </div>

        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Formas de pagamento aceitas</h3>
          <div class="space-y-2">
            <div
              v-for="method in paymentMethods"
              :key="method.value"
              class="flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-colors"
              :class="form.accepted_payment_methods.includes(method.value)
                ? 'border-orange-300 bg-orange-50 dark:bg-orange-500/10 dark:border-orange-500/30'
                : 'border-surface-200 dark:border-surface-700 hover:border-surface-300'"
              @click="togglePayment(method.value)"
            >
              <span class="text-sm font-medium text-surface-700 dark:text-surface-200">{{ method.label }}</span>
              <div
                class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                :class="form.accepted_payment_methods.includes(method.value)
                  ? 'border-orange-500 bg-orange-500'
                  : 'border-surface-300 dark:border-surface-600'"
              >
                <i v-if="form.accepted_payment_methods.includes(method.value)" class="pi pi-check text-white" style="font-size:0.6rem" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
