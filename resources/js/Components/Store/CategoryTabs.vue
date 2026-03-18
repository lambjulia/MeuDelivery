<script setup>
const props = defineProps({
  categories:      { type: Array,  required: true },
  activeCategory:  { type: String, default: null },
})

const emit = defineEmits(['select'])
</script>

<template>
  <div class="bg-white border-b border-gray-100 sticky top-16 z-40">
    <div class="max-w-3xl mx-auto px-4">
      <div class="flex gap-1 overflow-x-auto scrollbar-none py-2">
        <button
          v-for="category in categories"
          :key="category.slug"
          :class="[
            'shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-all whitespace-nowrap',
            activeCategory === category.slug
              ? 'text-white shadow-sm'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
          ]"
          :style="activeCategory === category.slug
            ? { background: 'var(--store-primary, #F97316)' }
            : {}"
          @click="emit('select', category.slug)"
        >
          {{ category.name }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>
