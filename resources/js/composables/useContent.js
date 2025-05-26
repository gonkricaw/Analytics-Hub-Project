import { computed, ref } from 'vue'
import { useApi } from './useApi'
import { useAuth } from './useAuth'

const contents = ref([])
const currentContent = ref(null)
const loading = ref(false)
const error = ref(null)

export function useContent() {
  const { apiCall } = useApi()
  const { user } = useAuth()

  const fetchContents = async (filters = {}) => {
    if (!user.value) return

    loading.value = true
    error.value = null

    try {
      const params = new URLSearchParams(filters).toString()
      const url = `/contents${params ? `?${params}` : ''}`
      
      const response = await apiCall(url, 'GET')
      if (response.success) {
        contents.value = response.data
      } else {
        throw new Error(response.message || 'Failed to fetch contents')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error fetching contents:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchContentBySlug = async slug => {
    if (!user.value) return null

    loading.value = true
    error.value = null

    try {
      const response = await apiCall(`/contents/slug/${slug}`, 'GET')
      if (response.success) {
        currentContent.value = response.data
        
        return response.data
      } else {
        throw new Error(response.message || 'Content not found')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error fetching content:', err)
      
      return null
    } finally {
      loading.value = false
    }
  }

  const fetchContentById = async id => {
    if (!user.value) return null

    loading.value = true
    error.value = null

    try {
      const response = await apiCall(`/contents/${id}`, 'GET')
      if (response.success) {
        currentContent.value = response.data
        
        return response.data
      } else {
        throw new Error(response.message || 'Content not found')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error fetching content:', err)
      
      return null
    } finally {
      loading.value = false
    }
  }

  const getContentsByType = type => {
    return computed(() => 
      contents.value.filter(content => content.type === type),
    )
  }

  const clearContents = () => {
    contents.value = []
    currentContent.value = null
    error.value = null
  }

  const clearCurrentContent = () => {
    currentContent.value = null
  }

  return {
    contents: computed(() => contents.value),
    currentContent: computed(() => currentContent.value),
    loading: computed(() => loading.value),
    error: computed(() => error.value),
    fetchContents,
    fetchContentBySlug,
    fetchContentById,
    getContentsByType,
    clearContents,
    clearCurrentContent,
  }
}

// Global state management
const globalContentState = {
  contents,
  currentContent,
  loading,
  error,
}

export function useContentStore() {
  const { user } = useAuth()
  const { apiCall } = useApi()

  const fetchContents = async (filters = {}) => {
    if (!user.value) return

    globalContentState.loading.value = true
    globalContentState.error.value = null

    try {
      const params = new URLSearchParams(filters).toString()
      const url = `/contents${params ? `?${params}` : ''}`
      
      const response = await apiCall(url, 'GET')
      if (response.success) {
        globalContentState.contents.value = response.data
      } else {
        throw new Error(response.message || 'Failed to fetch contents')
      }
    } catch (err) {
      globalContentState.error.value = err.message
      console.error('Error fetching contents:', err)
    } finally {
      globalContentState.loading.value = false
    }
  }

  const fetchContentBySlug = async slug => {
    if (!user.value) return null

    globalContentState.loading.value = true
    globalContentState.error.value = null

    try {
      const response = await apiCall(`/contents/slug/${slug}`, 'GET')
      if (response.success) {
        globalContentState.currentContent.value = response.data
        
        return response.data
      } else {
        throw new Error(response.message || 'Content not found')
      }
    } catch (err) {
      globalContentState.error.value = err.message
      console.error('Error fetching content:', err)
      
      return null
    } finally {
      globalContentState.loading.value = false
    }
  }

  const clearContents = () => {
    globalContentState.contents.value = []
    globalContentState.currentContent.value = null
    globalContentState.error.value = null
  }

  return {
    contents: computed(() => globalContentState.contents.value),
    currentContent: computed(() => globalContentState.currentContent.value),
    loading: computed(() => globalContentState.loading.value),
    error: computed(() => globalContentState.error.value),
    fetchContents,
    fetchContentBySlug,
    clearContents,
  }
}
