<script setup>
import { computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'
import StoreHeader from '@/Components/Store/StoreHeader.vue'
import CartSummary from '@/Components/Store/CartSummary.vue'
import DeliveryTypeSelector from '@/Components/Store/DeliveryTypeSelector.vue'
import AddressForm from '@/Components/Store/AddressForm.vue'
import CouponInput from '@/Components/Store/CouponInput.vue'
import PaymentMethodSelector from '@/Components/Store/PaymentMethodSelector.vue'
import { useCartStore } from '@/stores/cart'
import { useCheckoutStore } from '@/stores/checkout'
import { useCheckout } from '@/composables/useCheckout'

const props = defineProps({
  company:       { type: Object, required: true },
  deliveryZones: { type: Array, default: () => [] },
})

const cart      = useCartStore()
const checkout  = useCheckoutStore()
const { loading, errors, totalAmount, placeOrder } = useCheckout()

onMounted(() => {
  cart.init(props.company.slug)
  checkout.setCompany(props.company, props.deliveryZones)
})

watch(() => checkout.address.district, () => checkout.resolveDeliveryFee())

const currentDeliveryFee = computed(() =>
  checkout.orderType === 'delivery' ? checkout.deliveryFee : 0
)
const currentTotal       = computed(() => totalAmount.value)
const discount           = computed(() => checkout.discount)

const canSubmit = computed(() =>
  cart.count > 0 &&
  checkout.customer.name &&
  checkout.customer.phone &&
  checkout.paymentMethod &&
  (checkout.orderType !== 'delivery' || (checkout.address.street && checkout.address.number && checkout.address.district && checkout.address.city))
)

async function submit() {
  const result = await placeOrder(props.company.slug)
  if (result.success) {
    router.visit(route('store.success', [props.company.slug, result.orderCode]))
  }
}
</script>

<template>
  <StoreLayout :company="company" title="Checkout">
    <StoreHeader
      :company="company"
      :show-cart="false"
      show-back
      :back-href="route('store.cart', company.slug)"
      back-label="Carrinho"
    />

    <div class="max-w-3xl mx-auto px-4 py-5 pb-28 space-y-5">
      <h1 class="font-bold text-xl text-gray-900">Finalizar pedido</h1>

      <!-- Empty cart guard -->
      <div v-if="cart.count === 0" class="text-center py-10">
        <p class="text-gray-500 mb-4">Seu carrinho está vazio.</p>
        <a :href="route('store.home', company.slug)" class="text-orange-500 underline text-sm">
          Voltar ao cardápio
        </a>
      </div>

      <template v-else>
        <!-- 1. Customer data -->
        <section class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
          <h2 class="font-semibold text-gray-900 mb-3">
            <i class="pi pi-user mr-2 text-gray-400" />Seus dados
          </h2>
          <div class="space-y-3">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Nome *</label>
              <input
                v-model="checkout.customer.name"
                type="text"
                placeholder="Seu nome"
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                :class="errors['customer.name'] ? 'border-red-400' : ''"
              />
              <p v-if="errors['customer.name']" class="text-xs text-red-500 mt-0.5">{{ errors['customer.name'][0] }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">WhatsApp / Telefone *</label>
              <input
                v-model="checkout.customer.phone"
                type="tel"
                placeholder="(11) 99999-9999"
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                :class="errors['customer.phone'] ? 'border-red-400' : ''"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">E-mail (opcional)</label>
              <input
                v-model="checkout.customer.email"
                type="email"
                placeholder="seu@email.com"
                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
              />
            </div>
          </div>
        </section>

        <!-- 2. Delivery type -->
        <section class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
          <h2 class="font-semibold text-gray-900 mb-3">
            <i class="pi pi-truck mr-2 text-gray-400" />Como quer receber?
          </h2>
          <DeliveryTypeSelector
            v-model="checkout.orderType"
            :pickup-enabled="company.pickup_enabled"
            @update:model-value="checkout.setOrderType"
          />
        </section>

        <!-- 3. Address (delivery only) -->
        <section
          v-if="checkout.orderType === 'delivery'"
          class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"
        >
          <h2 class="font-semibold text-gray-900 mb-3">
            <i class="pi pi-map-marker mr-2 text-gray-400" />Endereço de entrega
          </h2>
          <AddressForm
            v-model="checkout.address"
            :errors="{
              street:   errors['address.street']?.[0],
              number:   errors['address.number']?.[0],
              district: errors['address.district']?.[0],
              city:     errors['address.city']?.[0],
            }"
          />

          <!-- Zone info -->
          <div v-if="checkout.matchedZone" class="mt-3 flex items-center gap-2 text-sm text-green-700 bg-green-50 rounded-xl p-3">
            <i class="pi pi-check-circle" />
            <span>
              Zona: <strong>{{ checkout.matchedZone.name }}</strong>
              · Entrega em ~{{ checkout.matchedZone.estimated_time }} min
            </span>
          </div>
        </section>

        <!-- 4. Coupon -->
        <section class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
          <h2 class="font-semibold text-gray-900 mb-3">
            <i class="pi pi-ticket mr-2 text-gray-400" />Cupom de desconto
          </h2>
          <CouponInput
            :company-slug="company.slug"
            :subtotal="cart.subtotal"
          />
        </section>

        <!-- 5. Payment -->
        <section class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
          <h2 class="font-semibold text-gray-900 mb-3">
            <i class="pi pi-credit-card mr-2 text-gray-400" />Forma de pagamento
          </h2>
          <PaymentMethodSelector
            :methods="company.accepted_payment_methods ?? ['cash', 'pix']"
            v-model="checkout.paymentMethod"
          />

          <!-- Change for cash -->
          <div v-if="checkout.paymentMethod === 'cash'" class="mt-3">
            <label class="block text-xs font-medium text-gray-600 mb-1">Troco para (opcional)</label>
            <input
              v-model="checkout.changeFor"
              type="number"
              step="0.01"
              :placeholder="`R$ ${currentTotal.toFixed(2)}`"
              class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
            />
          </div>

          <p v-if="errors['payment_method']" class="text-xs text-red-500 mt-1.5">
            {{ errors['payment_method'][0] }}
          </p>
        </section>

        <!-- 6. Notes -->
        <section class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
          <h2 class="font-semibold text-gray-900 mb-3">
            <i class="pi pi-comment mr-2 text-gray-400" />Observações do pedido
          </h2>
          <textarea
            v-model="checkout.notes"
            rows="2"
            placeholder="Algum pedido especial para este pedido? (opcional)"
            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-orange-300"
          />
        </section>

        <!-- Order summary -->
        <CartSummary
          :subtotal="cart.subtotal"
          :delivery-fee="currentDeliveryFee"
          :discount="discount"
          :total="currentTotal"
          :order-type="checkout.orderType"
        />

        <!-- Global errors -->
        <div v-if="errors.order" class="bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700">
          <i class="pi pi-exclamation-circle mr-2" />{{ errors.order[0] }}
        </div>

        <!-- Submit -->
        <button
          :disabled="!canSubmit || loading"
          class="w-full py-4 rounded-2xl text-white font-bold text-base transition-opacity disabled:opacity-50 flex items-center justify-center gap-2"
          :style="{ background: 'var(--store-primary, #F97316)' }"
          @click="submit"
        >
          <i v-if="loading" class="pi pi-spinner pi-spin" />
          <span>{{ loading ? 'Enviando pedido...' : 'Confirmar pedido' }}</span>
        </button>
      </template>
    </div>
  </StoreLayout>
</template>
