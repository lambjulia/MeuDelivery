<script setup>
import { computed, ref } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatsCard from '@/Components/StatsCard.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useI18n } from 'vue-i18n'
import { useCurrency } from '@/composables/useCurrency'
import Select from 'primevue/select'

const { t } = useI18n()
const { format: formatMoney } = useCurrency()

const page = usePage()
const companySlug = computed(() => page.props.companySlug)
const storeUrl = computed(() => companySlug.value ? `/store/${companySlug.value}` : null)
console.log('Company Slug:', companySlug.value)
const props = defineProps({
  metrics: { type: Object, default: () => ({}) },
  period:  { type: String, default: 'today' },
})

const periods = [
  { value: 'today',         label: 'Hoje' },
  { value: 'yesterday',     label: 'Ontem' },
  { value: 'last_7_days',   label: 'Últimos 7 dias' },
  { value: 'last_30_days',  label: 'Últimos 30 dias' },
  { value: 'current_month', label: 'Mês atual' },
]

const selectedPeriod = ref(props.period)

function changePeriod() {
  router.get(route('dashboard'), { period: selectedPeriod.value }, { preserveState: true })
}

const topProducts     = computed(() => props.metrics.topProducts ?? [])
const paymentMethods  = computed(() => props.metrics.paymentMethods ?? [])
const dailyRevenue    = computed(() => props.metrics.dailyRevenue ?? [])
const maxRevenue      = computed(() => Math.max(...dailyRevenue.value.map(d => d.total || 0), 1))
</script>

<template>
  <AppLayout :title="t('nav.dashboard')">
    <Head :title="t('nav.dashboard')" />

    <div class="flex items-center gap-3 mb-6">
      <h2 class="text-lg font-semibold text-surface-800 dark:text-surface-100 flex-1">
        {{ t('dashboard.title') }}
      </h2>
      <Select
        v-model="selectedPeriod"
        :options="periods"
        optionLabel="label"
        optionValue="value"
        size="small"
        class="w-44"
        @change="changePeriod"
      />
      <a
        v-if="storeUrl"
        :href="storeUrl"
        target="_blank"
        rel="noopener"
        class="ml-2 inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-orange-500 text-white text-sm font-semibold hover:opacity-95"
      >
        <i class="pi pi-storefront" />
        {{ t('nav.viewStore') }}
      </a>
    </div>

    <!-- Primary stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-4">
      <StatsCard :title="t('dashboard.totalOrders')"    :value="metrics.totalOrders ?? 0"       icon="pi-shopping-cart" color="blue"   />
      <StatsCard :title="t('dashboard.revenue')"        :value="formatMoney(metrics.revenue ?? 0)" icon="pi-dollar"     color="green"  />
      <StatsCard :title="t('dashboard.avgTicket')"      :value="formatMoney(metrics.avgTicket ?? 0)" icon="pi-chart-line" color="orange" />
      <StatsCard :title="t('dashboard.canceledOrders')" :value="metrics.canceledOrders ?? 0"    icon="pi-times-circle" color="red"    />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <StatsCard :title="t('dashboard.deliveredOrders')" :value="metrics.deliveredOrders ?? 0"  icon="pi-check-circle" color="green"  />
      <StatsCard :title="t('dashboard.activeOrders')"    :value="metrics.activeOrders ?? 0"     icon="pi-clock"        color="orange" />
      <StatsCard :title="t('dashboard.pendingOrders')"   :value="metrics.pendingOrders ?? 0"    icon="pi-hourglass"    color="purple" />
    </div>

    <!-- Panels row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
        <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">{{ t('dashboard.topProducts') }}</h3>
        <EmptyState v-if="!topProducts.length" :title="t('common.noData')" icon="pi-box" />
        <div v-else class="space-y-3">
          <div v-for="(p, i) in topProducts" :key="p.product_id" class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 text-xs font-bold flex items-center justify-center shrink-0">
              {{ i + 1 }}
            </span>
            <span class="flex-1 text-sm text-surface-700 dark:text-surface-200 truncate">{{ p.product_name }}</span>
            <span class="text-sm font-semibold text-surface-900 dark:text-surface-0">{{ p.total_quantity }}×</span>
            <span class="text-sm text-surface-500">{{ formatMoney(p.total_revenue) }}</span>
          </div>
        </div>
      </div>

      <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
        <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">{{ t('dashboard.paymentMethods') }}</h3>
        <EmptyState v-if="!paymentMethods.length" :title="t('common.noData')" icon="pi-credit-card" />
        <div v-else class="space-y-3">
          <div v-for="m in paymentMethods" :key="m.payment_method" class="flex items-center gap-3">
            <i class="pi pi-credit-card text-surface-400 shrink-0" />
            <span class="flex-1 text-sm text-surface-700 dark:text-surface-200 capitalize">
              {{ m.payment_method?.replace(/_/g, ' ') }}
            </span>
            <span class="text-xs text-surface-500">{{ m.count }}×</span>
            <span class="text-sm font-semibold text-surface-900 dark:text-surface-0">{{ formatMoney(m.total) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Bar chart -->
    <div v-if="dailyRevenue.length" class="mt-6 bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
      <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">{{ t('dashboard.dailyRevenue') }}</h3>
      <div class="flex items-end gap-1 h-32">
        <div v-for="day in dailyRevenue" :key="day.date" class="flex-1 flex flex-col items-center gap-1 group">
          <div
            class="w-full bg-orange-400 dark:bg-orange-500 rounded-t transition-all cursor-default"
            :style="{ height: ((day.total / maxRevenue) * 100) + '%', minHeight: '2px' }"
            :title="`${day.date}: ${formatMoney(day.total)}`"
          />
          <span class="text-[10px] text-surface-400 opacity-0 group-hover:opacity-100 transition-opacity">{{ day.date?.slice(5) }}</span>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
