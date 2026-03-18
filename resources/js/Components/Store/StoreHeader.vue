<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  company: { type: Object, required: true },
  showCart: { type: Boolean, default: true },
  showBack: { type: Boolean, default: false },
  backHref: { type: String, default: '' },
  backLabel: { type: String, default: 'Voltar' },
})

const cart = useCartStore()
const logoUrl = computed(() => props.company.logo_url)
const storeHomeUrl = computed(() => props.company?.slug ? route('store.home', props.company.slug) : '/store')
const storeCartUrl = computed(() => props.company?.slug ? route('store.cart', props.company.slug) : '/store')
</script>

<template>
  <header class="sticky top-0 z-50 bg-white shadow-sm">
    <div class="max-w-3xl mx-auto px-4 h-16 flex items-center gap-3">
      <!-- Back button -->
      <Link
        v-if="showBack && backHref"
        :href="backHref"
        class="p-2 -ml-2 rounded-full hover:bg-gray-100 text-gray-600"
      >
        <i class="pi pi-arrow-left text-lg" />
      </Link>

      <!-- Logo + Name -->
      <a :href="storeHomeUrl" class="flex items-center gap-3 flex-1 min-w-0">
        <img
          v-if="logoUrl"
          :src="logoUrl"
          :alt="company.name"
          class="w-10 h-10 rounded-full object-cover shrink-0"
        />
        <div
          v-else
          class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0"
          :style="{ background: company.primary_color ?? 'var(--store-primary, #F97316)' }"
        >
          {{ company.name?.charAt(0) }}
        </div>
        <div class="flex flex-col min-w-0">
          <span class="font-semibold text-gray-900 text-sm leading-tight">{{ company.name }}</span>
          <small v-if="company.tagline" class="text-xs text-gray-500 truncate">{{ company.tagline }}</small>
        </div>
      </a>

      <!-- Cart button -->
      <a
        v-if="showCart"
        :href="storeCartUrl"
        class="relative flex items-center gap-1.5 px-3 py-2 rounded-full text-white text-sm font-medium transition-opacity active:opacity-80"
        :style="{ background: 'var(--store-primary, #F97316)' }"
      >
        <i class="pi pi-shopping-cart" />
        <span v-if="cart.count > 0">{{ cart.count }}</span>
      </a>
    </div>
  </header>
</template>
