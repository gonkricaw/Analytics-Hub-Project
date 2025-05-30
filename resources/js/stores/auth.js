/**
 * Auth Store
 * Re-exports auth functionality as a store for consistent import paths
 */
import { useAuth } from '@/composables/useAuth'

/**
 * Auth store wrapper that provides Pinia-style interface
 * while using the existing useAuth composable
 */
export function useAuthStore() {
  const auth = useAuth()
  
  return {
    // State getters
    get isLoggedIn() {
      return auth.isLoggedIn.value
    },
    
    get user() {
      return auth.user.value
    },
    
    get isAuthenticated() {
      return auth.isLoggedIn.value
    },
    
    get currentUser() {
      return auth.user.value
    },
    
    get needsPasswordChange() {
      return auth.needsPasswordChange?.value || false
    },
    
    get needsTermsAcceptance() {
      return auth.needsTermsAcceptance?.value || false
    },
    
    // Actions
    login: auth.login,
    logout: auth.logout,
    initAuth: auth.initAuth,
    changePassword: auth.changePassword,
    forgotPassword: auth.forgotPassword,
    resetPassword: auth.resetPassword,
    updateProfile: auth.updateProfile,
    acceptTerms: auth.acceptTerms,
    
    // Computed properties for reactive access
    isLoggedInRef: auth.isLoggedIn,
    userRef: auth.user,
  }
}
