<script setup>
import { onMounted, onUnmounted, computed } from 'vue'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import StoreHeader from '@/Components/Store/StoreHeader.vue'
import OrderTrackingTimeline from '@/Components/Store/OrderTrackingTimeline.vue'
import { useOrderTracking } from '@/composables/useOrderTracking'

const props = defineProps({
  company:   { type: Object, required: true },
  order:     { type: Object, required: true },
  orderCode: { type: String, required: true },
})

const { order: liveOrder, startPolling, stopPolling } = useOrderTracking()

onMounted(() => {
  startPolling(props.company.slug, props.orderCode, props.order)
})
onUnmounted(() => stopPolling())

const totalFormatted = computed(() => {
  const v = liveOrder.value?.total ?? props.order?.total ?? 0
  return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
})

const orderTypeLabel = computed(() => {
  const t = liveOrder.value?.order_type ?? props.order?.order_type
  return t === 'pickup' ? 'Retirada no local' : 'Entrega'
})

const paymentLabel = computed(() => {
  const map = { cash: 'Dinheiro', pix: 'Pix', credit_card: 'Cartão de crédito', debit_card: 'Cartão de débito' }
  const m = liveOrder.value?.payment_method ?? props.order?.payment_method
  return map[m] ?? m
})
</script>

<template>
  <StoreLayout :company="company" title="Acompanhar pedido">
    <StoreHeader
      :company="company"
      :show-cart="false"
      show-back
      :back-href="route('store.home', company.slug)"
      back-label="Início"
    />

    <div class="max-w-2xl mx-auto px-4 py-5 space-y-5 pb-16">

      <!-- Header card -->
      <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-full flex items-center justify-center text-white shrink-0"
          :style="{ background: 'var(--store-primary, #F97316)' }"
        >
          <i class="pi pi-map-marker text-sm" />
        </div>
        <div class="min-w-0">
          <p class="text-xs text-gray-500">Pedido</p>
          <p class="font-mono font-bold text-lg tracking-widest text-gray-900">{{ orderCode }}</p>
        </div>
        <div class="ml-auto text-right">
          <p class="text-xs text-gray-500">Total</p>
          <p class="font-bold text-gray-900">{{ totalFormatted }}</p>
        </div>
      </div>

      <!-- Timeline -->
      <section class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
        <h2 class="font-semibold text-gray-900 mb-4">Status do pedido</h2>
        <OrderTrackingTimeline
          :current-status="liveOrder?.status ?? order?.status"
          :history="liveOrder?.history ?? order?.history ?? []"
          :order-type="liveOrder?.order_type ?? order?.order_type"
        />
      </section>

      <!-- Items -->
      <section class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
        <h2 class="font-semibold text-gray-900 mb-3">Itens do pedido</h2>
        <ul class="space-y-2.5">
          <li
            v-for="item in (liveOrder?.items ?? order?.items ?? [])"
            :key="item.id"
            class="flex items-start gap-3"
          >
            <span
              class="shrink-0 w-6 h-6 rounded-full text-white text-xs flex items-center justify-center font-bold"
              :style="{ background: 'var(--store-primary, #F97316)' }"
            >
              {{ item.quantity }}
            </span>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-800 truncate">{{ item.product_name }}</p>
              <p v-if="item.options?.length" class="text-xs text-gray-400">
                {{ item.options.map(o => o.option_name).join(', ') }}
              </p>
              <p v-if="item.notes" class="text-xs text-gray-400 italic">{{ item.notes }}</p>
            </div>
            <span class="text-sm text-gray-700 font-medium whitespace-nowrap">
              {{ Number(item.total_price).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}
            </span>
          </li>
        </ul>
      </section>

      <!-- Address (delivery) -->
      <section
        v-if="(liveOrder?.order_type ?? order?.order_type) === 'delivery'"
        class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm"
      >
        <h2 class="font-semibold text-gray-900 mb-2">Endereço de entrega</h2>
        <p class="text-sm text-gray-600">
          {{ (liveOrder?.address ?? order?.address)?.street }},
          {{ (liveOrder?.address ?? order?.address)?.number }}
          <span v-if="(liveOrder?.address ?? order?.address)?.complement">
            — {{ (liveOrder?.address ?? order?.address)?.complement }}
          </span>
        </p>
        <p class="text-sm text-gray-500">
          {{ (liveOrder?.address ?? order?.address)?.district }},
          {{ (liveOrder?.address ?? order?.address)?.city }}
        </p>
      </section>

      <!-- Info row -->
      <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl p-3 border border-gray-100 shadow-sm text-center">
          <p class="text-xs text-gray-400 mb-1">Tipo</p>
          <p class="text-sm font-semibold text-gray-800">{{ orderTypeLabel }}</p>
        </div>
        <div class="bg-white rounded-2xl p-3 border border-gray-100 shadow-sm text-center">
          <p class="text-xs text-gray-400 mb-1">Pagamento</p>
          <p class="text-sm font-semibold text-gray-800">{{ paymentLabel }}</p>
        </div>
      </div>

      <!-- Polling note -->
      <p class="text-center text-xs text-gray-400 pb-2">
        <i class="pi pi-refresh mr-1" />
        Status atualizado automaticamente a cada 30 segundos.
      </p>
    </div>
  </StoreLayout>
</template>
