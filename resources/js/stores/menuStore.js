import axios from 'axios'
import { defineStore } from 'pinia'

/**
 * Menu Management Store
 * Manages state for menu CRUD operations, hierarchy, and permissions
 */
export const useMenuStore = defineStore('menu', {
  state: () => ({
    // Menu data
    menus: [],
    currentMenu: null,
    menuTree: [],
    
    // Loading states
    loading: false,
    saving: false,
    deleting: false,
    
    // Filters and search
    filters: {
      search: '',
      role: '',
      status: '',
      parent_id: null,
      sort_by: 'order',
      sort_direction: 'asc',
    },
    
    // Available options
    roles: [],
    parentMenus: [],
    
    // Error handling
    error: null,
    validationErrors: {},
  }),

  getters: {
    /**
     * Get filtered menus
     */
    filteredMenus: state => {
      let filtered = [...state.menus]
      
      if (state.filters.search) {
        const search = state.filters.search.toLowerCase()

        filtered = filtered.filter(menu =>
          menu.name?.toLowerCase().includes(search) ||
          menu.label?.toLowerCase().includes(search) ||
          menu.url?.toLowerCase().includes(search),
        )
      }
      
      if (state.filters.role) {
        filtered = filtered.filter(menu => 
          menu.roles && menu.roles.some(role => role.name === state.filters.role),
        )
      }
      
      if (state.filters.status) {
        filtered = filtered.filter(menu => menu.status === state.filters.status)
      }
      
      if (state.filters.parent_id !== null) {
        filtered = filtered.filter(menu => menu.parent_id === state.filters.parent_id)
      }
      
      return filtered
    },

    /**
     * Get menu tree structure
     */
    getMenuTree: state => {
      const buildTree = (menus, parentId = null) => {
        return menus
          .filter(menu => menu.parent_id === parentId)
          .sort((a, b) => a.order - b.order)
          .map(menu => ({
            ...menu,
            children: buildTree(menus, menu.id),
          }))
      }
      
      return buildTree(state.menus)
    },

    /**
     * Get menu by ID
     */
    getMenuById: state => id => {
      return state.menus.find(menu => menu.id === id)
    },

    /**
     * Get available parent menus (excluding current menu and its descendants)
     */
    getAvailableParents: state => (excludeId = null) => {
      if (!excludeId) return state.parentMenus
      
      const isDescendant = (menuId, ancestorId) => {
        const menu = state.menus.find(m => m.id === menuId)
        if (!menu || !menu.parent_id) return false
        if (menu.parent_id === ancestorId) return true
        
        return isDescendant(menu.parent_id, ancestorId)
      }
      
      return state.parentMenus.filter(menu => 
        menu.id !== excludeId && !isDescendant(menu.id, excludeId),
      )
    },

    /**
     * Check permissions
     */
    canCreate: () => {
      // TODO: Implement permission check
      return true
    },

    canEdit: () => menu => {
      // TODO: Implement permission check
      return true
    },

    canDelete: () => menu => {
      // TODO: Implement permission check
      return true
    },
  },

  actions: {
    /**
     * Fetch all menus
     */
    async fetchMenus() {
      this.loading = true
      this.error = null

      try {
        const params = {
          search: this.filters.search || undefined,
          role: this.filters.role || undefined,
          status: this.filters.status || undefined,
          parent_id: this.filters.parent_id,
          sort_by: this.filters.sort_by,
          sort_direction: this.filters.sort_direction,
          include: 'roles,children',
        }

        // Remove undefined values
        Object.keys(params).forEach(key => 
          params[key] === undefined && delete params[key],
        )

        const response = await axios.get('/api/admin/menus', { params })
        
        this.menus = response.data.data
        
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch menus'
        console.error('Error fetching menus:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Fetch single menu by ID
     */
    async fetchMenu(id) {
      this.loading = true
      this.error = null

      try {
        const response = await axios.get(`/api/admin/menus/${id}?include=roles,children,parent`)

        this.currentMenu = response.data.data
        
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch menu'
        console.error('Error fetching menu:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Create new menu
     */
    async createMenu(menuData) {
      this.saving = true
      this.error = null
      this.validationErrors = {}

      try {
        const response = await axios.post('/api/admin/menus', menuData)

        // Add to menus array
        this.menus.push(response.data.data)

        // Update parent menus if this is a parent-level menu
        if (!response.data.data.parent_id) {
          this.parentMenus.push({
            id: response.data.data.id,
            name: response.data.data.name,
            label: response.data.data.label,
          })
        }

        return response.data.data
      } catch (error) {
        if (error.response?.status === 422) {
          this.validationErrors = error.response.data.errors
        } else {
          this.error = error.response?.data?.message || 'Failed to create menu'
        }
        console.error('Error creating menu:', error)
        throw error
      } finally {
        this.saving = false
      }
    },

    /**
     * Update existing menu
     */
    async updateMenu(id, menuData) {
      this.saving = true
      this.error = null
      this.validationErrors = {}

      try {
        const response = await axios.put(`/api/admin/menus/${id}`, menuData)

        // Update in menus array
        const index = this.menus.findIndex(menu => menu.id === id)
        if (index !== -1) {
          this.menus.splice(index, 1, response.data.data)
        }

        // Update current menu if it's the same
        if (this.currentMenu?.id === id) {
          this.currentMenu = response.data.data
        }

        // Update parent menus
        const parentIndex = this.parentMenus.findIndex(menu => menu.id === id)
        if (parentIndex !== -1) {
          this.parentMenus.splice(parentIndex, 1, {
            id: response.data.data.id,
            name: response.data.data.name,
            label: response.data.data.label,
          })
        }

        return response.data.data
      } catch (error) {
        if (error.response?.status === 422) {
          this.validationErrors = error.response.data.errors
        } else {
          this.error = error.response?.data?.message || 'Failed to update menu'
        }
        console.error('Error updating menu:', error)
        throw error
      } finally {
        this.saving = false
      }
    },

    /**
     * Delete menu
     */
    async deleteMenu(id) {
      this.deleting = true
      this.error = null

      try {
        await axios.delete(`/api/admin/menus/${id}`)

        // Remove from menus array
        const index = this.menus.findIndex(menu => menu.id === id)
        if (index !== -1) {
          this.menus.splice(index, 1)
        }

        // Remove from parent menus
        const parentIndex = this.parentMenus.findIndex(menu => menu.id === id)
        if (parentIndex !== -1) {
          this.parentMenus.splice(parentIndex, 1)
        }

        // Clear current menu if it's the deleted one
        if (this.currentMenu?.id === id) {
          this.currentMenu = null
        }

        return true
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete menu'
        console.error('Error deleting menu:', error)
        throw error
      } finally {
        this.deleting = false
      }
    },

    /**
     * Update menu order
     */
    async updateMenuOrder(menuId, newOrder, newParentId = null) {
      this.error = null

      try {
        const response = await axios.patch(`/api/admin/menus/${menuId}/order`, {
          order: newOrder,
          parent_id: newParentId,
        })

        // Update in menus array
        const index = this.menus.findIndex(menu => menu.id === menuId)
        if (index !== -1) {
          this.menus[index].order = newOrder
          if (newParentId !== null) {
            this.menus[index].parent_id = newParentId
          }
        }

        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update menu order'
        console.error('Error updating menu order:', error)
        throw error
      }
    },

    /**
     * Fetch available roles
     */
    async fetchRoles() {
      try {
        const response = await axios.get('/api/admin/roles')

        this.roles = response.data.data
        
        return response.data.data
      } catch (error) {
        console.error('Error fetching roles:', error)
        throw error
      }
    },

    /**
     * Fetch parent menu options
     */
    async fetchParentMenus() {
      try {
        const response = await axios.get('/api/admin/menus/parents')

        this.parentMenus = response.data.data
        
        return response.data.data
      } catch (error) {
        console.error('Error fetching parent menus:', error)
        throw error
      }
    },

    /**
     * Update filters and refresh menus
     */
    async updateFilters(newFilters) {
      this.filters = { ...this.filters, ...newFilters }
      await this.fetchMenus()
    },

    /**
     * Clear all filters
     */
    async clearFilters() {
      this.filters = {
        search: '',
        role: '',
        status: '',
        parent_id: null,
        sort_by: 'order',
        sort_direction: 'asc',
      }
      await this.fetchMenus()
    },

    /**
     * Refresh menus
     */
    async refresh() {
      await this.fetchMenus()
    },

    /**
     * Clear all state
     */
    clearState() {
      this.menus = []
      this.currentMenu = null
      this.menuTree = []
      this.error = null
      this.validationErrors = {}
      this.roles = []
      this.parentMenus = []
    },

    /**
     * Set current menu
     */
    setCurrentMenu(menu) {
      this.currentMenu = menu
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
