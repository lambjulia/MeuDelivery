import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function usePermissions() {
  const page = usePage()

  const user = computed(() => page.props.auth?.user)
  const role = computed(() => user.value?.role)

  const can = {
    manage: computed(() => ['owner', 'manager'].includes(role.value)),
    isOwner: computed(() => role.value === 'owner'),
    isManager: computed(() => role.value === 'manager'),
    viewOrders: computed(() => !!user.value?.company_id),
    createOrders: computed(() => !!user.value?.company_id),
  }

  return { user, role, can }
}
