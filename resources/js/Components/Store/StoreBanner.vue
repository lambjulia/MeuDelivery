<script setup>
const props = defineProps({
  company: { type: Object, required: true },
})
</script>

<template>
  <div class="relative w-full bg-gray-200 overflow-hidden" style="height: 180px;">
    <img
      v-if="company.banner_url"
      :src="company.banner_url"
      :alt="company.name"
      class="w-full h-full object-cover"
    />
    <div
      v-else
      class="w-full h-full flex items-center justify-center"
      :style="{ background: `linear-gradient(135deg, ${company.primary_color ?? '#F97316'}, ${company.primary_color ?? '#F97316'}99)` }"
    >
      <span class="text-white text-4xl font-bold tracking-tight opacity-30">{{ company.name }}</span>
    </div>

    <!-- Overlay bottom gradient -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent pointer-events-none" />

    <!-- Store status badge -->
    <div class="absolute bottom-3 left-4">
      <span
        :class="[
          'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold',
          company.is_open
            ? 'bg-green-500 text-white'
            : 'bg-gray-700 text-gray-200',
        ]"
      >
        <span
          :class="['w-1.5 h-1.5 rounded-full', company.is_open ? 'bg-white animate-pulse' : 'bg-gray-400']"
        />
        {{ company.is_open ? 'Aberto' : 'Fechado' }}
      </span>
    </div>
  </div>
</template>
