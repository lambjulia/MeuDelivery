<script setup>
import { useI18n } from 'vue-i18n'
import Select from 'primevue/select'
import { ref, watch } from 'vue'

const { locale } = useI18n()

const locales = [
  { value: 'pt_BR', label: '🇧🇷 PT' },
  { value: 'en',    label: '🇺🇸 EN' },
  { value: 'es',    label: '🇪🇸 ES' },
]

const selected = ref(locale.value)

watch(selected, (val) => {
  locale.value = val
  localStorage.setItem('locale', val)
})
</script>

<template>
  <Select
    v-model="selected"
    :options="locales"
    optionLabel="label"
    optionValue="value"
    size="small"
    class="w-24"
  />
</template>
