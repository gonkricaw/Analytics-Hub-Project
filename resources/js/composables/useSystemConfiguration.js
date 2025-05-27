import { useApiClient } from '@/composables/useApiClient'
import { ref } from 'vue'

export function useSystemConfiguration() {
  const { apiCall } = useApiClient()
  
  const loading = ref(false)
  const error = ref(null)
  const configurations = ref([])
  const groupedConfigurations = ref({})

  /**
   * Get all system configurations
   */
  const getConfigurations = async (params = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiCall('/api/admin/system-configurations', {
        method: 'GET',
        params
      })
      
      configurations.value = response.data
      return response
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Get grouped system configurations
   */
  const getGroupedConfigurations = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiCall('/api/admin/system-configurations/grouped', {
        method: 'GET'
      })
      
      groupedConfigurations.value = response.data
      return response
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Get a specific system configuration
   */
  const getConfiguration = async (key) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiCall(`/api/admin/system-configurations/${key}`, {
        method: 'GET'
      })
      
      return response
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Create a new system configuration
   */
  const createConfiguration = async (configurationData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiCall('/api/admin/system-configurations', {
        method: 'POST',
        data: configurationData
      })
      
      return response
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Update a system configuration
   */
  const updateConfiguration = async (key, configurationData) => {
    loading.value = true
    error.value = null
    
    try {
      // Handle file uploads
      if (configurationData instanceof FormData) {
        const response = await apiCall(`/api/admin/system-configurations/${key}`, {
          method: 'PUT',
          data: configurationData,
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        return response
      }
      
      const response = await apiCall(`/api/admin/system-configurations/${key}`, {
        method: 'PUT',
        data: configurationData
      })
      
      return response
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Delete a system configuration
   */
  const deleteConfiguration = async (key) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiCall(`/api/admin/system-configurations/${key}`, {
        method: 'DELETE'
      })
      
      return response
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Bulk update multiple configurations
   */
  const bulkUpdateConfigurations = async (configurationsData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiCall('/api/admin/system-configurations/bulk-update', {
        method: 'POST',
        data: {
          configurations: configurationsData
        }
      })
      
      return response
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Get public system configurations (no auth required)
   */
  const getPublicConfigurations = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiCall('/api/system-configurations/public', {
        method: 'GET',
        requiresAuth: false
      })
      
      return response
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Upload file for configuration
   */
  const uploadConfigurationFile = async (key, file) => {
    const formData = new FormData()
    formData.append('value', file)
    formData.append('type', 'file')
    
    return await updateConfiguration(key, formData)
  }

  /**
   * Validate JSON configuration value
   */
  const validateJsonValue = (value) => {
    try {
      if (value.trim()) {
        JSON.parse(value)
      }
      return { valid: true, error: null }
    } catch (error) {
      return { valid: false, error: 'Invalid JSON format' }
    }
  }

  /**
   * Format configuration key for display
   */
  const formatConfigKey = (key) => {
    return key.split('_').map(word => 
      word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ')
  }

  /**
   * Format group name for display
   */
  const formatGroupName = (groupName) => {
    return groupName.charAt(0).toUpperCase() + groupName.slice(1).replace(/_/g, ' ')
  }

  /**
   * Get group icon
   */
  const getGroupIcon = (groupName) => {
    const icons = {
      dashboard: 'fas fa-tachometer-alt',
      app: 'fas fa-cog',
      login: 'fas fa-sign-in-alt',
      default: 'fas fa-folder'
    }
    return icons[groupName] || icons.default
  }

  /**
   * Check if file is image
   */
  const isImageFile = (filePath) => {
    if (!filePath) return false
    const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg']
    return imageExtensions.some(ext => filePath.toLowerCase().endsWith(ext))
  }

  /**
   * Get file URL
   */
  const getFileUrl = (filePath) => {
    return filePath ? `/storage/${filePath}` : ''
  }

  /**
   * Get file name from path
   */
  const getFileName = (filePath) => {
    return filePath ? filePath.split('/').pop() : ''
  }

  /**
   * Get file accept types for input
   */
  const getFileAccept = (key) => {
    if (key.includes('logo') || key.includes('background') || key.includes('photo')) {
      return 'image/*'
    }
    return '*/*'
  }

  return {
    // State
    loading,
    error,
    configurations,
    groupedConfigurations,
    
    // Methods
    getConfigurations,
    getGroupedConfigurations,
    getConfiguration,
    createConfiguration,
    updateConfiguration,
    deleteConfiguration,
    bulkUpdateConfigurations,
    getPublicConfigurations,
    uploadConfigurationFile,
    
    // Utilities
    validateJsonValue,
    formatConfigKey,
    formatGroupName,
    getGroupIcon,
    isImageFile,
    getFileUrl,
    getFileName,
    getFileAccept
  }
}
