import Login from '@/pages/login.vue'
import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick } from 'vue'

// Mock useAuth
const mockLogin = vi.fn()
const mockIsLoggedIn = { value: false }

vi.mock('@/composables/useAuth', () => ({
  useAuth: () => ({
    login: mockLogin,
    isLoggedIn: mockIsLoggedIn,
  }),
}))

// Mock vue-router
const mockPush = vi.fn()

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
  RouterLink: {
    template: '<a><slot /></a>',
  },
}))

// Mock Vuetify components
const createVuetifyMock = name => ({
  name,
  template: '<div><slot /></div>',
  props: Object.keys({}), // Accept any props
})

const vuetifyComponents = {
  VCard: createVuetifyMock('VCard'),
  VCardText: createVuetifyMock('VCardText'),
  VForm: createVuetifyMock('VForm'),
  VTextField: createVuetifyMock('VTextField'),
  VCheckbox: createVuetifyMock('VCheckbox'),
  VBtn: createVuetifyMock('VBtn'),
  VAlert: createVuetifyMock('VAlert'),
  VRow: createVuetifyMock('VRow'),
  VCol: createVuetifyMock('VCol'),
  VContainer: createVuetifyMock('VContainer'),
}

describe('Login Component', () => {
  let wrapper

  beforeEach(() => {
    vi.clearAllMocks()
    mockLogin.mockResolvedValue({ success: true })
  })

  const mountComponent = (props = {}) => {
    return mount(Login, {
      props,
      global: {
        components: vuetifyComponents,
        mocks: {
          $t: key => key,
        },
      },
    })
  }

  it('should render login form', () => {
    wrapper = mountComponent()
    
    expect(wrapper.find('form').exists()).toBe(true)
    expect(wrapper.find('[data-test="email-field"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="password-field"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="login-button"]').exists()).toBe(true)
  })

  it('should call login function with form data on submit', async () => {
    wrapper = mountComponent()
    
    // Set form values
    await wrapper.find('[data-test="email-field"]').setValue('test@example.com')
    await wrapper.find('[data-test="password-field"]').setValue('password123')
    
    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await nextTick()

    expect(mockLogin).toHaveBeenCalledWith({
      email: 'test@example.com',
      password: 'password123',
      remember: false,
    })
  })

  it('should show error message on login failure', async () => {
    mockLogin.mockResolvedValueOnce({
      success: false,
      error: 'Invalid credentials',
    })

    wrapper = mountComponent()
    
    await wrapper.find('[data-test="email-field"]').setValue('wrong@example.com')
    await wrapper.find('[data-test="password-field"]').setValue('wrongpassword')
    await wrapper.find('form').trigger('submit.prevent')
    await nextTick()

    expect(wrapper.text()).toContain('Invalid credentials')
  })

  it('should handle remember me checkbox', async () => {
    wrapper = mountComponent()
    
    await wrapper.find('[data-test="email-field"]').setValue('test@example.com')
    await wrapper.find('[data-test="password-field"]').setValue('password123')
    await wrapper.find('[data-test="remember-checkbox"]').setChecked(true)
    
    await wrapper.find('form').trigger('submit.prevent')
    await nextTick()

    expect(mockLogin).toHaveBeenCalledWith({
      email: 'test@example.com',
      password: 'password123',
      remember: true,
    })
  })

  it('should disable submit button while loading', async () => {
    let resolveLogin
    mockLogin.mockReturnValue(new Promise(resolve => {
      resolveLogin = resolve
    }))

    wrapper = mountComponent()
    
    await wrapper.find('[data-test="email-field"]').setValue('test@example.com')
    await wrapper.find('[data-test="password-field"]').setValue('password123')
    
    // Start login
    wrapper.find('form').trigger('submit.prevent')
    await nextTick()

    // Button should be disabled
    expect(wrapper.find('[data-test="login-button"]').attributes('disabled')).toBeDefined()

    // Resolve login
    resolveLogin({ success: true })
    await nextTick()

    // Button should be enabled again
    expect(wrapper.find('[data-test="login-button"]').attributes('disabled')).toBeUndefined()
  })

  it('should validate required fields', async () => {
    wrapper = mountComponent()
    
    // Try to submit without filling fields
    await wrapper.find('form').trigger('submit.prevent')
    await nextTick()

    expect(mockLogin).not.toHaveBeenCalled()
  })

  it('should validate email format', async () => {
    wrapper = mountComponent()
    
    // Set invalid email
    await wrapper.find('[data-test="email-field"]').setValue('invalid-email')
    await wrapper.find('[data-test="password-field"]').setValue('password123')
    await wrapper.find('form').trigger('submit.prevent')
    await nextTick()

    expect(mockLogin).not.toHaveBeenCalled()
  })

  it('should redirect if already logged in', () => {
    mockIsLoggedIn.value = true
    
    wrapper = mountComponent()

    expect(mockPush).toHaveBeenCalledWith('/')
  })

  it('should have links to forgot password and register', () => {
    wrapper = mountComponent()
    
    expect(wrapper.text()).toContain('Forgot Password?')
    expect(wrapper.find('[data-test="forgot-password-link"]').exists()).toBe(true)
  })
})
