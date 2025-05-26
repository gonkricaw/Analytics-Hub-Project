import TermsModal from '@/components/TermsModal.vue'
import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick } from 'vue'

// Mock useAuth
const mockAcceptTerms = vi.fn()
const mockLogout = vi.fn()
const mockNeedsTermsAcceptance = { value: true }
const mockCurrentTermsVersion = { value: '1.0' }

vi.mock('@/composables/useAuth', () => ({
  useAuth: () => ({
    acceptTerms: mockAcceptTerms,
    logout: mockLogout,
    needsTermsAcceptance: mockNeedsTermsAcceptance,
    currentTermsVersion: mockCurrentTermsVersion,
  }),
}))

// Mock Vuetify components
const createVuetifyMock = name => ({
  name,
  template: '<div><slot /></div>',
  props: Object.keys({}),
})

const vuetifyComponents = {
  VDialog: {
    name: 'VDialog',
    template: '<div v-if="modelValue"><slot /></div>',
    props: ['modelValue'],
  },
  VCard: createVuetifyMock('VCard'),
  VCardTitle: createVuetifyMock('VCardTitle'),
  VCardText: createVuetifyMock('VCardText'),
  VCardActions: createVuetifyMock('VCardActions'),
  VBtn: createVuetifyMock('VBtn'),
  VCheckbox: createVuetifyMock('VCheckbox'),
  VSpacer: createVuetifyMock('VSpacer'),
}

describe('TermsModal Component', () => {
  let wrapper

  beforeEach(() => {
    vi.clearAllMocks()
    mockAcceptTerms.mockResolvedValue({ success: true })
    mockNeedsTermsAcceptance.value = true
    mockCurrentTermsVersion.value = '1.0'
  })

  const mountComponent = (props = {}) => {
    return mount(TermsModal, {
      props,
      global: {
        components: vuetifyComponents,
        mocks: {
          $t: key => key,
        },
      },
    })
  }

  it('should render when terms acceptance is needed', () => {
    wrapper = mountComponent()
    
    expect(wrapper.find('[data-test="terms-modal"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Terms and Conditions')
  })

  it('should not render when terms acceptance is not needed', () => {
    mockNeedsTermsAcceptance.value = false
    
    wrapper = mountComponent()
    
    expect(wrapper.find('[data-test="terms-modal"]').exists()).toBe(false)
  })

  it('should display terms content', () => {
    wrapper = mountComponent()
    
    expect(wrapper.text()).toContain('Please read and accept our Terms and Conditions')
    expect(wrapper.find('[data-test="terms-content"]').exists()).toBe(true)
  })

  it('should enable accept button only when checkbox is checked', async () => {
    wrapper = mountComponent()
    
    const acceptButton = wrapper.find('[data-test="accept-button"]')
    const checkbox = wrapper.find('[data-test="terms-checkbox"]')
    
    // Initially disabled
    expect(acceptButton.attributes('disabled')).toBeDefined()
    
    // Check the checkbox
    await checkbox.setChecked(true)
    await nextTick()
    
    // Should be enabled
    expect(acceptButton.attributes('disabled')).toBeUndefined()
  })

  it('should call acceptTerms when accept button is clicked', async () => {
    wrapper = mountComponent()
    
    // Check the checkbox first
    await wrapper.find('[data-test="terms-checkbox"]').setChecked(true)
    await nextTick()
    
    // Click accept button
    await wrapper.find('[data-test="accept-button"]').trigger('click')
    await nextTick()
    
    expect(mockAcceptTerms).toHaveBeenCalledWith('1.0')
  })

  it('should show error message on accept failure', async () => {
    mockAcceptTerms.mockResolvedValueOnce({
      success: false,
      error: 'Failed to accept terms',
    })
    
    wrapper = mountComponent()
    
    await wrapper.find('[data-test="terms-checkbox"]').setChecked(true)
    await wrapper.find('[data-test="accept-button"]').trigger('click')
    await nextTick()
    
    expect(wrapper.text()).toContain('Failed to accept terms')
  })

  it('should disable buttons while processing', async () => {
    let resolveAcceptTerms
    mockAcceptTerms.mockReturnValue(new Promise(resolve => {
      resolveAcceptTerms = resolve
    }))
    
    wrapper = mountComponent()
    
    await wrapper.find('[data-test="terms-checkbox"]').setChecked(true)
    
    // Start accept process
    wrapper.find('[data-test="accept-button"]').trigger('click')
    await nextTick()
    
    // Buttons should be disabled
    expect(wrapper.find('[data-test="accept-button"]').attributes('disabled')).toBeDefined()
    expect(wrapper.find('[data-test="decline-button"]').attributes('disabled')).toBeDefined()
    
    // Resolve accept
    resolveAcceptTerms({ success: true })
    await nextTick()
    
    // Modal should close (not visible)
    expect(wrapper.find('[data-test="terms-modal"]').exists()).toBe(false)
  })

  it('should call logout when decline button is clicked', async () => {
    wrapper = mountComponent()
    
    await wrapper.find('[data-test="decline-button"]').trigger('click')
    await nextTick()
    
    expect(mockLogout).toHaveBeenCalled()
  })

  it('should not be closable by clicking outside', () => {
    wrapper = mountComponent()
    
    const dialog = wrapper.findComponent({ name: 'VDialog' })

    expect(dialog.props('persistent')).toBe(true)
  })

  it('should show loading state', async () => {
    let resolveAcceptTerms
    mockAcceptTerms.mockReturnValue(new Promise(resolve => {
      resolveAcceptTerms = resolve
    }))
    
    wrapper = mountComponent()
    
    await wrapper.find('[data-test="terms-checkbox"]').setChecked(true)
    
    // Start accept process
    wrapper.find('[data-test="accept-button"]').trigger('click')
    await nextTick()
    
    // Should show loading state
    expect(wrapper.find('[data-test="accept-button"]').text()).toContain('Accepting...')
  })

  it('should handle different terms versions', async () => {
    mockCurrentTermsVersion.value = '2.0'
    
    wrapper = mountComponent()
    
    await wrapper.find('[data-test="terms-checkbox"]').setChecked(true)
    await wrapper.find('[data-test="accept-button"]').trigger('click')
    await nextTick()
    
    expect(mockAcceptTerms).toHaveBeenCalledWith('2.0')
  })
})
