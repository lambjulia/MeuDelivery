<script setup>
import { computed, onMounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useStoreTheme } from '@/composables/useStoreTheme'
import { useCartStore } from '@/stores/cart'
import Toast from 'primevue/toast'

const props = defineProps({
  company: { type: Object, required: true },
  title:   { type: String, default: '' },
})

const companyRef  = computed(() => props.company)
const { applyTheme } = useStoreTheme(companyRef)
const cart = useCartStore()

const pageTitle = computed(() =>
  props.title
    ? `${props.title} — ${props.company.name}`
    : props.company.name
)

onMounted(() => {
  applyTheme(props.company.primary_color)
  cart.init(props.company.slug)
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 font-sans">
    <Head :title="pageTitle" />
    <Toast position="top-center" />
    <slot />
  </div>
</template>
