/**
 * API Client Composable
 * Provides centralized API communication with authentication, error handling, and response formatting
 */
import axios from 'axios'
import { computed, ref } from 'vue'

export function useApiClient() {
  const loading = ref(false)
  const error = ref(null)
  const lastResponse = ref(null)

  // Get CSRF token from meta tag
  const getCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]')
    
    return token ? token.getAttribute('content') : null
  }

  // Setup axios defaults
  const setupAxiosDefaults = () => {
    const token = getCsrfToken()
    if (token) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = token
    }
    
    // Set base URL if not already set
    if (!axios.defaults.baseURL) {
      axios.defaults.baseURL = window.location.origin
    }
    
    // Request interceptor
    axios.interceptors.request.use(
      config => {
        loading.value = true
        
        return config
      },
      error => {
        loading.value = false
        
        return Promise.reject(error)
      },
    )
    
    // Response interceptor
    axios.interceptors.response.use(
      response => {
        loading.value = false
        lastResponse.value = response
        
        return response
      },
      error => {
        loading.value = false
        handleApiError(error)
        
        return Promise.reject(error)
      },
    )
  }

  // Initialize axios
  setupAxiosDefaults()

  /**
   * Handle API errors consistently
   */
  const handleApiError = apiError => {
    let errorMessage = 'An unexpected error occurred'
    
    if (apiError.response) {
      // Server responded with error status
      const { status, data } = apiError.response
      
      switch (status) {
      case 401:
        errorMessage = 'Authentication required'

        // Redirect to login if needed
        break
      case 403:
        errorMessage = 'Access denied'
        break
      case 404:
        errorMessage = 'Resource not found'
        break
      case 422:
        errorMessage = data.message || 'Validation error'
        break
      case 500:
        errorMessage = 'Server error occurred'
        break
      default:
        errorMessage = data.message || `HTTP ${status} error`
      }
    } else if (apiError.request) {
      // Network error
      errorMessage = 'Network error - please check your connection'
    }
    
    error.value = {
      message: errorMessage,
      originalError: apiError,
      status: apiError.response?.status,
      data: apiError.response?.data,
    }
  }

  /**
   * Make API call with standardized options
   */
  const apiCall = async (url, options = {}) => {
    const {
      method = 'GET',
      data = null,
      params = {},
      headers = {},
      timeout = 30000,
      requiresAuth = true,
      ...axiosOptions
    } = options

    error.value = null
    
    try {
      // Advanced URL normalization to prevent duplicate /api/ prefixes
      let normalizedUrl = url
      
      // Handle multiple scenarios for URL normalization
      if (typeof normalizedUrl === 'string') {
        console.log('URL normalization - Original URL:', url)
        console.log('Axios baseURL:', axios.defaults.baseURL)
        
        // Check if axios already has /api as baseURL
        const hasApiBaseURL = axios.defaults.baseURL && axios.defaults.baseURL.includes('/api')
        
        if (hasApiBaseURL) {
          // If baseURL is /api, remove /api prefix from our URL to prevent duplication
          if (normalizedUrl.startsWith('/api/')) {
            normalizedUrl = normalizedUrl.substring(4) // Remove '/api' prefix
          }
        } else {
          // Remove any duplicate /api/api/ patterns (could be multiple)
          while (normalizedUrl.includes('/api/api/')) {
            normalizedUrl = normalizedUrl.replace('/api/api/', '/api/')
          }
          
          // Add /api prefix only if URL doesn't start with /api/ or http/https
          if (!normalizedUrl.startsWith('/api/') && !normalizedUrl.startsWith('http')) {
            normalizedUrl = `/api${normalizedUrl.startsWith('/') ? '' : '/'}${normalizedUrl}`
          }
        }
        
        // Remove duplicate slashes except for protocol separators
        normalizedUrl = normalizedUrl.replace(/([^:]\/)\/+/g, '$1')
        
        console.log('URL normalization - Final URL:', normalizedUrl)
      }
      
      const config = {
        method: method.toLowerCase(),
        url: normalizedUrl,
        timeout,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...headers,
        },
        ...axiosOptions,
      }

      if (data) {
        config.data = data
      }

      if (Object.keys(params).length > 0) {
        config.params = params
      }

      return await axios(config)
    } catch (err) {
      throw err
    }
  }

  /**
   * GET request helper
   */
  const get = async (url, params = {}, options = {}) => {
    return apiCall(url, { method: 'GET', params, ...options })
  }

  /**
   * POST request helper
   */
  const post = async (url, data = {}, options = {}) => {
    return apiCall(url, { method: 'POST', data, ...options })
  }

  /**
   * PUT request helper
   */
  const put = async (url, data = {}, options = {}) => {
    return apiCall(url, { method: 'PUT', data, ...options })
  }

  /**
   * PATCH request helper
   */
  const patch = async (url, data = {}, options = {}) => {
    return apiCall(url, { method: 'PATCH', data, ...options })
  }

  /**
   * DELETE request helper
   */
  const del = async (url, options = {}) => {
    return apiCall(url, { method: 'DELETE', ...options })
  }

  /**
   * Upload file helper
   */
  const upload = async (url, file, additionalData = {}, options = {}) => {
    const formData = new FormData()

    formData.append('file', file)
    
    // Add additional form data
    Object.keys(additionalData).forEach(key => {
      formData.append(key, additionalData[key])
    })

    return apiCall(url, {
      method: 'POST',
      data: formData,
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      ...options,
    })
  }

  /**
   * Download file helper
   */
  const download = async (url, filename = null, options = {}) => {
    try {
      const response = await apiCall(url, {
        responseType: 'blob',
        ...options,
      })

      // Create download link
      const blob = new Blob([response.data])
      const downloadUrl = window.URL.createObjectURL(blob)
      const link = document.createElement('a')

      link.href = downloadUrl
      link.download = filename || 'download'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(downloadUrl)

      return response
    } catch (err) {
      throw err
    }
  }

  /**
   * Clear error state
   */
  const clearError = () => {
    error.value = null
  }

  /**
   * Computed properties
   */
  const isLoading = computed(() => loading.value)
  const hasError = computed(() => error.value !== null)
  const errorMessage = computed(() => error.value?.message || null)

  return {
    // State
    loading: isLoading,
    error: hasError,
    errorMessage,
    lastResponse,
    
    // Methods
    apiCall,
    get,
    post,
    put,
    patch,
    delete: del,
    upload,
    download,
    clearError,
    handleApiError,
  }
}
