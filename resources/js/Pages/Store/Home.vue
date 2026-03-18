<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import StoreHeader from '@/Components/Store/StoreHeader.vue'
import StoreBanner from '@/Components/Store/StoreBanner.vue'
import StoreStatusBanner from '@/Components/Store/StoreStatusBanner.vue'
import CategoryTabs from '@/Components/Store/CategoryTabs.vue'
import ProductCard from '@/Components/Store/ProductCard.vue'
import ProductDetailModal from '@/Components/Store/ProductDetailModal.vue'
import FloatingCartButton from '@/Components/Store/FloatingCartButton.vue'
import { useStorefrontStore } from '@/stores/storefront'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  company:       { type: Object, required: true },
  categories:    { type: Array, required: true },
  deliveryZones: { type: Array, default: () => [] },
})

const storefront = useStorefrontStore()
const cart       = useCartStore()

onMounted(() => {
  storefront.init(props.company, props.categories)
  cart.init(props.company.slug)
})

const selectedProduct = ref(null)
const modalVisible    = ref(false)

function openProduct(product) {
  // Merge category data — we need option_groups.  For the home page list, products
  // don't have option_groups yet. We fetch the detail on the fly.
  fetchProductDetail(product)
}

async function fetchProductDetail(product) {
  // If already has option_groups from inertia share (product detail page), skip fetch
  if (product.option_groups !== undefined) {
    selectedProduct.value = product
    modalVisible.value    = true
    return
  }

  try {
    // Lightweight fetch using the existing Inertia route to get option_groups
    const res  = await fetch(
      `/store/${props.company.slug}/product/${product.slug}`,
      { headers: { 'X-Inertia': 'true', 'X-Inertia-Version': '', 'Accept': 'application/json' } }
    )
    // The Inertia JSON response contains { component, props: { product } }
    const json = await res.json()
    const detail = json.props?.product ?? product
    selectedProduct.value = detail
  } catch {
    selectedProduct.value = { ...product, option_groups: [] }
  }
  modalVisible.value = true
}

const searchQuery = computed({
  get: () => storefront.searchQuery,
  set: v => { storefront.searchQuery = v },
})

const filteredCategories = computed(() => storefront.filteredCategories)

function scrollToCategory(slug) {
  storefront.activeCategory = slug
  const el = document.getElementById(`cat-${slug}`)
  if (el) {
    const offset = 130 // header + tabs
    const top = el.getBoundingClientRect().top + window.scrollY - offset
    window.scrollTo({ top, behavior: 'smooth' })
  }
}
</script>

<template>
  <StoreLayout :company="company">
    <div class="bg-gradient-to-b from-white to-indigo-50">
      <StoreStatusBanner :is_open="company.is_open" />
      <StoreHeader :company="company" />
    </div>

    <div class="max-w-4xl mx-auto px-4">
      <div class="bg-white shadow-sm rounded-xl mt-4 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
          <span v-if="company.address?.district" class="flex items-center gap-2">
            <i class="pi pi-map-marker text-indigo-400" />
            <span class="text-gray-700">{{ company.address.district }}, {{ company.address.city }}</span>
          </span>
          <span class="flex items-center gap-2">
            <i class="pi pi-truck text-indigo-400" />
            <span class="text-gray-700">
              Entrega
              <span class="text-gray-700 font-medium">
                {{ company.default_delivery_fee == null ? '—' : (company.default_delivery_fee === 0 ? 'Grátis' : `R$ ${Number(company.default_delivery_fee).toFixed(2)}`) }}
              </span>
            </span>
          </span>
          <span v-if="company.min_order_amount > 0" class="flex items-center gap-2">
            <i class="pi pi-shopping-cart text-indigo-400" />
            <span class="text-gray-700">Mín. R$ {{ company.min_order_amount }}</span>
          </span>
        </div>

        <div class="w-full sm:w-1/2">
          <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2 shadow-inner">
            <i class="pi pi-search text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Buscar produtos..."
              class="w-full bg-transparent text-sm text-gray-800 focus:outline-none placeholder-gray-400"
            />
            <button v-if="searchQuery" @click="searchQuery = ''" class="text-gray-400">
              <i class="pi pi-times text-xs" />
            </button>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <StoreBanner :company="company" />
      </div>

      <div class="mt-4">
        <CategoryTabs
          v-if="!searchQuery && filteredCategories.length"
          :categories="filteredCategories"
          :active-category="storefront.activeCategory"
          @select="scrollToCategory"
        />
      </div>

      <div class="mt-6 pb-28">
        <template v-for="category in filteredCategories" :key="category.slug">
          <div v-if="category.products?.length" :id="`cat-${category.slug}`" class="pt-6">
            <h2 class="font-semibold text-gray-900 text-lg mb-3 border-l-4 border-indigo-500 pl-3">{{ category.name }}</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <ProductCard
                v-for="product in category.products"
                :key="product.id"
                :product="product"
                :company-slug="company.slug"
                @open="openProduct"
              />
            </div>
          </div>
        </template>

        <div v-if="!filteredCategories.length" class="flex flex-col items-center gap-3 py-16 text-center text-gray-500">
          <i class="pi pi-search text-6xl text-gray-300" />
          <p class="font-semibold text-gray-600">Nenhum produto encontrado</p>
          <p class="text-sm text-gray-400">Tente outro termo de busca ou verifique as categorias</p>
        </div>
      </div>
    </div>

    <ProductDetailModal
      :product="selectedProduct"
      :visible="modalVisible"
      @update:visible="v => { modalVisible = v; if (!v) selectedProduct = null }"
    />

    <FloatingCartButton :company="company" />
  </StoreLayout>
</template>
