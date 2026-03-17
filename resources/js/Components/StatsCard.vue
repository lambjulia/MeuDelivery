<script setup>
defineProps({
  title:  { type: String, required: true },
  value:  { type: [String, Number], required: true },
  icon:   { type: String, required: true },
  color:  { type: String, default: 'orange' }, // orange | blue | green | red | purple
  trend:  { type: Number, default: null },
  suffix: { type: String, default: '' },
})

const colorMap = {
  orange: 'bg-orange-100 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400',
  blue:   'bg-blue-100   dark:bg-blue-500/10   text-blue-600   dark:text-blue-400',
  green:  'bg-green-100  dark:bg-green-500/10  text-green-600  dark:text-green-400',
  red:    'bg-red-100    dark:bg-red-500/10    text-red-600    dark:text-red-400',
  purple: 'bg-purple-100 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400',
}
</script>

<template>
  <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5 flex items-start gap-4 hover:shadow-md dark:hover:shadow-surface-900/50 transition-shadow">
    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center shrink-0', colorMap[color] ?? colorMap.orange]">
      <i :class="['pi text-xl', icon]" />
    </div>
    <div class="flex-1 min-w-0">
      <p class="text-sm text-surface-500 dark:text-surface-400 mb-1 truncate">{{ title }}</p>
      <p class="text-2xl font-bold text-surface-900 dark:text-surface-0 leading-none">
        {{ value }}<span v-if="suffix" class="text-base font-normal ml-1">{{ suffix }}</span>
      </p>
      <p v-if="trend !== null" class="text-xs mt-1" :class="trend >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
        <i :class="['pi', trend >= 0 ? 'pi-arrow-up' : 'pi-arrow-down']" />
        {{ Math.abs(trend).toFixed(1) }}% vs período anterior
      </p>
    </div>
  </div>
</template>
