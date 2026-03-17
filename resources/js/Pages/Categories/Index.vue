<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useI18n } from 'vue-i18n'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import ToggleSwitch from 'primevue/toggleswitch'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

const { t } = useI18n()
const toast = useToast()
const confirm = useConfirm()

const props = defineProps({
  categories: { type: Array, default: () => [] },
  errors:     { type: Object, default: () => ({}) },
})

const showDialog  = ref(false)
const editItem    = ref(null)
const imageFile   = ref(null)

const emptyForm = () => ({ id: null, name: '', description: '', sort_order: 0, is_active: true, image: null })
const form = ref(emptyForm())

function openCreate() {
  editItem.value = null
  form.value = emptyForm()
  showDialog.value = true
}

function openEdit(cat) {
  editItem.value = cat
  form.value = { id: cat.id, name: cat.name, description: cat.description ?? '', sort_order: cat.sort_order ?? 0, is_active: cat.is_active, image: null }
  showDialog.value = true
}

function submit() {
  const data = new FormData()
  data.append('name', form.value.name)
  data.append('description', form.value.description || '')
  data.append('sort_order', form.value.sort_order)
  data.append('is_active', form.value.is_active ? '1' : '0')
  if (form.value.image) data.append('image', form.value.image)

  if (editItem.value) {
    data.append('_method', 'PUT')
    router.post(route('categories.update', editItem.value.id), data, {
      onSuccess: () => { toast.add({ severity: 'success', summary: 'Categoria atualizada!', life: 3000 }); showDialog.value = false },
      onError: () => toast.add({ severity: 'error', summary: 'Verifique os campos', life: 4000 }),
    })
  } else {
    router.post(route('categories.store'), data, {
      onSuccess: () => { toast.add({ severity: 'success', summary: 'Categoria criada!', life: 3000 }); showDialog.value = false },
      onError: () => toast.add({ severity: 'error', summary: 'Verifique os campos', life: 4000 }),
    })
  }
}

function deleteCategory(cat) {
  confirm.require({
    header: `Excluir ${cat.name}?`,
    message: 'Os produtos desta categoria não serão excluídos.',
    icon: 'pi pi-trash',
    acceptLabel: 'Sim, excluir',
    rejectLabel: 'Não',
    acceptSeverity: 'danger',
    accept: () => router.delete(route('categories.destroy', cat.id), {
      onSuccess: () => toast.add({ severity: 'success', summary: 'Categoria excluída', life: 3000 }),
    }),
  })
}
</script>

<template>
  <AppLayout :title="t('nav.categories')">
    <Head :title="t('nav.categories')" />

    <PageHeader :title="t('nav.categories')" :subtitle="`${categories.length} categorias`">
      <Button label="Nova categoria" icon="pi pi-plus" severity="contrast" size="small" @click="openCreate" />
    </PageHeader>

    <div v-if="!categories.length">
      <EmptyState :title="t('common.noData')" description="Crie sua primeira categoria para organizar os produtos." icon="pi-tags">
        <template #action>
          <Button label="Nova categoria" icon="pi pi-plus" severity="contrast" size="small" @click="openCreate" />
        </template>
      </EmptyState>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div
        v-for="cat in categories"
        :key="cat.id"
        class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 overflow-hidden group"
      >
        <!-- Image -->
        <div class="h-28 bg-surface-100 dark:bg-surface-700 overflow-hidden">
          <img v-if="cat.image_url" :src="cat.image_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
          <div v-else class="w-full h-full flex items-center justify-center">
            <i class="pi pi-tags text-3xl text-surface-300 dark:text-surface-600" />
          </div>
        </div>
        <!-- Content -->
        <div class="p-4">
          <div class="flex items-start justify-between gap-2">
            <div>
              <h3 class="font-semibold text-surface-800 dark:text-surface-100">{{ cat.name }}</h3>
              <p v-if="cat.description" class="text-xs text-surface-500 mt-0.5 line-clamp-2">{{ cat.description }}</p>
            </div>
            <Tag :value="cat.is_active ? 'Ativo' : 'Inativo'" :severity="cat.is_active ? 'success' : 'secondary'" />
          </div>
          <div class="flex gap-2 mt-3 pt-3 border-t border-surface-100 dark:border-surface-700">
            <Button icon="pi pi-pencil" label="Editar" size="small" text severity="secondary" class="flex-1" @click="openEdit(cat)" />
            <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="deleteCategory(cat)" />
          </div>
        </div>
      </div>
    </div>

    <!-- Dialog -->
    <Dialog v-model:visible="showDialog" :header="editItem ? 'Editar Categoria' : 'Nova Categoria'" modal class="w-full max-w-md">
      <div class="space-y-3 pt-2">
        <div>
          <label class="block text-xs font-medium text-surface-500 mb-1">Nome *</label>
          <InputText v-model="form.name" size="small" class="w-full" />
        </div>
        <div>
          <label class="block text-xs font-medium text-surface-500 mb-1">Descrição</label>
          <Textarea v-model="form.description" rows="2" class="w-full text-sm" />
        </div>
        <div class="flex items-center gap-3">
          <div class="flex-1">
            <label class="block text-xs font-medium text-surface-500 mb-1">Ordem</label>
            <InputText v-model="form.sort_order" type="number" size="small" class="w-full" />
          </div>
          <div class="flex items-center gap-2 mt-4">
            <label class="text-sm text-surface-600 dark:text-surface-300">Ativa</label>
            <ToggleSwitch v-model="form.is_active" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-surface-500 mb-1">Imagem</label>
          <input type="file" accept="image/*" class="w-full text-sm text-surface-500" @change="e => form.image = e.target.files[0]" />
        </div>
      </div>
      <template #footer>
        <Button label="Cancelar" severity="secondary" outlined size="small" @click="showDialog = false" />
        <Button :label="editItem ? 'Salvar' : 'Criar'" icon="pi pi-check" severity="contrast" size="small" @click="submit" />
      </template>
    </Dialog>
  </AppLayout>
</template>
