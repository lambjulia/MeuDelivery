<script setup>
import { onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useTheme } from '@/composables/useTheme'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Checkbox from 'primevue/checkbox'

const { init, isDark, toggle } = useTheme()
onMounted(() => init())

defineProps({
  canResetPassword: Boolean,
  status: String,
})

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.transform(data => ({
    ...data,
    remember: form.remember ? 'on' : '',
  })).post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Entrar" />

  <div class="min-h-screen flex bg-surface-50 dark:bg-surface-900 transition-colors duration-200">

    <!-- Dark mode toggle -->
    <button
      class="absolute top-4 right-4 p-2 rounded-lg hover:bg-surface-200 dark:hover:bg-surface-700 transition-colors text-surface-500 dark:text-surface-400"
      :title="isDark ? 'Modo claro' : 'Modo escuro'"
      @click="toggle"
    >
      <i :class="['pi', isDark ? 'pi-sun' : 'pi-moon']" />
    </button>

    <!-- Left panel — branding (hidden on mobile) -->
    <div class="hidden lg:flex lg:w-1/2 flex-col justify-between p-12 bg-orange-500 text-white">
      <div class="flex items-center gap-3">
        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/20">
          <i class="pi pi-truck text-white text-xl" />
        </div>
        <span class="font-bold text-2xl tracking-tight">MeuDelivery</span>
      </div>

      <div>
        <h2 class="text-4xl font-bold leading-snug mb-4">
          Gerencie seus pedidos<br />com facilidade
        </h2>
        <p class="text-orange-100 text-lg leading-relaxed">
          Plataforma completa para delivery: pedidos, clientes, cardápio, entregadores e muito mais em um só lugar.
        </p>
      </div>

      <div class="flex items-center gap-6 text-orange-100 text-sm">
        <div class="flex items-center gap-2">
          <i class="pi pi-check-circle" />
          <span>Pedidos em tempo real</span>
        </div>
        <div class="flex items-center gap-2">
          <i class="pi pi-check-circle" />
          <span>Relatórios completos</span>
        </div>
        <div class="flex items-center gap-2">
          <i class="pi pi-check-circle" />
          <span>Multi-usuário</span>
        </div>
      </div>
    </div>

    <!-- Right panel — form -->
    <div class="flex flex-1 items-center justify-center px-6 py-12">
      <div class="w-full max-w-md">

        <!-- Mobile logo -->
        <div class="flex items-center gap-3 mb-8 lg:hidden">
          <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-orange-500">
            <i class="pi pi-truck text-white text-xl" />
          </div>
          <span class="font-bold text-2xl text-surface-900 dark:text-surface-0 tracking-tight">MeuDelivery</span>
        </div>

        <div class="bg-surface-0 dark:bg-surface-800 rounded-2xl shadow-sm border border-surface-200 dark:border-surface-700 p-8">
          <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0 mb-1">Bem-vindo de volta</h1>
          <p class="text-surface-500 dark:text-surface-400 text-sm mb-8">Entre com suas credenciais para continuar.</p>

          <!-- Status message (e.g. password reset link sent) -->
          <div
            v-if="status"
            class="flex items-center gap-2 mb-6 px-4 py-3 rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 text-green-700 dark:text-green-400 text-sm"
          >
            <i class="pi pi-check-circle" />
            {{ status }}
          </div>

          <form @submit.prevent="submit" class="space-y-5">
            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
                E-mail
              </label>
              <InputText
                id="email"
                v-model="form.email"
                type="email"
                placeholder="seu@email.com"
                autocomplete="username"
                autofocus
                required
                class="w-full"
                :invalid="!!form.errors.email"
              />
              <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div>
              <div class="flex justify-between items-center mb-1.5">
                <label for="password" class="block text-sm font-medium text-surface-700 dark:text-surface-300">
                  Senha
                </label>
                <Link
                  v-if="canResetPassword"
                  :href="route('password.request')"
                  class="text-xs text-orange-500 hover:text-orange-600 font-medium"
                >
                  Esqueceu a senha?
                </Link>
              </div>
              <Password
                id="password"
                v-model="form.password"
                placeholder="••••••••"
                autocomplete="current-password"
                :feedback="false"
                toggleMask
                required
                class="w-full"
                inputClass="w-full"
                :invalid="!!form.errors.password"
              />
              <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500">{{ form.errors.password }}</p>
            </div>

            <!-- Remember me -->
            <div class="flex items-center gap-2">
              <Checkbox v-model="form.remember" inputId="remember" binary />
              <label for="remember" class="text-sm text-surface-600 dark:text-surface-400 cursor-pointer select-none">
                Lembrar de mim
              </label>
            </div>

            <!-- Submit -->
            <Button
              type="submit"
              label="Entrar"
              icon="pi pi-sign-in"
              class="w-full"
              :loading="form.processing"
            />
          </form>
        </div>

        <p class="text-center text-xs text-surface-400 mt-6">
          &copy; {{ new Date().getFullYear() }} MeuDelivery. Todos os direitos reservados.
        </p>
      </div>
    </div>
  </div>
</template>
