import { ref, reactive, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

/**
 * Composable para manejar formularios de manera reutilizable
 * 
 * Proporciona funcionalidades comunes como:
 * - Gestión de datos del formulario
 * - Validación
 * - Estados de carga
 * - Manejo de errores
 * - Envío de datos
 */
export function useForm(initialData = {}, options = {}) {
  // Configuración por defecto
  const defaultOptions = {
    resetOnSuccess: false,
    preserveScroll: false,
    preserveState: true,
    replace: false,
    onSuccess: null,
    onError: null,
    onFinish: null,
    transform: null,
  }
  
  const config = { ...defaultOptions, ...options }
  
  // Estado del formulario
  const data = reactive({ ...initialData })
  const errors = ref({})
  const processing = ref(false)
  const wasSuccessful = ref(false)
  const recentlySuccessful = ref(false)
  const isDirty = ref(false)
  
  // Timer para recentlySuccessful
  let recentlySuccessfulTimeoutId = null
  
  // Datos iniciales para reset
  const initialFormData = { ...initialData }
  
  // Computadas
  const hasErrors = computed(() => Object.keys(errors.value).length > 0)
  
  const isValid = computed(() => {
    // Aquí puedes agregar lógica de validación personalizada
    return !hasErrors.value
  })
  
  // Watchers
  watch(data, () => {
    isDirty.value = true
    // Limpiar errores cuando el usuario modifica los datos
    if (hasErrors.value) {
      clearErrors()
    }
  }, { deep: true })
  
  // Métodos
  const setData = (key, value) => {
    if (typeof key === 'object') {
      Object.assign(data, key)
    } else {
      data[key] = value
    }
  }
  
  const setError = (key, value) => {
    if (typeof key === 'object') {
      errors.value = { ...errors.value, ...key }
    } else {
      errors.value[key] = value
    }
  }
  
  const clearErrors = (key = null) => {
    if (key) {
      delete errors.value[key]
    } else {
      errors.value = {}
    }
  }
  
  const reset = (fields = null) => {
    if (fields) {
      const fieldsArray = Array.isArray(fields) ? fields : [fields]
      fieldsArray.forEach(field => {
        data[field] = initialFormData[field]
      })
    } else {
      Object.assign(data, initialFormData)
    }
    
    clearErrors()
    isDirty.value = false
  }
  
  const transform = (callback) => {
    config.transform = callback
    return { data, setData, setError, clearErrors, reset, submit, processing, errors, hasErrors, isValid, wasSuccessful, recentlySuccessful, isDirty }
  }
  
  const submit = (method, url, submitOptions = {}) => {
    const finalOptions = { ...config, ...submitOptions }
    
    processing.value = true
    wasSuccessful.value = false
    recentlySuccessful.value = false
    
    // Limpiar timeout anterior
    if (recentlySuccessfulTimeoutId) {
      clearTimeout(recentlySuccessfulTimeoutId)
    }
    
    // Transformar datos si es necesario
    let transformedData = { ...data }
    if (finalOptions.transform && typeof finalOptions.transform === 'function') {
      transformedData = finalOptions.transform(transformedData)
    }
    
    const requestOptions = {
      method: method.toUpperCase(),
      data: transformedData,
      preserveScroll: finalOptions.preserveScroll,
      preserveState: finalOptions.preserveState,
      replace: finalOptions.replace,
      onSuccess: (page) => {
        processing.value = false
        wasSuccessful.value = true
        recentlySuccessful.value = true
        
        // Auto-hide recentlySuccessful después de 2 segundos
        recentlySuccessfulTimeoutId = setTimeout(() => {
          recentlySuccessful.value = false
        }, 2000)
        
        if (finalOptions.resetOnSuccess) {
          reset()
        }
        
        if (finalOptions.onSuccess) {
          finalOptions.onSuccess(page)
        }
      },
      onError: (responseErrors) => {
        processing.value = false
        errors.value = responseErrors
        
        if (finalOptions.onError) {
          finalOptions.onError(responseErrors)
        }
      },
      onFinish: () => {
        processing.value = false
        
        if (finalOptions.onFinish) {
          finalOptions.onFinish()
        }
      }
    }
    
    router.visit(url, requestOptions)
  }
  
  // Métodos de conveniencia para diferentes tipos de request
  const get = (url, options = {}) => submit('get', url, options)
  const post = (url, options = {}) => submit('post', url, options)
  const put = (url, options = {}) => submit('put', url, options)
  const patch = (url, options = {}) => submit('patch', url, options)
  const del = (url, options = {}) => submit('delete', url, options)
  
  // Validación personalizada
  const validate = (rules = {}) => {
    const newErrors = {}
    
    Object.keys(rules).forEach(field => {
      const fieldRules = Array.isArray(rules[field]) ? rules[field] : [rules[field]]
      const value = data[field]
      
      for (const rule of fieldRules) {
        if (typeof rule === 'function') {
          const result = rule(value, data)
          if (result !== true) {
            newErrors[field] = result
            break
          }
        } else if (typeof rule === 'object') {
          // Reglas predefinidas
          if (rule.required && (!value || value.toString().trim() === '')) {
            newErrors[field] = rule.message || `${field} es requerido`
            break
          }
          
          if (rule.min && value && value.length < rule.min) {
            newErrors[field] = rule.message || `${field} debe tener al menos ${rule.min} caracteres`
            break
          }
          
          if (rule.max && value && value.length > rule.max) {
            newErrors[field] = rule.message || `${field} no puede tener más de ${rule.max} caracteres`
            break
          }
          
          if (rule.email && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            newErrors[field] = rule.message || `${field} debe ser un email válido`
            break
          }
        }
      }
    })
    
    errors.value = newErrors
    return Object.keys(newErrors).length === 0
  }
  
  // Limpiar timeout al desmontar
  const cleanup = () => {
    if (recentlySuccessfulTimeoutId) {
      clearTimeout(recentlySuccessfulTimeoutId)
    }
  }
  
  return {
    // Datos
    data,
    errors,
    
    // Estados
    processing,
    wasSuccessful,
    recentlySuccessful,
    isDirty,
    hasErrors,
    isValid,
    
    // Métodos
    setData,
    setError,
    clearErrors,
    reset,
    submit,
    transform,
    validate,
    cleanup,
    
    // Métodos de conveniencia
    get,
    post,
    put,
    patch,
    delete: del,
  }
}
