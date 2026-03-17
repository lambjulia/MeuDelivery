<script setup>
defineProps({
  title:       { type: String, required: true },
  description: { type: String, default: '' },
  icon:        { type: String, default: 'pi-inbox' },
})
</script>

<template>
  <div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="w-16 h-16 rounded-full bg-surface-100 dark:bg-surface-700 flex items-center justify-center mb-4">
      <i :class="['pi text-3xl text-surface-400', icon]" />
    </div>
    <h3 class="text-lg font-semibold text-surface-700 dark:text-surface-200 mb-2">{{ title }}</h3>
    <p v-if="description" class="text-sm text-surface-400 dark:text-surface-500 max-w-xs">{{ description }}</p>
    <div v-if="$slots.action" class="mt-4">
      <slot name="action" />
    </div>
  </div>
</template>
