<template>
  <div :class="wrapperClasses">
    <!-- Header -->
    <div v-if="hasHeader" class="px-6 py-4 border-b border-gray-200">
      <slot name="header">
        <div class="flex items-center justify-between">
          <h3 v-if="title" class="text-lg font-medium text-gray-900">{{ title }}</h3>
          <div v-if="hasHeaderActions" class="flex items-center space-x-3">
            <slot name="header-actions" />
          </div>
        </div>
      </slot>
    </div>

    <!-- Filters -->
    <div v-if="hasFilters" class="px-6 py-3 bg-gray-50 border-b border-gray-200">
      <slot name="filters" />
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <LoadingSpinner size="lg" />
      <span class="ml-3 text-gray-600">{{ loadingText }}</span>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <!-- Table Header -->
        <thead class="bg-gray-50">
          <tr>
            <!-- Select all checkbox -->
            <th v-if="selectable" class="px-6 py-3 text-left">
              <input
                type="checkbox"
                :checked="isAllSelected"
                :indeterminate="isIndeterminate"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                @change="toggleSelectAll"
              />
            </th>
            
            <!-- Column headers -->
            <th
              v-for="column in columns"
              :key="column.key"
              :class="getHeaderClasses(column)"
              @click="handleSort(column)"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.label }}</span>
                <div v-if="column.sortable" class="flex flex-col">
                  <Icon
                    name="chevron-up"
                    size="xs"
                    :class="getSortIconClasses(column, 'asc')"
                  />
                  <Icon
                    name="chevron-down"
                    size="xs"
                    :class="getSortIconClasses(column, 'desc')"
                  />
                </div>
              </div>
            </th>
            
            <!-- Actions column -->
            <th v-if="hasActions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="bg-white divide-y divide-gray-200">
          <!-- Empty state -->
          <tr v-if="!data.length">
            <td :colspan="totalColumns" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center">
                <Icon name="inbox" size="xl" class="text-gray-400 mb-3" />
                <p class="text-gray-500">{{ emptyText }}</p>
              </div>
            </td>
          </tr>

          <!-- Data rows -->
          <tr
            v-for="(item, index) in data"
            :key="getRowKey(item, index)"
            :class="getRowClasses(item, index)"
            @click="handleRowClick(item, index)"
          >
            <!-- Select checkbox -->
            <td v-if="selectable" class="px-6 py-4">
              <input
                type="checkbox"
                :checked="isSelected(item)"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                @change="toggleSelect(item)"
                @click.stop
              />
            </td>

            <!-- Data cells -->
            <td
              v-for="column in columns"
              :key="column.key"
              :class="getCellClasses(column)"
            >
              <slot
                :name="`cell-${column.key}`"
                :item="item"
                :value="getColumnValue(item, column.key)"
                :index="index"
              >
                {{ formatColumnValue(item, column) }}
              </slot>
            </td>

            <!-- Actions cell -->
            <td v-if="hasActions" class="px-6 py-4 text-right text-sm font-medium">
              <slot name="actions" :item="item" :index="index" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer -->
    <div v-if="hasFooter" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
      <slot name="footer">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ data.length }} de {{ total }} resultados
          </div>
          <div v-if="pagination">
            <slot name="pagination" />
          </div>
        </div>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, useSlots } from 'vue'
import Icon from '@/Components/UI/Icon.vue'
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'

const props = defineProps({
  // Datos
  data: {
    type: Array,
    default: () => []
  },
  columns: {
    type: Array,
    required: true
  },
  
  // Configuración
  title: String,
  loading: Boolean,
  loadingText: {
    type: String,
    default: 'Cargando...'
  },
  emptyText: {
    type: String,
    default: 'No hay datos disponibles'
  },
  
  // Selección
  selectable: Boolean,
  selected: {
    type: Array,
    default: () => []
  },
  rowKey: {
    type: String,
    default: 'id'
  },
  
  // Ordenamiento
  sortBy: String,
  sortDirection: {
    type: String,
    default: 'asc'
  },
  
  // Paginación
  pagination: Boolean,
  total: {
    type: Number,
    default: 0
  },
  
  // Estilos
  striped: Boolean,
  hoverable: {
    type: Boolean,
    default: true
  },
  clickableRows: Boolean,
})

const emit = defineEmits(['sort', 'select', 'select-all', 'row-click'])

const slots = useSlots()

// Estados computados
const hasHeader = computed(() => !!slots.header || !!props.title || hasHeaderActions.value)
const hasHeaderActions = computed(() => !!slots['header-actions'])
const hasFilters = computed(() => !!slots.filters)
const hasActions = computed(() => !!slots.actions)
const hasFooter = computed(() => !!slots.footer || props.pagination)

const totalColumns = computed(() => {
  let count = props.columns.length
  if (props.selectable) count++
  if (hasActions.value) count++
  return count
})

// Selección
const isAllSelected = computed(() => {
  return props.data.length > 0 && props.selected.length === props.data.length
})

const isIndeterminate = computed(() => {
  return props.selected.length > 0 && props.selected.length < props.data.length
})

// Clases
const wrapperClasses = computed(() => {
  return 'bg-white shadow rounded-lg overflow-hidden'
})

const getHeaderClasses = (column) => {
  const classes = [
    'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'
  ]
  
  if (column.sortable) {
    classes.push('cursor-pointer hover:bg-gray-100')
  }
  
  return classes.join(' ')
}

const getRowClasses = (item, index) => {
  const classes = []
  
  if (props.striped && index % 2 === 1) {
    classes.push('bg-gray-50')
  }
  
  if (props.hoverable) {
    classes.push('hover:bg-gray-50')
  }
  
  if (props.clickableRows) {
    classes.push('cursor-pointer')
  }
  
  return classes.join(' ')
}

const getCellClasses = (column) => {
  const classes = ['px-6 py-4 whitespace-nowrap']
  
  if (column.align === 'center') {
    classes.push('text-center')
  } else if (column.align === 'right') {
    classes.push('text-right')
  }
  
  return classes.join(' ')
}

const getSortIconClasses = (column, direction) => {
  const classes = ['text-gray-400']
  
  if (props.sortBy === column.key && props.sortDirection === direction) {
    classes.push('text-blue-600')
  }
  
  return classes.join(' ')
}

// Métodos
const getRowKey = (item, index) => {
  return item[props.rowKey] || index
}

const getColumnValue = (item, key) => {
  return key.split('.').reduce((obj, k) => obj?.[k], item)
}

const formatColumnValue = (item, column) => {
  const value = getColumnValue(item, column.key)
  
  if (column.formatter && typeof column.formatter === 'function') {
    return column.formatter(value, item)
  }
  
  return value
}

const isSelected = (item) => {
  const key = item[props.rowKey]
  return props.selected.some(selected => selected[props.rowKey] === key)
}

// Manejadores de eventos
const handleSort = (column) => {
  if (!column.sortable) return
  
  let direction = 'asc'
  if (props.sortBy === column.key && props.sortDirection === 'asc') {
    direction = 'desc'
  }
  
  emit('sort', { column: column.key, direction })
}

const toggleSelect = (item) => {
  emit('select', item)
}

const toggleSelectAll = () => {
  emit('select-all', !isAllSelected.value)
}

const handleRowClick = (item, index) => {
  if (props.clickableRows) {
    emit('row-click', item, index)
  }
}
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
