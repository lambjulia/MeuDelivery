import { createI18n } from 'vue-i18n'
import en from './en.js'
import pt_BR from './pt_BR.js'
import es from './es.js'

const savedLocale = localStorage.getItem('locale') || 'pt_BR'

const i18n = createI18n({
  legacy: false,
  locale: savedLocale,
  fallbackLocale: 'en',
  messages: {
    en,
    pt_BR,
    es,
  },
})

export default i18n
