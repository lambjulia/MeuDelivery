<script setup>
import { ref, computed, watch } from 'vue'
import { useCurrency } from '@/composables/useCurrency'
import { useCartStore } from '@/stores/cart'
import QuantitySelector from './QuantitySelector.vue'
import OptionGroupSelector from './OptionGroupSelector.vue'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
  product: { type: Object, default: null },
  visible: { type: Boolean, default: false },
})

const emit = defineEmits(['update:visible'])

const { format } = useCurrency()
const cart  = useCartStore()
const toast = useToast()

const qty           = ref(1)
const notes         = ref('')
const selectedOpts  = ref({}) // groupId -> array of option objects
const validationErr = ref('')

watch(() => props.product, () => {
  qty.value          = 1
  notes.value        = ''
  selectedOpts.value = {}
  validationErr.value = ''
})

function close() {
  emit('update:visible', false)
}

const basePrice  = computed(() => props.product?.current_price ?? 0)
const optionSum  = computed(() =>
  Object.values(selectedOpts.value)
    .flat()
    .reduce((s, o) => s + (o.additional_price || 0), 0)
)
const unitPrice  = computed(() => basePrice.value + optionSum.value)
const totalPrice = computed(() => unitPrice.value * qty.value)

function validateGroups() {
  if (! props.product) return false
  for (const group of props.product.option_groups ?? []) {
    if (! group.is_required) continue
    const selected = selectedOpts.value[group.id] ?? []
    if (selected.length < group.min_selections) {
      validationErr.value = `Select at least ${group.min_selections} option in "${group.name}"`
      return false
    }
  }
  validationErr.value = ''
  return true
}

function addToCart() {
  if (! validateGroups()) return

  const flatOptions = Object.entries(selectedOpts.value).flatMap(([groupId, opts]) => {
    const group = props.product.option_groups.find(g => g.id === Number(groupId))
    return opts.map(o => ({
      option_id:        o.id,
      name:             o.name,
      additional_price: o.additional_price,
      group_name:       group?.name ?? '',
    }))
  })

  cart.addItem(props.product, flatOptions, qty.value, notes.value)

  toast.add({
    severity: 'success',
    summary:  'Adicionado!',
    detail:   `${props.product.name} adicionado ao carrinho.`,
    life:     2000,
  })

  close()
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="visible && product"
        class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="close" />

        <!-- Modal card -->
        <div class="relative z-10 w-full sm:max-w-lg bg-white rounded-t-3xl sm:rounded-2xl overflow-hidden max-h-[90vh] flex flex-col">

          <!-- Product image -->
          <div class="relative h-52 bg-gray-100 shrink-0">
            <img
              v-if="product.image_url"
              :src="product.image_url"
              :alt="product.name"
              class="w-full h-full object-cover"
            />
            <button
              class="absolute top-3 right-3 w-8 h-8 bg-white/90 rounded-full flex items-center justify-center shadow"
              @click="close"
            >
              <i class="pi pi-times text-gray-700 text-sm" />
            </button>
          </div>

          <!-- Scrollable content -->
          <div class="flex-1 overflow-y-auto p-4 space-y-4">
            <!-- Title + description -->
            <div>
              <h2 class="text-lg font-bold text-gray-900">{{ product.name }}</h2>
              <p v-if="product.description" class="text-sm text-gray-500 mt-1">
                {{ product.description }}
              </p>
              <div class="flex items-center gap-2 mt-2">
                <span class="text-lg font-bold" :style="{ color: 'var(--store-primary, #F97316)' }">
                  {{ format(product.current_price) }}
                </span>
                <span
                  v-if="product.promotional_price && product.base_price > product.promotional_price"
                  class="text-sm text-gray-400 line-through"
                >
                  {{ format(product.base_price) }}
                </span>
              </div>
            </div>

            <!-- Option groups -->
            <OptionGroupSelector
              v-for="group in product.option_groups ?? []"
              :key="group.id"
              :group="group"
              v-model="selectedOpts[group.id]"
            />

            <!-- Notes -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
              <textarea
                v-model="notes"
                placeholder="Algum pedido especial? (opcional)"
                rows="2"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-orange-400"
              />
            </div>

            <!-- Validation error -->
            <p v-if="validationErr" class="text-sm text-red-500 font-medium">
              <i class="pi pi-exclamation-circle mr-1" />{{ validationErr }}
            </p>
          </div>

          <!-- Footer -->
          <div class="p-4 border-t border-gray-100 bg-white shrink-0">
            <div class="flex items-center gap-3">
              <QuantitySelector v-model="qty" :min="1" :max="20" />
              <button
                class="flex-1 py-3 rounded-xl text-white font-semibold text-sm transition-opacity active:opacity-80"
                :style="{ background: 'var(--store-primary, #F97316)' }"
                @click="addToCart"
              >
                Adicionar · {{ format(totalPrice) }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-active .relative.z-10     { transition: transform 0.25s ease; }
.modal-leave-active .relative.z-10     { transition: transform 0.2s ease; }
.modal-enter-from, .modal-leave-to     { opacity: 0; }
.modal-enter-from .relative.z-10       { transform: translateY(30px); }
.modal-leave-to .relative.z-10         { transform: translateY(30px); }
</style>
