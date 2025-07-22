import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Composable para manejar tablas de manera reutilizable
 * 
 * Proporciona funcionalidades comunes como:
 * - Ordenamiento
 * - Filtrado
 * - Paginación
 * - Selección múltiple
 * - Búsqueda
 */
export function useTable(options = {}) {
  // Configuración por defecto
  const defaultOptions = {
    defaultSort: null,
    defaultSortDirection: 'asc',
    defaultPerPage: 10,
    preserveScroll: true,
    preserveState: true,
    debounceMs: 300,
  }
  
  const config = { ...defaultOptions, ...options }
  
  // Estado de la tabla
  const data = ref([])
  const loading = ref(false)
  const selected = ref([])
  const sortBy = ref(config.defaultSort)
  const sortDirection = ref(config.defaultSortDirection)
  const currentPage = ref(1)
  const perPage = ref(config.defaultPerPage)
  const search = ref('')
  const filters = ref({})
  const total = ref(0)
  const lastPage = ref(1)
  
  // Debounce timer para búsqueda
  let searchTimeout = null
  
  // Computadas
  const isAllSelected = computed(() => {
    return data.value.length > 0 && selected.value.length === data.value.length
  })
  
  const isIndeterminate = computed(() => {
    return selected.value.length > 0 && selected.value.length < data.value.length
  })
  
  const hasSelection = computed(() => selected.value.length > 0)
  
  const paginationInfo = computed(() => {
    const start = (currentPage.value - 1) * perPage.value + 1
    const end = Math.min(currentPage.value * perPage.value, total.value)
    
    return {
      start,
      end,
      total: total.value,
      currentPage: currentPage.value,
      lastPage: lastPage.value,
      perPage: perPage.value
    }
  })
  
  // Watchers
  watch(search, (newValue) => {
    if (searchTimeout) {
      clearTimeout(searchTimeout)
    }
    
    searchTimeout = setTimeout(() => {
      currentPage.value = 1 // Reset a primera página al buscar
      fetchData()
    }, config.debounceMs)
  })
  
  watch([sortBy, sortDirection, currentPage, perPage], () => {
    fetchData()
  })
  
  watch(filters, () => {
    currentPage.value = 1 // Reset a primera página al filtrar
    fetchData()
  }, { deep: true })
  
  // Métodos
  const setData = (newData, meta = {}) => {
    data.value = newData
    
    if (meta.total !== undefined) total.value = meta.total
    if (meta.current_page !== undefined) currentPage.value = meta.current_page
    if (meta.last_page !== undefined) lastPage.value = meta.last_page
    if (meta.per_page !== undefined) perPage.value = meta.per_page
  }
  
  const fetchData = async (url = null) => {
    if (!url && !config.endpoint) {
      console.warn('No endpoint provided for table data fetching')
      return
    }
    
    loading.value = true
    
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
      search: search.value,
      sort_by: sortBy.value,
      sort_direction: sortDirection.value,
      ...filters.value
    }
    
    // Limpiar parámetros vacíos
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key]
      }
    })
    
    try {
      const targetUrl = url || config.endpoint
      
      router.get(targetUrl, params, {
        preserveScroll: config.preserveScroll,
        preserveState: config.preserveState,
        onSuccess: (page) => {
          if (config.onSuccess) {
            config.onSuccess(page)
          }
        },
        onError: (errors) => {
          if (config.onError) {
            config.onError(errors)
          }
        },
        onFinish: () => {
          loading.value = false
        }
      })
    } catch (error) {
      console.error('Error fetching table data:', error)
      loading.value = false
    }
  }
  
  const sort = (column, direction = null) => {
    if (direction) {
      sortDirection.value = direction
    } else {
      // Toggle direction si es la misma columna
      if (sortBy.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
      } else {
        sortDirection.value = 'asc'
      }
    }
    
    sortBy.value = column
  }
  
  const goToPage = (page) => {
    if (page >= 1 && page <= lastPage.value) {
      currentPage.value = page
    }
  }
  
  const nextPage = () => {
    if (currentPage.value < lastPage.value) {
      currentPage.value++
    }
  }
  
  const previousPage = () => {
    if (currentPage.value > 1) {
      currentPage.value--
    }
  }
  
  const setPerPage = (newPerPage) => {
    perPage.value = newPerPage
    currentPage.value = 1
  }
  
  const setSearch = (searchTerm) => {
    search.value = searchTerm
  }
  
  const setFilter = (key, value) => {
    if (value === null || value === undefined || value === '') {
      delete filters.value[key]
    } else {
      filters.value[key] = value
    }
  }
  
  const clearFilters = () => {
    filters.value = {}
    search.value = ''
  }
  
  const select = (item, rowKey = 'id') => {
    const itemKey = item[rowKey]
    const index = selected.value.findIndex(s => s[rowKey] === itemKey)
    
    if (index > -1) {
      selected.value.splice(index, 1)
    } else {
      selected.value.push(item)
    }
  }
  
  const selectAll = (shouldSelect = true, rowKey = 'id') => {
    if (shouldSelect) {
      selected.value = [...data.value]
    } else {
      selected.value = []
    }
  }
  
  const clearSelection = () => {
    selected.value = []
  }
  
  const isSelected = (item, rowKey = 'id') => {
    return selected.value.some(s => s[rowKey] === item[rowKey])
  }
  
  const refresh = () => {
    fetchData()
  }
  
  const reset = () => {
    currentPage.value = 1
    search.value = ''
    filters.value = {}
    selected.value = []
    sortBy.value = config.defaultSort
    sortDirection.value = config.defaultSortDirection
    perPage.value = config.defaultPerPage
  }
  
  // Bulk actions
  const bulkAction = async (action, items = null) => {
    const targetItems = items || selected.value
    
    if (targetItems.length === 0) {
      console.warn('No items selected for bulk action')
      return
    }
    
    if (config.onBulkAction) {
      return await config.onBulkAction(action, targetItems)
    }
  }
  
  // Cleanup
  const cleanup = () => {
    if (searchTimeout) {
      clearTimeout(searchTimeout)
    }
  }
  
  return {
    // Datos
    data,
    selected,
    total,
    
    // Estados
    loading,
    sortBy,
    sortDirection,
    currentPage,
    perPage,
    search,
    filters,
    lastPage,
    
    // Computadas
    isAllSelected,
    isIndeterminate,
    hasSelection,
    paginationInfo,
    
    // Métodos de datos
    setData,
    fetchData,
    refresh,
    reset,
    
    // Métodos de ordenamiento
    sort,
    
    // Métodos de paginación
    goToPage,
    nextPage,
    previousPage,
    setPerPage,
    
    // Métodos de filtrado
    setSearch,
    setFilter,
    clearFilters,
    
    // Métodos de selección
    select,
    selectAll,
    clearSelection,
    isSelected,
    
    // Bulk actions
    bulkAction,
    
    // Cleanup
    cleanup,
  }
}
