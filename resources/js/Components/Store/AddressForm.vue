<script setup>
const props = defineProps({
  modelValue: { type: Object, required: true },
  errors:     { type: Object, default: () => ({}) },
})

const emit = defineEmits(['update:modelValue'])

function update(field, value) {
  emit('update:modelValue', { ...props.modelValue, [field]: value })
}
</script>

<template>
  <div class="space-y-3">
    <div class="grid grid-cols-3 gap-3">
      <div class="col-span-2">
        <label class="block text-xs font-medium text-gray-600 mb-1">Rua *</label>
        <input
          :value="modelValue.street"
          type="text"
          placeholder="Nome da rua"
          class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
          :class="errors.street ? 'border-red-400' : ''"
          @input="update('street', $event.target.value)"
        />
        <p v-if="errors.street" class="text-xs text-red-500 mt-0.5">{{ errors.street }}</p>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Número *</label>
        <input
          :value="modelValue.number"
          type="text"
          placeholder="Nº"
          class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
          :class="errors.number ? 'border-red-400' : ''"
          @input="update('number', $event.target.value)"
        />
      </div>
    </div>

    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Complemento</label>
      <input
        :value="modelValue.complement"
        type="text"
        placeholder="Apto, bloco, etc. (opcional)"
        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
        @input="update('complement', $event.target.value)"
      />
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Bairro *</label>
        <input
          :value="modelValue.district"
          type="text"
          placeholder="Bairro"
          class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
          :class="errors.district ? 'border-red-400' : ''"
          @input="update('district', $event.target.value)"
        />
        <p v-if="errors.district" class="text-xs text-red-500 mt-0.5">{{ errors.district }}</p>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Cidade *</label>
        <input
          :value="modelValue.city"
          type="text"
          placeholder="Cidade"
          class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
          :class="errors.city ? 'border-red-400' : ''"
          @input="update('city', $event.target.value)"
        />
      </div>
    </div>

    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Referência</label>
      <input
        :value="modelValue.reference"
        type="text"
        placeholder="Ponto de referência (opcional)"
        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
        @input="update('reference', $event.target.value)"
      />
    </div>
  </div>
</template>
