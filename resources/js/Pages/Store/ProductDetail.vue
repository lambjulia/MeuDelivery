<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import StoreHeader from '@/Components/Store/StoreHeader.vue'
import OptionGroupSelector from '@/Components/Store/OptionGroupSelector.vue'
import QuantitySelector from '@/Components/Store/QuantitySelector.vue'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  company: { type: Object, required: true },
  product: { type: Object, required: true },
})

const cart = useCartStore()

const quantity     = ref(1)
const notes        = ref('')
const selectedOpts = ref({})
const addedFlash   = ref(false)

// Init selections: auto-check first option in required single-choice groups
props.product.option_groups?.forEach(group => {
  if (group.required && !group.multiple && group.options?.length) {
    selectedOpts.value[group.id] = [group.options[0].id]
  } else {
    selectedOpts.value[group.id] = []
  }
})

const priceFormatted = computed(() =>
  Number(props.product.price).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
)
const promoPriceFormatted = computed(() =>
  props.product.promo_price
    ? Number(props.product.promo_price).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
    : null
)

const optionsTotal = computed(() => {
  let extra = 0
  props.product.option_groups?.forEach(group => {
    const ids = selectedOpts.value[group.id] ?? []
    ids.forEach(optId => {
      const opt = group.options.find(o => o.id === optId)
      if (opt) extra += Number(opt.price ?? 0)
    })
  })
  return extra
})
const basePrice  = computed(() => Number(props.product.promo_price ?? props.product.price))
const totalPrice = computed(() => (basePrice.value + optionsTotal.value) * quantity.value)

const canAdd = computed(() => {
  return props.product.option_groups?.every(group => {
    if (!group.required) return true
    return (selectedOpts.value[group.id]?.length ?? 0) > 0
  }) ?? true
})

function handleOptionChange(groupId, ids) {
  selectedOpts.value[groupId] = ids
}

function addToCart() {
  if (!canAdd.value) return

  const options = []
  props.product.option_groups?.forEach(group => {
    const ids = selectedOpts.value[group.id] ?? []
    ids.forEach(optId => {
      const opt = group.options.find(o => o.id === optId)
      if (opt) options.push({ option_id: opt.id, name: opt.name, price: Number(opt.price ?? 0) })
    })
  })

  cart.addItem({
    product_id:   props.product.id,
    product_name: props.product.name,
    image_url:    props.product.image_url,
    price:        basePrice.value,
    quantity:     quantity.value,
    notes:        notes.value,
    options,
  })

  addedFlash.value = true
  setTimeout(() => {
    addedFlash.value = false
    router.visit(route('store.home', props.company.slug))
  }, 800)
}
</script>

<template>
  <StoreLayout :company="company" :title="product.name">
    <StoreHeader
      :company="company"
      show-back
      :back-href="route('store.home', company.slug)"
      back-label="Cardápio"
    />

    <div class="max-w-2xl mx-auto pb-28">

      <!-- Hero image -->
      <div class="relative w-full aspect-square bg-gray-100 overflow-hidden">
        <img
          v-if="product.image_url"
          :src="product.image_url"
          :alt="product.name"
          class="w-full h-full object-cover"
        />
        <div v-else class="w-full h-full flex items-center justify-center bg-gray-200">
          <i class="pi pi-image text-5xl text-gray-300" />
        </div>

        <!-- Promo badge -->
        <span
          v-if="product.promo_price"
          class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full"
        >
          Promoção
        </span>
      </div>

      <div class="px-4 py-5 space-y-5">
        <!-- Name & description -->
        <div>
          <h1 class="text-xl font-bold text-gray-900">{{ product.name }}</h1>
          <p v-if="product.description" class="text-sm text-gray-500 mt-1">{{ product.description }}</p>
          <div class="flex items-baseline gap-2 mt-2">
            <span
              v-if="product.promo_price"
              class="text-xs text-gray-400 line-through"
            >{{ priceFormatted }}</span>
            <span class="font-bold text-lg" :style="{ color: 'var(--store-primary, #F97316)' }">
              {{ promoPriceFormatted ?? priceFormatted }}
            </span>
          </div>
        </div>

        <!-- Option groups -->
        <OptionGroupSelector
          v-for="group in product.option_groups ?? []"
          :key="group.id"
          :group="group"
          :model-value="selectedOpts[group.id] ?? []"
          @update:model-value="ids => handleOptionChange(group.id, ids)"
        />

        <!-- Notes -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Observação <span class="text-gray-400 font-normal">(opcional)</span>
          </label>
          <textarea
            v-model="notes"
            rows="2"
            placeholder="Ex.: sem cebola, ponto da carne…"
            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-orange-300"
          />
        </div>

        <!-- Qty + total -->
        <div class="flex items-center justify-between">
          <QuantitySelector v-model="quantity" :min="1" :max="20" />
          <span class="font-bold text-lg">
            {{ totalPrice.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Sticky bottom bar -->
    <div class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-100 px-4 py-3 max-w-2xl mx-auto">
      <button
        :disabled="!canAdd"
        class="w-full py-3.5 rounded-2xl text-white font-bold text-sm transition-all disabled:opacity-50 flex items-center justify-center gap-2"
        :class="addedFlash ? 'scale-95' : ''"
        :style="{ background: addedFlash ? '#22c55e' : 'var(--store-primary, #F97316)' }"
        @click="addToCart"
      >
        <i :class="addedFlash ? 'pi pi-check' : 'pi pi-shopping-cart'" />
        {{ addedFlash ? 'Adicionado!' : 'Adicionar ao carrinho' }}
        <span class="ml-auto">
          {{ totalPrice.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}
        </span>
      </button>
    </div>
  </StoreLayout>
</template>
