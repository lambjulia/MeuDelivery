import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useStorefrontStore = defineStore('storefront', () => {
  const company       = ref(null)
  const categories    = ref([])
  const activeCategory = ref(null)  // slug
  const searchQuery   = ref('')

  const filteredCategories = computed(() => {
    if (! searchQuery.value.trim()) return categories.value

    const q = searchQuery.value.toLowerCase()
    return categories.value
      .map(cat => ({
        ...cat,
        products: (cat.products ?? []).filter(p =>
          p.name.toLowerCase().includes(q) ||
          p.description?.toLowerCase().includes(q)
        ),
      }))
      .filter(cat => cat.products.length > 0)
  })

  const allProducts = computed(() =>
    categories.value.flatMap(c => c.products ?? [])
  )

  function init(companyData, categoriesData) {
    company.value    = companyData
    categories.value = categoriesData
    if (categoriesData.length > 0 && ! activeCategory.value) {
      activeCategory.value = categoriesData[0].slug
    }
  }

  return {
    company, categories, activeCategory, searchQuery,
    filteredCategories, allProducts,
    init,
  }
})
