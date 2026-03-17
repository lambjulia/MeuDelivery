<script setup>
defineProps({
  title:    { type: String, required: true },
  subtitle: { type: String, default: '' },
  back:     { type: String, default: '' },
})
</script>

<template>
  <div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
      <a
        v-if="back"
        :href="back"
        class="p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-500 transition-colors"
      >
        <i class="pi pi-arrow-left text-sm" />
      </a>
      <div>
        <h1 class="text-xl font-bold text-surface-900 dark:text-surface-0">{{ title }}</h1>
        <p v-if="subtitle" class="text-sm text-surface-500 dark:text-surface-400 mt-0.5">{{ subtitle }}</p>
      </div>
    </div>
    <div v-if="$slots.default" class="flex items-center gap-2">
      <slot />
    </div>
  </div>
</template>
