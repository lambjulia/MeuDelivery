<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { useI18n } from 'vue-i18n'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import InputNumber from 'primevue/inputnumber'
import ToggleSwitch from 'primevue/toggleswitch'
import FileUpload from 'primevue/fileupload'
import { useToast } from 'primevue/usetoast'

const { t } = useI18n()
const toast = useToast()

const props = defineProps({
  product:    { type: Object, default: null },
  categories: { type: Array, default: () => [] },
  errors:     { type: Object, default: () => ({}) },
})

const isEdit = computed(() => !!props.product)

const form = ref({
  name:             props.product?.name ?? '',
  description:      props.product?.description ?? '',
  category_id:      props.product?.category_id ?? null,
  base_price:       props.product?.base_price ?? 0,
  promotional_price:props.product?.promotional_price ?? null,
  sku:              props.product?.sku ?? '',
  preparation_time: props.product?.preparation_time ?? null,
  is_active:        props.product?.is_active ?? true,
  option_groups:    props.product?.optionGroups?.map(g => ({
    id:           g.id ?? null,
    name:         g.name,
    is_required:  g.is_required,
    is_multiple:  g.is_multiple,
    min_selections: g.min_selections,
    max_selections: g.max_selections,
    options: g.options?.map(o => ({ id: o.id ?? null, name: o.name, additional_price: o.additional_price, is_active: o.is_active })) ?? [],
  })) ?? [],
  image: null,
})

const categoryOptions = props.categories.map(c => ({ value: c.id, label: c.name }))

function addOptionGroup() {
  form.value.option_groups.push({
    id: null, name: '', is_required: false, is_multiple: false,
    min_selections: 1, max_selections: 1, options: [],
  })
}

function removeOptionGroup(idx) { form.value.option_groups.splice(idx, 1) }

function addOption(group) {
  group.options.push({ id: null, name: '', additional_price: 0, is_active: true })
}

function removeOption(group, idx) { group.options.splice(idx, 1) }

function onImageSelect(event) { form.value.image = event.files[0] }

function submit() {
  const data = new FormData()
  Object.entries(form.value).forEach(([k, v]) => {
    if (k === 'option_groups') {
      data.append(k, JSON.stringify(v))
    } else if (k === 'image' && v) {
      data.append(k, v)
    } else if (v !== null && v !== '') {
      data.append(k, v)
    }
  })

  if (isEdit.value) {
    data.append('_method', 'PUT')
    router.post(route('products.update', props.product.id), data, {
      onSuccess: () => toast.add({ severity: 'success', summary: 'Produto atualizado!', life: 3000 }),
      onError: () => toast.add({ severity: 'error', summary: 'Verifique os campos', life: 4000 }),
    })
  } else {
    router.post(route('products.store'), data, {
      onError: () => toast.add({ severity: 'error', summary: 'Verifique os campos', life: 4000 }),
    })
  }
}
</script>

<template>
  <AppLayout :title="isEdit ? 'Editar Produto' : 'Novo Produto'">
    <Head :title="isEdit ? 'Editar Produto' : 'Novo Produto'" />

    <PageHeader :title="isEdit ? 'Editar Produto' : 'Novo Produto'" :back="route('products.index')">
      <Button :label="isEdit ? 'Salvar' : 'Criar produto'" icon="pi pi-check" severity="contrast" size="small" @click="submit" />
    </PageHeader>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main info -->
      <div class="lg:col-span-2 space-y-5">
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Informações</h3>
          <div class="space-y-3">
            <div>
              <label class="block text-xs font-medium text-surface-500 mb-1">Nome *</label>
              <InputText v-model="form.name" size="small" class="w-full" :class="errors.name ? 'border-red-500' : ''" />
              <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-surface-500 mb-1">Descrição</label>
              <Textarea v-model="form.description" rows="3" class="w-full text-sm" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-surface-500 mb-1">Categoria *</label>
                <Select v-model="form.category_id" :options="categoryOptions" optionLabel="label" optionValue="value" size="small" class="w-full" :class="errors.category_id ? 'border-red-500' : ''" />
                <p v-if="errors.category_id" class="text-xs text-red-500 mt-1">{{ errors.category_id }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-surface-500 mb-1">SKU</label>
                <InputText v-model="form.sku" size="small" class="w-full" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-surface-500 mb-1">Preço base *</label>
                <InputNumber v-model="form.base_price" mode="currency" currency="BRL" locale="pt-BR" size="small" class="w-full" />
                <p v-if="errors.base_price" class="text-xs text-red-500 mt-1">{{ errors.base_price }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-surface-500 mb-1">Preço promocional</label>
                <InputNumber v-model="form.promotional_price" mode="currency" currency="BRL" locale="pt-BR" size="small" class="w-full" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-surface-500 mb-1">Tempo de preparo (min)</label>
                <InputNumber v-model="form.preparation_time" size="small" class="w-full" :min="0" />
              </div>
              <div class="flex items-end pb-1">
                <label class="text-sm text-surface-600 dark:text-surface-300 mr-3">Ativo</label>
                <ToggleSwitch v-model="form.is_active" />
              </div>
            </div>
          </div>
        </div>

        <!-- Option groups -->
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-surface-800 dark:text-surface-100">Grupos de opções</h3>
            <Button label="Adicionar grupo" icon="pi pi-plus" size="small" severity="secondary" outlined @click="addOptionGroup" />
          </div>
          <div class="space-y-4">
            <div
              v-for="(group, gi) in form.option_groups"
              :key="gi"
              class="border border-surface-200 dark:border-surface-700 rounded-xl p-4"
            >
              <div class="flex items-center gap-3 mb-3">
                <InputText v-model="group.name" placeholder="Nome do grupo (ex: Tamanho)" size="small" class="flex-1" />
                <div class="flex items-center gap-2 text-xs text-surface-500">
                  <label>Obrigatório</label>
                  <ToggleSwitch v-model="group.is_required" />
                </div>
                <div class="flex items-center gap-2 text-xs text-surface-500">
                  <label>Múltiplo</label>
                  <ToggleSwitch v-model="group.is_multiple" />
                </div>
                <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="removeOptionGroup(gi)" />
              </div>
              <div class="space-y-2 mb-3">
                <div
                  v-for="(opt, oi) in group.options"
                  :key="oi"
                  class="flex items-center gap-2"
                >
                  <InputText v-model="opt.name" placeholder="Nome da opção" size="small" class="flex-1" />
                  <InputNumber v-model="opt.additional_price" mode="currency" currency="BRL" locale="pt-BR" size="small" class="w-32" placeholder="Acréscimo" />
                  <ToggleSwitch v-model="opt.is_active" />
                  <Button icon="pi pi-times" size="small" text rounded severity="danger" @click="removeOption(group, oi)" />
                </div>
              </div>
              <Button label="Opção" icon="pi pi-plus" size="small" text severity="secondary" @click="addOption(group)" />
            </div>
            <div v-if="!form.option_groups.length" class="text-center py-6 text-surface-400 text-sm border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-xl">
              Nenhum grupo de opções. Clique em "Adicionar grupo" para começar.
            </div>
          </div>
        </div>
      </div>

      <!-- Image -->
      <div>
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Imagem</h3>
          <div v-if="product?.image_url" class="mb-3 rounded-lg overflow-hidden">
            <img :src="product.image_url" class="w-full h-40 object-cover" />
          </div>
          <FileUpload
            mode="basic"
            accept="image/*"
            :maxFileSize="2000000"
            chooseLabel="Escolher imagem"
            size="small"
            class="w-full"
            @select="onImageSelect"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
