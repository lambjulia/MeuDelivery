<script setup>
import { useTheme } from '@/composables/useTheme'
import Button from 'primevue/button'

const { isDark, toggle } = useTheme()
</script>

<template>
  <Button
    :icon="isDark ? 'pi pi-sun' : 'pi pi-moon'"
    rounded
    text
    size="small"
    severity="secondary"
    @click="toggle"
    :aria-label="isDark ? 'Light mode' : 'Dark mode'"
  />
</template>
