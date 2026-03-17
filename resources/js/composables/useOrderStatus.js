import { useI18n } from 'vue-i18n'

const statusConfig = {
  pending:          { color: 'warn',    icon: 'pi-clock' },
  confirmed:        { color: 'info',    icon: 'pi-check' },
  preparing:        { color: 'info',    icon: 'pi-spinner' },
  ready:            { color: 'success', icon: 'pi-check-circle' },
  out_for_delivery: { color: 'primary', icon: 'pi-send' },
  delivered:        { color: 'success', icon: 'pi-check-circle' },
  canceled:         { color: 'danger',  icon: 'pi-times-circle' },
}

export function useOrderStatus() {
  const { t } = useI18n()

  function getConfig(status) {
    return statusConfig[status] || { color: 'secondary', icon: 'pi-question' }
  }

  function getLabel(status) {
    return t(`orders.statuses.${status}`, status)
  }

  function getSeverity(status) {
    const colorMap = {
      warn: 'warn',
      info: 'info',
      success: 'success',
      primary: 'primary',
      danger: 'danger',
      secondary: 'secondary',
    }
    return colorMap[getConfig(status).color] || 'secondary'
  }

  function getIcon(status) {
    return getConfig(status).icon
  }

  const orderedStatuses = [
    'pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered',
  ]

  return { getLabel, getSeverity, getIcon, orderedStatuses }
}
