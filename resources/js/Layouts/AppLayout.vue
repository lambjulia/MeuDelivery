<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useTheme } from '@/composables/useTheme'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'

const { t } = useI18n()
const page = usePage()
const { isDark, toggle, init } = useTheme()

const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)

onMounted(() => {
  init()
  sidebarCollapsed.value = localStorage.getItem('sidebar-collapsed') === 'true'
})

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

function toggleCollapse() {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value)
}

const logout = () => router.post(route('logout'))

const navItems = [
  { label: 'nav.dashboard',       icon: 'pi-home',         route: 'dashboard' },
  { label: 'nav.orders',          icon: 'pi-shopping-cart', route: 'orders.index' },
  { label: 'nav.dispatchBoard',   icon: 'pi-map',           route: 'orders.dispatch-board' },
  { divider: true },
  { label: 'nav.customers',       icon: 'pi-users',         route: 'customers.index' },
  { label: 'nav.products',        icon: 'pi-box',           route: 'products.index' },
  { label: 'nav.categories',      icon: 'pi-tags',          route: 'categories.index' },
  { divider: true },
  { label: 'nav.deliveryZones',   icon: 'pi-map-marker',    route: 'delivery-zones.index' },
  { label: 'nav.deliveryDrivers', icon: 'pi-truck',         route: 'delivery-drivers.index' },
  { divider: true },
  { label: 'nav.coupons',         icon: 'pi-ticket',        route: 'coupons.index' },
  { label: 'nav.expenses',        icon: 'pi-wallet',        route: 'expenses.index' },
  { divider: true },
  { label: 'nav.settings',        icon: 'pi-cog',           route: 'settings.company' },
]

defineProps({ title: String })
</script>

<template>
  <div class="min-h-screen bg-surface-50 dark:bg-surface-900 text-surface-900 dark:text-surface-0 transition-colors duration-200">
    <Head :title="title" />
    <Toast position="top-right" />
    <ConfirmDialog />

    <!-- Sidebar Overlay (mobile) -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-20 bg-black/50 lg:hidden"
      @click="sidebarOpen = false"
    />

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed top-0 left-0 h-full z-30 flex flex-col bg-surface-0 dark:bg-surface-800 border-r border-surface-200 dark:border-surface-700 transition-all duration-300',
        sidebarCollapsed ? 'w-16' : 'w-64',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      ]"
    >
      <!-- Logo -->
      <div class="flex items-center gap-3 px-4 py-4 border-b border-surface-200 dark:border-surface-700 min-h-[64px]">
        <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-orange-500 shrink-0">
          <i class="pi pi-truck text-white text-lg" />
        </div>
        <span v-if="!sidebarCollapsed" class="font-bold text-lg text-surface-900 dark:text-surface-0 tracking-tight">
          MeuDelivery
        </span>
        <button
          v-if="!sidebarCollapsed"
          class="ml-auto p-1 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 hidden lg:flex"
          @click="toggleCollapse"
        >
          <i class="pi pi-chevron-left text-surface-500 text-sm" />
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto py-3 px-2">
        <template v-for="(item, idx) in navItems" :key="idx">
          <div v-if="item.divider" class="my-2 border-t border-surface-200 dark:border-surface-700" />
          <Link
            v-else
            :href="route(item.route)"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-lg mb-0.5 text-sm font-medium transition-colors group',
              route().current(item.route)
                ? 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400'
                : 'text-surface-600 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-700 hover:text-surface-900 dark:hover:text-surface-0',
            ]"
            :title="sidebarCollapsed ? t(item.label) : ''"
          >
            <i
              :class="[
                'pi', item.icon, 'text-base shrink-0',
                route().current(item.route) ? 'text-orange-500' : 'text-surface-400 group-hover:text-surface-600 dark:group-hover:text-surface-200',
              ]"
            />
            <span v-if="!sidebarCollapsed" class="truncate">{{ t(item.label) }}</span>
          </Link>
        </template>
      </nav>

      <!-- Bottom -->
      <div class="p-2 border-t border-surface-200 dark:border-surface-700">
        <button
          v-if="sidebarCollapsed"
          class="flex items-center justify-center w-full p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700"
          @click="toggleCollapse"
        >
          <i class="pi pi-chevron-right text-surface-500 text-sm" />
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <div :class="['transition-all duration-300', sidebarCollapsed ? 'lg:ml-16' : 'lg:ml-64']">
      <!-- Top Header -->
      <header class="sticky top-0 z-10 flex items-center gap-3 h-16 px-4 bg-surface-0 dark:bg-surface-800 border-b border-surface-200 dark:border-surface-700">
        <!-- Mobile menu button -->
        <button
          class="lg:hidden p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700"
          @click="toggleSidebar"
        >
          <i class="pi pi-bars text-surface-600 dark:text-surface-300" />
        </button>

        <!-- Page title slot -->
        <div class="flex-1">
          <slot name="header">
            <h1 v-if="title" class="text-lg font-semibold text-surface-900 dark:text-surface-0">{{ title }}</h1>
          </slot>
        </div>

        <!-- Right actions -->
        <div class="flex items-center gap-2">
          <!-- Dark mode toggle -->
          <button
            class="p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors"
            :title="isDark ? 'Light mode' : 'Dark mode'"
            @click="toggle"
          >
            <i :class="['pi text-surface-600 dark:text-surface-300', isDark ? 'pi-sun' : 'pi-moon']" />
          </button>

          <!-- Notifications placeholder -->
          <Link
            :href="route('dashboard')"
            class="relative p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700"
          >
            <i class="pi pi-bell text-surface-600 dark:text-surface-300" />
          </Link>

          <!-- User menu -->
          <div class="flex items-center gap-2 pl-2 border-l border-surface-200 dark:border-surface-700">
            <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center">
              <span class="text-xs font-bold text-orange-600 dark:text-orange-400">
                {{ $page.props.auth?.user?.name?.charAt(0)?.toUpperCase() }}
              </span>
            </div>
            <div class="hidden sm:block">
              <p class="text-sm font-medium text-surface-900 dark:text-surface-0 leading-tight">
                {{ $page.props.auth?.user?.name }}
              </p>
              <p class="text-xs text-surface-500 capitalize">
                {{ $page.props.auth?.user?.role }}
              </p>
            </div>
            <button
              class="p-1.5 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-500"
              @click="logout"
              :title="t('nav.logout')"
            >
              <i class="pi pi-sign-out text-sm" />
            </button>
          </div>
        </div>
      </header>

      <!-- Flash messages -->
      <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="px-6 pt-4">
        <div
          v-if="$page.props.flash?.success"
          class="flex items-center gap-2 px-4 py-3 rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 text-green-700 dark:text-green-400 text-sm"
        >
          <i class="pi pi-check-circle" />
          {{ $page.props.flash.success }}
        </div>
        <div
          v-if="$page.props.flash?.error"
          class="flex items-center gap-2 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 text-sm"
        >
          <i class="pi pi-exclamation-circle" />
          {{ $page.props.flash.error }}
        </div>
      </div>

      <!-- Page content -->
      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
