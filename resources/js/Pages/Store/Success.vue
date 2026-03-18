<script setup>
import { computed } from 'vue'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import StoreHeader from '@/Components/Store/StoreHeader.vue'

const props = defineProps({
  company:   { type: Object, required: true },
  orderCode: { type: String, required: true },
})

const trackingUrl = computed(() => route('store.tracking', [props.company.slug, props.orderCode]))
const menuUrl     = computed(() => route('store.home', props.company.slug))
</script>

<template>
  <StoreLayout :company="company" title="Pedido confirmado!">
    <StoreHeader :company="company" :show-cart="false" />

    <div class="min-h-[calc(100vh-64px)] flex items-center justify-center px-4">
      <div class="max-w-md w-full text-center space-y-6 py-12">

        <!-- Animated checkmark -->
        <div
          class="w-24 h-24 rounded-full mx-auto flex items-center justify-center text-white text-4xl"
          :style="{ background: 'var(--store-primary, #F97316)' }"
        >
          <i class="pi pi-check" />
        </div>

        <div>
          <h1 class="text-2xl font-bold text-gray-900">Pedido confirmado!</h1>
          <p class="text-gray-500 mt-1 text-sm">
            Recebemos seu pedido e já estamos preparando tudo.
          </p>
        </div>

        <!-- Order code badge -->
        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
          <p class="text-sm text-gray-500 mb-1">Código do pedido</p>
          <p class="text-3xl font-mono font-bold tracking-widest" :style="{ color: 'var(--store-primary, #F97316)' }">
            {{ orderCode }}
          </p>
          <p class="text-xs text-gray-400 mt-1">Guarde este código para acompanhar seu pedido</p>
        </div>

        <!-- CTAs -->
        <div class="space-y-3">
          <a
            :href="trackingUrl"
            class="block w-full py-3.5 rounded-2xl text-white font-semibold text-sm transition-opacity active:opacity-80"
            :style="{ background: 'var(--store-primary, #F97316)' }"
          >
            <i class="pi pi-map-marker mr-2" />
            Acompanhar pedido
          </a>

          <a
            :href="menuUrl"
            class="block w-full py-3.5 rounded-2xl font-semibold text-sm border-2 transition-colors hover:bg-gray-50"
            :style="{ borderColor: 'var(--store-primary, #F97316)', color: 'var(--store-primary, #F97316)' }"
          >
            <i class="pi pi-shopping-bag mr-2" />
            Voltar ao cardápio
          </a>
        </div>

        <p class="text-xs text-gray-400">
          Você também receberá atualizações pelo número cadastrado.
        </p>
      </div>
    </div>
  </StoreLayout>
</template>
