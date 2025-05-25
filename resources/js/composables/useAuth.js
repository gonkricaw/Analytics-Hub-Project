import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

// Global state for authentication
const isAuthenticated = ref(false)
const currentUser = ref(null)

export function useAuth() {
  const router = useRouter()

  // Computed properties
  const isLoggedIn = computed(() => isAuthenticated.value)
  const user = computed(() => currentUser.value)

  // Methods
  const login = async (credentials) => {
    try {
      // TODO: Implement actual API call to Laravel backend
      // For now, simulate login for Phase 0
      if (credentials.email && credentials.password) {
        isAuthenticated.value = true
        currentUser.value = {
          id: 1,
          name: 'Admin User',
          email: credentials.email,
          role: 'admin',
        }
        
        // Redirect to dashboard
        await router.push('/')
        return { success: true }
      } else {
        throw new Error('Invalid credentials')
      }
    } catch (error) {
      return { 
        success: false, 
        error: error.message || 'Login failed' 
      }
    }
  }

  const logout = async () => {
    try {
      // TODO: Implement actual API call to Laravel backend
      isAuthenticated.value = false
      currentUser.value = null
      
      // Redirect to login
      await router.push('/login')
      return { success: true }
    } catch (error) {
      return { 
        success: false, 
        error: error.message || 'Logout failed' 
      }
    }
  }

  const checkAuth = () => {
    // TODO: Implement actual token validation with Laravel backend
    // For Phase 0, check localStorage for demo purposes
    const token = localStorage.getItem('auth_token')
    if (token) {
      isAuthenticated.value = true
      currentUser.value = JSON.parse(localStorage.getItem('user_data') || '{}')
    }
  }

  // Initialize auth state
  checkAuth()

  return {
    isLoggedIn,
    user,
    login,
    logout,
    checkAuth,
  }
}

// Router guards
export function setupAuthGuards(router) {
  router.beforeEach((to, from, next) => {
    const { isLoggedIn } = useAuth()
    
    // Check if route requires authentication
    const requiresAuth = to.meta.requiresAuth || !to.meta.public
    
    if (requiresAuth && !isLoggedIn.value) {
      // Redirect to login if not authenticated
      next('/login')
    } else if (to.path === '/login' && isLoggedIn.value) {
      // Redirect to home if already authenticated and trying to access login
      next('/')
    } else {
      next()
    }
  })
}
