import axios from 'axios'
import { defineStore } from 'pinia'

/**
 * Content Management Store
 * Manages state for content CRUD operations, pagination, and filtering
 */
export const useContentStore = defineStore('content', {
  state: () => ({
    // Content data
    contents: [],
    currentContent: null,
    
    // Pagination
    pagination: {
      currentPage: 1,
      lastPage: 1,
      perPage: 12,
      total: 0,
      from: 0,
      to: 0,
    },
    
    // Loading states
    loading: false,
    saving: false,
    deleting: false,
    
    // Filters and search
    filters: {
      search: '',
      type: '',
      status: '',
      menu_id: '',
      sort_by: 'created_at',
      sort_direction: 'desc',
    },
    
    // Available options
    contentTypes: [
      { value: 'custom_html', label: 'Custom HTML' },
      { value: 'embedded_url', label: 'Embedded URL' },
      { value: 'file_upload', label: 'File Upload' },
    ],
    
    statuses: [
      { value: 'published', label: 'Published' },
      { value: 'draft', label: 'Draft' },
      { value: 'archived', label: 'Archived' },
    ],
    
    // Error handling
    error: null,
    validationErrors: {},
  }),

  getters: {
    /**
     * Get filtered and sorted contents
     */
    filteredContents: state => {
      let filtered = [...state.contents]
      
      if (state.filters.search) {
        const search = state.filters.search.toLowerCase()

        filtered = filtered.filter(content =>
          content.title?.toLowerCase().includes(search) ||
          content.description?.toLowerCase().includes(search),
        )
      }
      
      if (state.filters.type) {
        filtered = filtered.filter(content => content.type === state.filters.type)
      }
      
      if (state.filters.status) {
        filtered = filtered.filter(content => content.status === state.filters.status)
      }
      
      if (state.filters.menu_id) {
        filtered = filtered.filter(content => content.menu_id === state.filters.menu_id)
      }
      
      return filtered
    },

    /**
     * Check if there are any active filters
     */
    hasActiveFilters: state => {
      return state.filters.search ||
             state.filters.type ||
             state.filters.status ||
             state.filters.menu_id
    },

    /**
     * Get content by ID
     */
    getContentById: state => id => {
      return state.contents.find(content => content.id === id)
    },

    /**
     * Check if user can perform actions based on permissions
     */
    canCreate: () => {
      // This should be integrated with your permission system
      return true // TODO: Implement permission check
    },

    canEdit: () => content => {
      // This should be integrated with your permission system
      return true // TODO: Implement permission check
    },

    canDelete: () => content => {
      // This should be integrated with your permission system
      return true // TODO: Implement permission check
    },
  },

  actions: {
    /**
     * Fetch contents with pagination and filters
     */
    async fetchContents(page = 1, preserveState = false) {
      if (!preserveState) {
        this.loading = true
        this.error = null
      }

      try {
        const params = {
          page,
          per_page: this.pagination.perPage,
          search: this.filters.search || undefined,
          type: this.filters.type || undefined,
          status: this.filters.status || undefined,
          menu_id: this.filters.menu_id || undefined,
          sort_by: this.filters.sort_by,
          sort_direction: this.filters.sort_direction,
        }

        // Remove undefined values
        Object.keys(params).forEach(key => 
          params[key] === undefined && delete params[key],
        )

        const response = await axios.get('/api/admin/contents', { params })
        
        this.contents = response.data.data
        this.pagination = {
          currentPage: response.data.current_page,
          lastPage: response.data.last_page,
          perPage: response.data.per_page,
          total: response.data.total,
          from: response.data.from,
          to: response.data.to,
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch contents'
        console.error('Error fetching contents:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Fetch single content by ID
     */
    async fetchContent(id) {
      this.loading = true
      this.error = null

      try {
        const response = await axios.get(`/api/admin/contents/${id}`)

        this.currentContent = response.data.data
        
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch content'
        console.error('Error fetching content:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Create new content
     */
    async createContent(contentData) {
      this.saving = true
      this.error = null
      this.validationErrors = {}

      try {
        const formData = new FormData()
        
        // Append all content data to FormData
        Object.keys(contentData).forEach(key => {
          if (contentData[key] !== null && contentData[key] !== undefined) {
            if (key === 'file' && contentData[key] instanceof File) {
              formData.append(key, contentData[key])
            } else if (typeof contentData[key] === 'object') {
              formData.append(key, JSON.stringify(contentData[key]))
            } else {
              formData.append(key, contentData[key])
            }
          }
        })

        const response = await axios.post('/api/admin/contents', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })

        // Add to contents array
        this.contents.unshift(response.data.data)
        this.pagination.total++

        return response.data.data
      } catch (error) {
        if (error.response?.status === 422) {
          this.validationErrors = error.response.data.errors
        } else {
          this.error = error.response?.data?.message || 'Failed to create content'
        }
        console.error('Error creating content:', error)
        throw error
      } finally {
        this.saving = false
      }
    },

    /**
     * Update existing content
     */
    async updateContent(id, contentData) {
      this.saving = true
      this.error = null
      this.validationErrors = {}

      try {
        const formData = new FormData()

        formData.append('_method', 'PUT')
        
        // Append all content data to FormData
        Object.keys(contentData).forEach(key => {
          if (contentData[key] !== null && contentData[key] !== undefined) {
            if (key === 'file' && contentData[key] instanceof File) {
              formData.append(key, contentData[key])
            } else if (typeof contentData[key] === 'object') {
              formData.append(key, JSON.stringify(contentData[key]))
            } else {
              formData.append(key, contentData[key])
            }
          }
        })

        const response = await axios.post(`/api/admin/contents/${id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })

        // Update in contents array
        const index = this.contents.findIndex(content => content.id === id)
        if (index !== -1) {
          this.contents.splice(index, 1, response.data.data)
        }

        // Update current content if it's the same
        if (this.currentContent?.id === id) {
          this.currentContent = response.data.data
        }

        return response.data.data
      } catch (error) {
        if (error.response?.status === 422) {
          this.validationErrors = error.response.data.errors
        } else {
          this.error = error.response?.data?.message || 'Failed to update content'
        }
        console.error('Error updating content:', error)
        throw error
      } finally {
        this.saving = false
      }
    },

    /**
     * Delete content
     */
    async deleteContent(id) {
      this.deleting = true
      this.error = null

      try {
        await axios.delete(`/api/admin/contents/${id}`)

        // Remove from contents array
        const index = this.contents.findIndex(content => content.id === id)
        if (index !== -1) {
          this.contents.splice(index, 1)
          this.pagination.total--
        }

        // Clear current content if it's the deleted one
        if (this.currentContent?.id === id) {
          this.currentContent = null
        }

        return true
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete content'
        console.error('Error deleting content:', error)
        throw error
      } finally {
        this.deleting = false
      }
    },

    /**
     * Update content status (publish/unpublish/archive)
     */
    async updateContentStatus(id, status) {
      this.error = null

      try {
        const response = await axios.patch(`/api/admin/contents/${id}/status`, {
          status,
        })

        // Update in contents array
        const index = this.contents.findIndex(content => content.id === id)
        if (index !== -1) {
          this.contents[index].status = status
          this.contents[index].updated_at = response.data.data.updated_at
        }

        // Update current content if it's the same
        if (this.currentContent?.id === id) {
          this.currentContent.status = status
          this.currentContent.updated_at = response.data.data.updated_at
        }

        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update content status'
        console.error('Error updating content status:', error)
        throw error
      }
    },

    /**
     * Update filters and refresh contents
     */
    async updateFilters(newFilters) {
      this.filters = { ...this.filters, ...newFilters }
      await this.fetchContents(1)
    },

    /**
     * Clear all filters
     */
    async clearFilters() {
      this.filters = {
        search: '',
        type: '',
        status: '',
        menu_id: '',
        sort_by: 'created_at',
        sort_direction: 'desc',
      }
      await this.fetchContents(1)
    },

    /**
     * Change page
     */
    async changePage(page) {
      await this.fetchContents(page)
    },

    /**
     * Change items per page
     */
    async changePerPage(perPage) {
      this.pagination.perPage = perPage
      await this.fetchContents(1)
    },

    /**
     * Refresh current page
     */
    async refresh() {
      await this.fetchContents(this.pagination.currentPage, true)
    },

    /**
     * Clear all state
     */
    clearState() {
      this.contents = []
      this.currentContent = null
      this.error = null
      this.validationErrors = {}
      this.pagination = {
        currentPage: 1,
        lastPage: 1,
        perPage: 12,
        total: 0,
        from: 0,
        to: 0,
      }
    },

    /**
     * Set current content
     */
    setCurrentContent(content) {
      this.currentContent = content
    },

    /**
     * Clear errors
     */
    clearErrors() {
      this.error = null
      this.validationErrors = {}
    },
  },
})
