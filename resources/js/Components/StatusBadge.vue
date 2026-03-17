<script setup>
import { computed } from 'vue'
import Tag from 'primevue/tag'
import { useOrderStatus } from '@/composables/useOrderStatus'

const props = defineProps({
  status: { type: String, required: true },
  size: { type: String, default: 'normal' }, // 'small' | 'normal'
})

const { getLabel, getSeverity, getIcon } = useOrderStatus()

const severity = computed(() => getSeverity(props.status))
const label    = computed(() => getLabel(props.status))
const icon     = computed(() => 'pi ' + getIcon(props.status))
</script>

<template>
  <Tag :severity="severity" :value="label" :icon="icon" :class="size === 'small' ? 'text-xs' : ''" />
</template>
