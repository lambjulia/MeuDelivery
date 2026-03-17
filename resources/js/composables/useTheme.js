import { ref, watchEffect, onMounted } from 'vue'

const STORAGE_KEY = 'meudelivery-theme'

const isDark = ref(false)

export function useTheme() {
  function applyTheme(dark) {
    if (dark) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
    localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light')
  }

  function toggle() {
    isDark.value = !isDark.value
    applyTheme(isDark.value)
  }

  function init() {
    const stored = localStorage.getItem(STORAGE_KEY)
    if (stored === 'dark') {
      isDark.value = true
    } else if (stored === null) {
      isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
    }
    applyTheme(isDark.value)
  }

  return { isDark, toggle, init }
}
