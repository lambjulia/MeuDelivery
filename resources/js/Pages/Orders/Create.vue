<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import MoneyDisplay from '@/Components/MoneyDisplay.vue'
import { useI18n } from 'vue-i18n'
import { useCurrency } from '@/composables/useCurrency'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Textarea from 'primevue/textarea'
import InputNumber from 'primevue/inputnumber'
import { useToast } from 'primevue/usetoast'

const { t } = useI18n()
const { format } = useCurrency()
const toast = useToast()

const props = defineProps({
  customers:     { type: Array, default: () => [] },
  products:      { type: Array, default: () => [] },
  deliveryZones: { type: Array, default: () => [] },
  errors:        { type: Object, default: () => ({}) },
})

// Form state
const customerId      = ref(null)
const customerSearch  = ref('')
const filteredCustomers = ref([])
const selectedCustomer = ref(null)
const deliveryAddressId = ref(null)
const orderType       = ref('delivery')
const paymentMethod   = ref('cash')
const couponCode      = ref('')
const notes           = ref('')
const changeAmount    = ref(null)
const deliveryZoneId  = ref(null)
const cart            = ref([])

// Customer search
let debounceTimer = null
watch(customerSearch, (val) => {
  clearTimeout(debounceTimer)
  if (!val || val.length < 2) { filteredCustomers.value = []; return }
  debounceTimer = setTimeout(() => {
    fetch(route('customers.search') + '?q=' + encodeURIComponent(val))
      .then(r => r.json())
      .then(data => { filteredCustomers.value = data })
  }, 300)
})

function selectCustomer(c) {
  selectedCustomer.value = c
  customerId.value = c.id
  customerSearch.value = c.name
  filteredCustomers.value = []
  if (c.addresses?.length) deliveryAddressId.value = c.addresses.find(a => a.is_default)?.id ?? c.addresses[0].id
}

// Cart management
const productMap = computed(() => Object.fromEntries(props.products.map(p => [p.id, p])))

function addToCart(product) {
  const existing = cart.value.find(i => i.product_id === product.id && !i.options?.length)
  if (existing) { existing.quantity++; return }
  cart.value.push({ product_id: product.id, product_name: product.name, unit_price: product.effective_price ?? product.base_price, quantity: 1, options: [], notes: '' })
}

function removeFromCart(idx) { cart.value.splice(idx, 1) }

const subtotal = computed(() => cart.value.reduce((s, i) => s + i.unit_price * i.quantity, 0))
const deliveryFee = computed(() => {
  if (orderType.value !== 'delivery') return 0
  const zone = props.deliveryZones.find(z => z.id === deliveryZoneId.value)
  return zone?.delivery_fee ?? 0
})
const total = computed(() => subtotal.value + deliveryFee.value)

const paymentMethods = [
  { value: 'cash',        label: 'Dinheiro' },
  { value: 'credit_card', label: 'Cartão de crédito' },
  { value: 'debit_card',  label: 'Cartão de débito' },
  { value: 'pix',         label: 'PIX' },
  { value: 'voucher',     label: 'Voucher' },
]

function submit() {
  if (!customerId.value) { toast.add({ severity: 'warn', summary: 'Selecione um cliente', life: 3000 }); return }
  if (!cart.value.length) { toast.add({ severity: 'warn', summary: 'Adicione pelo menos um item', life: 3000 }); return }

  router.post(route('orders.store'), {
    customer_id:         customerId.value,
    delivery_address_id: orderType.value === 'delivery' ? deliveryAddressId.value : undefined,
    delivery_zone_id:    orderType.value === 'delivery' ? deliveryZoneId.value : undefined,
    order_type:          orderType.value,
    payment_method:      paymentMethod.value,
    coupon_code:         couponCode.value || undefined,
    change_amount:       changeAmount.value || undefined,
    notes:               notes.value || undefined,
    items: cart.value.map(i => ({
      product_id: i.product_id,
      quantity:   i.quantity,
      notes:      i.notes || undefined,
      options:    i.options,
    })),
  }, {
    onError: () => toast.add({ severity: 'error', summary: 'Verifique os campos', life: 4000 }),
  })
}

const productSearch = ref('')
const filteredProducts = computed(() => {
  const q = productSearch.value.toLowerCase()
  if (!q) return props.products
  return props.products.filter(p => p.name.toLowerCase().includes(q))
})
</script>

<template>
  <AppLayout title="Novo Pedido">
    <Head title="Novo Pedido" />

    <PageHeader title="Novo Pedido" :back="route('orders.index')">
      <Button label="Criar Pedido" icon="pi pi-check" severity="contrast" size="small" @click="submit" :disabled="!cart.length || !customerId" />
    </PageHeader>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: Customer + Products -->
      <div class="lg:col-span-2 space-y-5">
        <!-- Customer -->
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Cliente</h3>
          <div class="relative">
            <InputText
              v-model="customerSearch"
              placeholder="Buscar cliente por nome, telefone..."
              size="small"
              class="w-full"
            />
            <div v-if="filteredCustomers.length" class="absolute z-10 w-full mt-1 bg-surface-0 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-lg shadow-lg overflow-hidden">
              <button
                v-for="c in filteredCustomers"
                :key="c.id"
                class="w-full px-4 py-2.5 text-left hover:bg-surface-50 dark:hover:bg-surface-700 border-b border-surface-100 dark:border-surface-700 last:border-0"
                @click="selectCustomer(c)"
              >
                <p class="text-sm font-medium text-surface-800 dark:text-surface-100">{{ c.name }}</p>
                <p class="text-xs text-surface-500">{{ c.phone }}</p>
              </button>
            </div>
          </div>

          <div v-if="selectedCustomer" class="mt-3 p-3 bg-green-50 dark:bg-green-500/10 rounded-lg border border-green-200 dark:border-green-500/30">
            <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ selectedCustomer.name }}</p>
            <p class="text-xs text-green-600 dark:text-green-400">{{ selectedCustomer.phone }}</p>
          </div>

          <!-- Address for delivery -->
          <div v-if="orderType === 'delivery' && selectedCustomer?.addresses?.length" class="mt-3">
            <label class="block text-xs font-medium text-surface-500 mb-1">Endereço de entrega</label>
            <Select
              v-model="deliveryAddressId"
              :options="selectedCustomer.addresses.map(a => ({ value: a.id, label: `${a.street}, ${a.number} - ${a.district}` }))"
              optionLabel="label"
              optionValue="value"
              size="small"
              class="w-full"
            />
          </div>
        </div>

        <!-- Products -->
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <div class="flex items-center gap-3 mb-4">
            <h3 class="font-semibold text-surface-800 dark:text-surface-100 flex-1">Produtos</h3>
            <InputText v-model="productSearch" placeholder="Buscar produto..." size="small" class="w-48" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72 overflow-y-auto pr-1">
            <button
              v-for="p in filteredProducts"
              :key="p.id"
              class="flex items-center gap-3 p-3 rounded-lg border border-surface-200 dark:border-surface-700 hover:border-orange-300 dark:hover:border-orange-500/50 hover:bg-orange-50 dark:hover:bg-orange-500/5 text-left transition-colors"
              @click="addToCart(p)"
            >
              <div class="w-10 h-10 rounded-lg bg-surface-100 dark:bg-surface-700 overflow-hidden shrink-0">
                <img v-if="p.image_url" :src="p.image_url" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <i class="pi pi-box text-surface-400 text-sm" />
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-surface-800 dark:text-surface-100 truncate">{{ p.name }}</p>
                <p class="text-xs font-semibold text-orange-600 dark:text-orange-400">{{ format(p.effective_price ?? p.base_price) }}</p>
              </div>
              <i class="pi pi-plus text-surface-400 text-sm shrink-0" />
            </button>
          </div>
        </div>
      </div>

      <!-- Right: Cart + details -->
      <div class="space-y-5">
        <!-- Order type + payment -->
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">Detalhes</h3>
          <div class="space-y-3">
            <div>
              <label class="block text-xs font-medium text-surface-500 mb-1">Tipo de pedido</label>
              <div class="flex gap-2">
                <button
                  v-for="opt in [{value:'delivery',label:'Entrega'},{value:'pickup',label:'Retirada'}]"
                  :key="opt.value"
                  class="flex-1 py-2 rounded-lg border text-sm font-medium transition-colors"
                  :class="orderType === opt.value ? 'border-orange-500 bg-orange-50 dark:bg-orange-500/10 text-orange-600' : 'border-surface-200 dark:border-surface-700 text-surface-500 hover:border-surface-300'"
                  @click="orderType = opt.value"
                >{{ opt.label }}</button>
              </div>
            </div>
            <div v-if="orderType === 'delivery'">
              <label class="block text-xs font-medium text-surface-500 mb-1">Zona de entrega</label>
              <Select
                v-model="deliveryZoneId"
                :options="deliveryZones.map(z => ({ value: z.id, label: `${z.name} - ${format(z.delivery_fee)}` }))"
                optionLabel="label"
                optionValue="value"
                size="small"
                class="w-full"
                placeholder="Selecione a zona..."
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-surface-500 mb-1">Forma de pagamento</label>
              <Select v-model="paymentMethod" :options="paymentMethods" optionLabel="label" optionValue="value" size="small" class="w-full" />
            </div>
            <div v-if="paymentMethod === 'cash'">
              <label class="block text-xs font-medium text-surface-500 mb-1">Troco para</label>
              <InputNumber v-model="changeAmount" mode="currency" currency="BRL" locale="pt-BR" size="small" class="w-full" />
            </div>
            <div>
              <label class="block text-xs font-medium text-surface-500 mb-1">Cupom (opcional)</label>
              <InputText v-model="couponCode" placeholder="CUPOM10" size="small" class="w-full uppercase" />
            </div>
            <div>
              <label class="block text-xs font-medium text-surface-500 mb-1">Observações</label>
              <Textarea v-model="notes" placeholder="Sem cebola, sem pimenta..." rows="2" class="w-full text-sm" />
            </div>
          </div>
        </div>

        <!-- Cart -->
        <div class="bg-surface-0 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 p-5">
          <h3 class="font-semibold text-surface-800 dark:text-surface-100 mb-4">
            Carrinho <span class="text-surface-400 font-normal text-sm">({{ cart.length }} iten(s))</span>
          </h3>
          <div v-if="!cart.length" class="text-center py-8 text-surface-400 text-sm">
            <i class="pi pi-shopping-cart text-2xl block mb-2" />
            Adicione produtos ao pedido
          </div>
          <div v-else class="space-y-3">
            <div v-for="(item, idx) in cart" :key="idx" class="flex items-start gap-2 border-b border-surface-100 dark:border-surface-700 pb-3 last:border-0 last:pb-0">
              <div class="flex-1">
                <p class="text-sm font-medium text-surface-800 dark:text-surface-100 truncate">{{ item.product_name }}</p>
                <p class="text-xs text-orange-600 dark:text-orange-400">{{ format(item.unit_price) }}</p>
              </div>
              <div class="flex items-center gap-1">
                <button class="w-5 h-5 rounded bg-surface-100 dark:bg-surface-700 flex items-center justify-center text-xs" @click="item.quantity > 1 ? item.quantity-- : removeFromCart(idx)">
                  <i class="pi pi-minus" style="font-size:0.6rem" />
                </button>
                <span class="w-6 text-center text-sm font-semibold">{{ item.quantity }}</span>
                <button class="w-5 h-5 rounded bg-surface-100 dark:bg-surface-700 flex items-center justify-center text-xs" @click="item.quantity++">
                  <i class="pi pi-plus" style="font-size:0.6rem" />
                </button>
              </div>
              <span class="text-sm font-semibold text-surface-800 dark:text-surface-100 w-16 text-right">{{ format(item.unit_price * item.quantity) }}</span>
              <button @click="removeFromCart(idx)" class="text-surface-400 hover:text-red-500 ml-1">
                <i class="pi pi-trash text-xs" />
              </button>
            </div>
            <div class="pt-2 border-t border-surface-200 dark:border-surface-700 space-y-1 text-sm">
              <div class="flex justify-between text-surface-500"><span>Subtotal</span><span>{{ format(subtotal) }}</span></div>
              <div v-if="deliveryFee" class="flex justify-between text-surface-500"><span>Entrega</span><span>{{ format(deliveryFee) }}</span></div>
              <div class="flex justify-between font-bold text-base"><span>Total</span><span class="text-orange-600 dark:text-orange-400">{{ format(total) }}</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
