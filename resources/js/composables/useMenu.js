import { computed, ref } from 'vue'
import { useApi } from './useApi'
import { useAuth } from './useAuth'

const menus = ref([])
const loading = ref(false)
const error = ref(null)

export function useMenu() {
  const { apiCall } = useApi()
  const { user } = useAuth()

  const hierarchicalMenus = computed(() => {
    return buildHierarchy(menus.value)
  })

  const buildHierarchy = menuItems => {
    const menuMap = new Map()
    const rootMenus = []

    // Create a map of all menus
    menuItems.forEach(menu => {
      menuMap.set(menu.id, { ...menu, children: [] })
    })

    // Build the hierarchy
    menuItems.forEach(menu => {
      const menuItem = menuMap.get(menu.id)
      
      if (menu.parent_id) {
        const parent = menuMap.get(menu.parent_id)
        if (parent) {
          parent.children.push(menuItem)
        }
      } else {
        rootMenus.push(menuItem)
      }
    })

    // Sort menus by order
    const sortByOrder = items => {
      items.sort((a, b) => (a.order || 0) - (b.order || 0))
      items.forEach(item => {
        if (item.children.length > 0) {
          sortByOrder(item.children)
        }
      })
    }

    sortByOrder(rootMenus)
    
    return rootMenus
  }

  const fetchMenus = async () => {
    if (!user.value) return

    loading.value = true
    error.value = null

    try {
      const response = await apiCall('/menus/hierarchy', 'GET')
      if (response.success) {
        menus.value = response.data
      } else {
        throw new Error(response.message || 'Failed to fetch menus')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error fetching menus:', err)
    } finally {
      loading.value = false
    }
  }

  const getMenuById = id => {
    const findMenu = items => {
      for (const item of items) {
        if (item.id === id) return item
        if (item.children.length > 0) {
          const found = findMenu(item.children)
          if (found) return found
        }
      }
      
      return null
    }
    
    return findMenu(hierarchicalMenus.value)
  }

  const clearMenus = () => {
    menus.value = []
    error.value = null
  }

  const refreshMenus = () => {
    return fetchMenus()
  }

  return {
    menus: computed(() => menus.value),
    hierarchicalMenus,
    loading: computed(() => loading.value),
    error: computed(() => error.value),
    fetchMenus,
    getMenuById,
    clearMenus,
    refreshMenus,
  }
}

// Global state management
const globalMenuState = {
  menus,
  loading,
  error,
}

export function useMenuStore() {
  const { user } = useAuth()
  const { apiCall } = useApi()

  const hierarchicalMenus = computed(() => {
    return buildHierarchy(globalMenuState.menus.value)
  })

  const buildHierarchy = menuItems => {
    const menuMap = new Map()
    const rootMenus = []

    menuItems.forEach(menu => {
      menuMap.set(menu.id, { ...menu, children: [] })
    })

    menuItems.forEach(menu => {
      const menuItem = menuMap.get(menu.id)
      
      if (menu.parent_id) {
        const parent = menuMap.get(menu.parent_id)
        if (parent) {
          parent.children.push(menuItem)
        }
      } else {
        rootMenus.push(menuItem)
      }
    })

    const sortByOrder = items => {
      items.sort((a, b) => (a.order || 0) - (b.order || 0))
      items.forEach(item => {
        if (item.children.length > 0) {
          sortByOrder(item.children)
        }
      })
    }

    sortByOrder(rootMenus)
    
    return rootMenus
  }

  const fetchMenus = async () => {
    if (!user.value) return

    globalMenuState.loading.value = true
    globalMenuState.error.value = null

    try {
      const response = await apiCall('/menus/hierarchy', 'GET')
      if (response.success) {
        globalMenuState.menus.value = response.data
      } else {
        throw new Error(response.message || 'Failed to fetch menus')
      }
    } catch (err) {
      globalMenuState.error.value = err.message
      console.error('Error fetching menus:', err)
    } finally {
      globalMenuState.loading.value = false
    }
  }

  const clearMenus = () => {
    globalMenuState.menus.value = []
    globalMenuState.error.value = null
  }

  return {
    menus: computed(() => globalMenuState.menus.value),
    hierarchicalMenus,
    loading: computed(() => globalMenuState.loading.value),
    error: computed(() => globalMenuState.error.value),
    fetchMenus,
    clearMenus,
  }
}
