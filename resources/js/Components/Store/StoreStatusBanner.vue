<script setup>
const props = defineProps({
  company:  { type: Boolean, default: true },
  is_open:  { type: Boolean, default: true },
})
</script>

<template>
  <div
    v-if="!is_open"
    class="bg-gray-800 text-white text-center py-2.5 px-4 text-sm"
  >
    <i class="pi pi-clock mr-2" />
    Esta loja está <strong>fechada</strong> no momento. Você pode fazer seu pedido e ele será processado quando a loja abrir.
  </div>
</template>
