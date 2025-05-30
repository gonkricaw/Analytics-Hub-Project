import { useErrorHandler } from '@/composables/useErrorHandler'
import axios from 'axios'
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

// Global state for authentication
const isAuthenticated = ref(false)
const currentUser = ref(null)
const needsPasswordChange = ref(false)
const needsTermsAcceptance = ref(false)
const currentTermsVersion = ref(null)

// Setup axios defaults with proper base URL for XAMPP
const isXAMPP = window.location.pathname.includes('/Analytics-Hub-Project/')
const baseURL = isXAMPP ? '/Analytics-Hub-Project/public/api' : '/api'
const csrfBaseURL = isXAMPP ? '/Analytics-Hub-Project/public' : '/'

axios.defaults.withCredentials = true
axios.defaults.baseURL = baseURL

// Create separate axios instance for non-API routes (like CSRF)
const baseAxios = axios.create({
  withCredentials: true,
  baseURL: csrfBaseURL
})

// Add response interceptor to handle authentication errors
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      isAuthenticated.value = false
      currentUser.value = null
      needsPasswordChange.value = false
      needsTermsAcceptance.value = false
      localStorage.removeItem('auth_token')
    }
    
    return Promise.reject(error)
  },
)

export function useAuth() {
  const router = useRouter()
  const { handleAxiosError } = useErrorHandler()

  // Computed properties
  const isLoggedIn = computed(() => isAuthenticated.value)
  const user = computed(() => currentUser.value)

  // Initialize auth from token
  const initAuth = async () => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
      try {
        const response = await axios.get('/user')
        if (response.data.success) {
          isAuthenticated.value = true
          currentUser.value = response.data.data.user
          needsPasswordChange.value = response.data.data.needs_password_change
          needsTermsAcceptance.value = response.data.data.needs_terms_acceptance
          currentTermsVersion.value = response.data.data.current_terms_version
          
          return true
        }
      } catch (error) {
        localStorage.removeItem('auth_token')
        delete axios.defaults.headers.common['Authorization']
      }
    }
    
    return false
  }

  // Methods
  const login = async credentials => {
    try {
      // Get CSRF cookie first (use baseAxios for non-API routes)
      await baseAxios.get('/sanctum/csrf-cookie')
      
      const response = await axios.post('/login', {
        email: credentials.email,
        password: credentials.password,
        remember: credentials.remember || false,
      })

      if (response.data.success) {
        const token = response.data.data.token

        localStorage.setItem('auth_token', token)
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
        
        isAuthenticated.value = true
        currentUser.value = response.data.data.user
        needsPasswordChange.value = response.data.data.needs_password_change
        needsTermsAcceptance.value = response.data.data.needs_terms_acceptance
        currentTermsVersion.value = response.data.data.current_terms_version

        // Redirect based on user needs
        if (needsPasswordChange.value) {
          await router.push('/change-password')
        } else {
          await router.push('/')
        }
        
        return { success: true }
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Login failed'
      
      return { 
        success: false, 
        error: message,
      }
    }
  }

  const logout = async () => {
    try {
      await axios.post('/logout')
    } catch (error) {
      // Continue with logout even if API call fails
    }
    
    isAuthenticated.value = false
    currentUser.value = null
    needsPasswordChange.value = false
    needsTermsAcceptance.value = false
    localStorage.removeItem('auth_token')
    delete axios.defaults.headers.common['Authorization']
    
    await router.push('/login')
    
    return { success: true }
  }

  const changePassword = async passwordData => {
    try {
      const response = await axios.post('/change-password', passwordData)
      if (response.data.success) {
        needsPasswordChange.value = false
        
        return { success: true, message: response.data.message }
      }
    } catch (error) {
      return { 
        success: false, 
        error: error.response?.data?.message || 'Password change failed',
      }
    }
  }

  const forgotPassword = async email => {
    try {
      const response = await axios.post('/forgot-password', { email })
      
      return { 
        success: true, 
        message: response.data.message, 
      }
    } catch (error) {
      return { 
        success: false, 
        error: error.response?.data?.message || 'Request failed',
      }
    }
  }

  const resetPassword = async resetData => {
    try {
      const response = await axios.post('/reset-password', resetData)
      
      return { 
        success: true, 
        message: response.data.message, 
      }
    } catch (error) {
      return { 
        success: false, 
        error: error.response?.data?.message || 'Password reset failed',
      }
    }
  }

  const updateProfile = async profileData => {
    try {
      const response = await axios.post('/update-profile', profileData)
      if (response.data.success) {
        currentUser.value = { ...currentUser.value, ...response.data.data.user }
        
        return { success: true, message: response.data.message }
      }
    } catch (error) {
      return { 
        success: false, 
        error: error.response?.data?.message || 'Profile update failed',
      }
    }
  }

  const acceptTerms = async version => {
    try {
      const response = await axios.post('/accept-terms', { terms_version: version })
      if (response.data.success) {
        needsTermsAcceptance.value = false
        
        return { success: true, message: response.data.message }
      }
    } catch (error) {
      return { 
        success: false, 
        error: error.response?.data?.message || 'Terms acceptance failed',
      }
    }
  }

  // Initialize auth state
  initAuth()

  return {
    isLoggedIn,
    user,
    needsPasswordChange: computed(() => needsPasswordChange.value),
    needsTermsAcceptance: computed(() => needsTermsAcceptance.value),
    currentTermsVersion: computed(() => currentTermsVersion.value),
    login,
    logout,
    changePassword,
    forgotPassword,
    resetPassword,
    updateProfile,
    acceptTerms,
    initAuth,
  }
}

// Router guards
export function setupAuthGuards(router) {
  router.beforeEach(async (to, from, next) => {
    const { isLoggedIn, user, needsPasswordChange, needsTermsAcceptance } = useAuth()
    
    // Public routes that don't require authentication
    const publicRoutes = ['/login', '/forgot-password', '/reset-password']
    const isPublicRoute = to.meta.public || publicRoutes.includes(to.path)
    
    // Check if route requires authentication
    const requiresAuth = to.meta.requiresAuth || !isPublicRoute
    
    if (requiresAuth && !isLoggedIn.value) {
      // Redirect to login if not authenticated
      return next('/login')
    }
    
    if (isLoggedIn.value) {
      // User is authenticated, check additional requirements
      
      // If trying to access login page while authenticated, redirect to home
      if (to.path === '/login') {
        return next('/')
      }
      
      // Check if user needs to change password (except when already on change-password page)
      if (needsPasswordChange.value && to.path !== '/change-password') {
        return next('/change-password')
      }
      
      // Check role-based access
      const requiredRole = to.meta.requiresRole
      if (requiredRole && user.value?.role !== requiredRole) {
        // User doesn't have required role, redirect to home with error
        router.push('/').then(() => {
          // Show error notification
          if (typeof window !== 'undefined' && window.flashNotifications) {
            window.flashNotifications.showError('You do not have permission to access this page.')
          }
        })
        
        return
      }
      
      // Allow admin access to all routes
      if (user.value?.role === 'admin') {
        return next()
      }
      
      // Check if route requires admin access for non-admin users
      if (to.path.startsWith('/admin') && user.value?.role !== 'admin') {
        router.push('/').then(() => {
          if (typeof window !== 'undefined' && window.flashNotifications) {
            window.flashNotifications.showError('Admin access required.')
          }
        })
        
        return
      }
    }
    
    next()
  })
}
