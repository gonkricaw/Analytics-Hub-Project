import { useAuth } from '@/composables/useAuth'
import axios from 'axios'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

// Mock vue-router
const mockPush = vi.fn()
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
}))

// Mock axios
vi.mock('axios')
const mockedAxios = vi.mocked(axios)

describe('useAuth', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    localStorage.clear()
    
    // Reset axios defaults
    mockedAxios.defaults = {
      withCredentials: true,
      baseURL: '/api',
      headers: { common: {} },
    }
    
    mockedAxios.interceptors = {
      response: {
        use: vi.fn(),
      },
    }
  })

  afterEach(() => {
    localStorage.clear()
  })

  describe('login', () => {
    it('should login successfully with valid credentials', async () => {
      const { login } = useAuth()
      
      // Mock CSRF call
      mockedAxios.get.mockResolvedValueOnce({ data: {} })
      
      // Mock login response
      const loginResponse = {
        data: {
          success: true,
          data: {
            token: 'test-token',
            user: { id: 1, email: 'test@example.com', name: 'Test User', role: 'user' },
            needs_password_change: false,
            needs_terms_acceptance: false,
            current_terms_version: null,
          },
        },
      }
      mockedAxios.post.mockResolvedValueOnce(loginResponse)

      const credentials = {
        email: 'test@example.com',
        password: 'password123',
        remember: false,
      }

      const result = await login(credentials)

      expect(mockedAxios.get).toHaveBeenCalledWith('/sanctum/csrf-cookie')
      expect(mockedAxios.post).toHaveBeenCalledWith('/login', credentials)
      expect(localStorage.getItem('auth_token')).toBe('test-token')
      expect(result).toEqual({ success: true })
      expect(mockPush).toHaveBeenCalledWith('/')
    })

    it('should redirect to change password when needs_password_change is true', async () => {
      const { login } = useAuth()
      
      mockedAxios.get.mockResolvedValueOnce({ data: {} })
      
      const loginResponse = {
        data: {
          success: true,
          data: {
            token: 'test-token',
            user: { id: 1, email: 'test@example.com', name: 'Test User', role: 'user' },
            needs_password_change: true,
            needs_terms_acceptance: false,
            current_terms_version: null,
          },
        },
      }
      mockedAxios.post.mockResolvedValueOnce(loginResponse)

      const result = await login({
        email: 'test@example.com',
        password: 'password123',
      })

      expect(result).toEqual({ success: true })
      expect(mockPush).toHaveBeenCalledWith('/change-password')
    })

    it('should handle login failure', async () => {
      const { login } = useAuth()
      
      mockedAxios.get.mockResolvedValueOnce({ data: {} })
      
      const error = {
        response: {
          data: {
            message: 'Invalid credentials',
          },
        },
      }
      mockedAxios.post.mockRejectedValueOnce(error)

      const result = await login({
        email: 'wrong@example.com',
        password: 'wrongpassword',
      })

      expect(result).toEqual({
        success: false,
        error: 'Invalid credentials',
      })
      expect(localStorage.getItem('auth_token')).toBeNull()
    })
  })

  describe('logout', () => {
    it('should logout successfully', async () => {
      const { logout } = useAuth()
      
      // Set up authenticated state
      localStorage.setItem('auth_token', 'test-token')
      mockedAxios.defaults.headers.common['Authorization'] = 'Bearer test-token'
      
      mockedAxios.post.mockResolvedValueOnce({ data: { success: true } })

      const result = await logout()

      expect(mockedAxios.post).toHaveBeenCalledWith('/logout')
      expect(localStorage.getItem('auth_token')).toBeNull()
      expect(mockedAxios.defaults.headers.common['Authorization']).toBeUndefined()
      expect(result).toEqual({ success: true })
      expect(mockPush).toHaveBeenCalledWith('/login')
    })

    it('should logout even if API call fails', async () => {
      const { logout } = useAuth()
      
      localStorage.setItem('auth_token', 'test-token')
      mockedAxios.post.mockRejectedValueOnce(new Error('Network error'))

      const result = await logout()

      expect(localStorage.getItem('auth_token')).toBeNull()
      expect(result).toEqual({ success: true })
      expect(mockPush).toHaveBeenCalledWith('/login')
    })
  })

  describe('changePassword', () => {
    it('should change password successfully', async () => {
      const { changePassword } = useAuth()
      
      const response = {
        data: {
          success: true,
          message: 'Password changed successfully',
        },
      }
      mockedAxios.post.mockResolvedValueOnce(response)

      const passwordData = {
        current_password: 'oldpassword',
        password: 'newpassword',
        password_confirmation: 'newpassword',
      }

      const result = await changePassword(passwordData)

      expect(mockedAxios.post).toHaveBeenCalledWith('/change-password', passwordData)
      expect(result).toEqual({
        success: true,
        message: 'Password changed successfully',
      })
    })

    it('should handle password change failure', async () => {
      const { changePassword } = useAuth()
      
      const error = {
        response: {
          data: {
            message: 'Current password is incorrect',
          },
        },
      }
      mockedAxios.post.mockRejectedValueOnce(error)

      const result = await changePassword({
        current_password: 'wrongpassword',
        password: 'newpassword',
        password_confirmation: 'newpassword',
      })

      expect(result).toEqual({
        success: false,
        error: 'Current password is incorrect',
      })
    })
  })

  describe('forgotPassword', () => {
    it('should send forgot password email successfully', async () => {
      const { forgotPassword } = useAuth()
      
      const response = {
        data: {
          message: 'Password reset link sent to your email',
        },
      }
      mockedAxios.post.mockResolvedValueOnce(response)

      const result = await forgotPassword('test@example.com')

      expect(mockedAxios.post).toHaveBeenCalledWith('/forgot-password', { email: 'test@example.com' })
      expect(result).toEqual({
        success: true,
        message: 'Password reset link sent to your email',
      })
    })
  })

  describe('resetPassword', () => {
    it('should reset password successfully', async () => {
      const { resetPassword } = useAuth()
      
      const response = {
        data: {
          message: 'Password reset successfully',
        },
      }
      mockedAxios.post.mockResolvedValueOnce(response)

      const resetData = {
        token: 'reset-token',
        email: 'test@example.com',
        password: 'newpassword',
        password_confirmation: 'newpassword',
      }

      const result = await resetPassword(resetData)

      expect(mockedAxios.post).toHaveBeenCalledWith('/reset-password', resetData)
      expect(result).toEqual({
        success: true,
        message: 'Password reset successfully',
      })
    })
  })

  describe('updateProfile', () => {
    it('should update profile successfully', async () => {
      const { updateProfile } = useAuth()
      
      const response = {
        data: {
          success: true,
          message: 'Profile updated successfully',
          data: {
            user: { id: 1, email: 'test@example.com', name: 'Updated Name', role: 'user' },
          },
        },
      }
      mockedAxios.post.mockResolvedValueOnce(response)

      const profileData = {
        name: 'Updated Name',
        email: 'test@example.com',
      }

      const result = await updateProfile(profileData)

      expect(mockedAxios.post).toHaveBeenCalledWith('/update-profile', profileData)
      expect(result).toEqual({
        success: true,
        message: 'Profile updated successfully',
      })
    })
  })

  describe('acceptTerms', () => {
    it('should accept terms successfully', async () => {
      const { acceptTerms } = useAuth()
      
      const response = {
        data: {
          success: true,
          message: 'Terms accepted successfully',
        },
      }
      mockedAxios.post.mockResolvedValueOnce(response)

      const result = await acceptTerms('1.0')

      expect(mockedAxios.post).toHaveBeenCalledWith('/accept-terms', { terms_version: '1.0' })
      expect(result).toEqual({
        success: true,
        message: 'Terms accepted successfully',
      })
    })
  })

  describe('initAuth', () => {
    it('should initialize auth from stored token', async () => {
      const { initAuth } = useAuth()
      
      localStorage.setItem('auth_token', 'stored-token')
      
      const response = {
        data: {
          success: true,
          data: {
            user: { id: 1, email: 'test@example.com', name: 'Test User', role: 'user' },
            needs_password_change: false,
            needs_terms_acceptance: false,
            current_terms_version: null,
          },
        },
      }
      mockedAxios.get.mockResolvedValueOnce(response)

      const result = await initAuth()

      expect(mockedAxios.get).toHaveBeenCalledWith('/user')
      expect(mockedAxios.defaults.headers.common['Authorization']).toBe('Bearer stored-token')
      expect(result).toBe(true)
    })

    it('should return false when no token is stored', async () => {
      const { initAuth } = useAuth()
      
      const result = await initAuth()

      expect(result).toBe(false)
      expect(mockedAxios.get).not.toHaveBeenCalled()
    })

    it('should handle invalid token', async () => {
      const { initAuth } = useAuth()
      
      localStorage.setItem('auth_token', 'invalid-token')
      mockedAxios.get.mockRejectedValueOnce(new Error('Unauthorized'))

      const result = await initAuth()

      expect(localStorage.getItem('auth_token')).toBeNull()
      expect(mockedAxios.defaults.headers.common['Authorization']).toBeUndefined()
      expect(result).toBe(false)
    })
  })
})
