import SystemConfiguration from '@/components/admin/SystemConfiguration.vue'
import { useSystemConfiguration } from '@/composables/useSystemConfiguration'
import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createVuetify } from 'vuetify'

// Mock the composable
vi.mock('@/composables/useSystemConfiguration')

const createWrapper = (props = {}) => {
  const vuetify = createVuetify()
  
  return mount(SystemConfiguration, {
    props,
    global: {
      plugins: [vuetify],
      stubs: {
        VIcon: true,
        VBtn: true,
        VCard: true,
        VCardTitle: true,
        VCardText: true,
        VCardSubtitle: true,
        VRow: true,
        VCol: true,
        VTabs: true,
        VTab: true,
        VWindow: true,
        VWindowItem: true,
        VTextField: true,
        VTextarea: true,
        VSwitch: true,
        VFileInput: true,
        VImg: true,
        VChip: true,
        VAlert: true,
        VProgressCircular: true,
        VSnackbar: true,
        VSpacer: true,
      },
    },
  })
}

describe('SystemConfiguration', () => {
  let mockSystemConfig

  beforeEach(() => {
    // Reset mock
    mockSystemConfig = {
      loading: { value: false },
      error: { value: null },
      groupedConfigurations: {
        value: {
          dashboard: [
            {
              id: 1,
              key: 'dashboard_jumbotron',
              value: [{ title: 'Welcome', content: 'Test content' }],
              type: 'json',
              description: 'Dashboard jumbotron configuration',
              is_public: true,
            },
            {
              id: 2,
              key: 'dashboard_marquee',
              value: 'Welcome to Analytics Hub',
              type: 'string',
              description: 'Dashboard marquee text',
              is_public: true,
            },
          ],
          app: [
            {
              id: 3,
              key: 'app_name',
              value: 'Analytics Hub',
              type: 'string',
              description: 'Application name',
              is_public: true,
            },
            {
              id: 4,
              key: 'app_logo',
              value: 'logos/app-logo.png',
              type: 'file',
              description: 'Application logo',
              is_public: true,
            },
          ],
        },
      },
      getGroupedConfigurations: vi.fn(),
      bulkUpdateConfigurations: vi.fn(),
      uploadConfigurationFile: vi.fn(),
      validateJsonValue: vi.fn(),
      formatConfigKey: vi.fn().mockImplementation(key => 
        key.split('_').map(word => 
          word.charAt(0).toUpperCase() + word.slice(1),
        ).join(' '),
      ),
      formatGroupName: vi.fn().mockImplementation(groupName => 
        groupName.charAt(0).toUpperCase() + groupName.slice(1).replace(/_/g, ' '),
      ),
      getGroupIcon: vi.fn().mockImplementation(groupName => {
        const icons = {
          dashboard: 'fas-tachometer-alt',
          app: 'fas-cog',
          login: 'fas-sign-in-alt',
          default: 'fas-folder',
        }
        
        return icons[groupName] || icons.default
      }),
      isImageFile: vi.fn(),
      getFileUrl: vi.fn(),
      getFileName: vi.fn(),
      getFileAccept: vi.fn(),
    }

    useSystemConfiguration.mockReturnValue(mockSystemConfig)
  })

  it('renders correctly', () => {
    const wrapper = createWrapper()

    expect(wrapper.find('[data-testid="system-configuration"]').exists()).toBe(true)
  })

  it('displays loading state', () => {
    mockSystemConfig.loading.value = true

    const wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Loading configurations...')
  })

  it('displays error state', () => {
    mockSystemConfig.error.value = 'Failed to load configurations'

    const wrapper = createWrapper()
    
    expect(wrapper.text()).toContain('Failed to load configurations')
  })

  it('renders configuration tabs', () => {
    const wrapper = createWrapper()
    
    // Should have tabs for dashboard and app
    expect(wrapper.text()).toContain('Dashboard')
    expect(wrapper.text()).toContain('App')
  })

  it('renders configuration cards', () => {
    const wrapper = createWrapper()
    
    // Should display configuration names
    expect(wrapper.text()).toContain('Dashboard Jumbotron')
    expect(wrapper.text()).toContain('Dashboard Marquee')
    expect(wrapper.text()).toContain('App Name')
    expect(wrapper.text()).toContain('App Logo')
  })

  it('shows public/private badges', () => {
    const wrapper = createWrapper()
    
    // All test configs are public
    expect(wrapper.text()).toContain('Public')
  })

  it('handles different input types correctly', async () => {
    const wrapper = createWrapper()
    
    // Wait for component to be fully rendered
    await wrapper.vm.$nextTick()
    
    // Should have text inputs for string types
    expect(wrapper.findAll('input[type="text"]').length).toBeGreaterThan(0)
    
    // Should have textareas for JSON types
    expect(wrapper.findAll('textarea').length).toBeGreaterThan(0)
    
    // Should have file inputs for file types
    expect(wrapper.findAll('input[type="file"]').length).toBeGreaterThan(0)
  })

  it('tracks configuration changes', async () => {
    const wrapper = createWrapper()
    
    // Simulate changing a configuration value
    await wrapper.setData({
      configValues: {
        'dashboard_marquee': 'Updated marquee text',
      },
      originalValues: {
        'dashboard_marquee': 'Welcome to Analytics Hub',
      },
    })
    
    expect(wrapper.vm.hasChanges).toBe(true)
  })

  it('validates JSON configurations', async () => {
    const wrapper = createWrapper()
    
    // Mock validation function
    mockSystemConfig.validateJsonValue.mockReturnValue({
      valid: false,
      error: 'Invalid JSON format',
    })
    
    await wrapper.setData({
      configValues: {
        'dashboard_jumbotron': 'invalid json',
      },
    })
    
    await wrapper.vm.validateJsonConfig('dashboard_jumbotron', 'invalid json')
    
    expect(wrapper.vm.jsonErrors).toHaveProperty('dashboard_jumbotron')
  })

  it('handles file uploads', async () => {
    const wrapper = createWrapper()
    
    const mockFile = new File(['test'], 'test.jpg', { type: 'image/jpeg' })
    
    await wrapper.vm.handleFileUpload('app_logo', [mockFile])
    
    expect(wrapper.vm.fileUploads).toHaveProperty('app_logo')
    expect(wrapper.vm.fileUploads['app_logo']).toBe(mockFile)
  })

  it('resets configuration values', async () => {
    const wrapper = createWrapper()
    
    await wrapper.setData({
      configValues: {
        'app_name': 'Changed Name',
      },
      originalValues: {
        'app_name': 'Analytics Hub',
      },
      fileUploads: {
        'app_logo': new File(['test'], 'test.jpg'),
      },
      jsonErrors: {
        'dashboard_jumbotron': 'Some error',
      },
    })
    
    await wrapper.vm.resetConfig('app_name')
    
    expect(wrapper.vm.configValues['app_name']).toBe('Analytics Hub')
    expect(wrapper.vm.fileUploads).not.toHaveProperty('app_name')
    expect(wrapper.vm.jsonErrors).not.toHaveProperty('app_name')
  })

  it('saves all configurations', async () => {
    const wrapper = createWrapper()
    
    // Setup some changes
    await wrapper.setData({
      configValues: {
        'app_name': 'Updated App Name',
        'dashboard_marquee': 'Updated marquee',
      },
      originalValues: {
        'app_name': 'Analytics Hub',
        'dashboard_marquee': 'Welcome to Analytics Hub',
      },
    })
    
    mockSystemConfig.bulkUpdateConfigurations.mockResolvedValue({
      success: true,
      data: [],
    })
    
    await wrapper.vm.saveAllConfigurations()
    
    expect(mockSystemConfig.bulkUpdateConfigurations).toHaveBeenCalledWith([
      { key: 'app_name', value: 'Updated App Name' },
      { key: 'dashboard_marquee', value: 'Updated marquee' },
    ])
  })

  it('shows success message after saving', async () => {
    const wrapper = createWrapper()
    
    mockSystemConfig.bulkUpdateConfigurations.mockResolvedValue({
      success: true,
      data: [],
    })
    mockSystemConfig.getGroupedConfigurations.mockResolvedValue({
      success: true,
      data: {},
    })
    
    await wrapper.vm.saveAllConfigurations()
    
    expect(wrapper.vm.successMessage).toBe('System configurations saved successfully')
    expect(wrapper.vm.showSuccessSnackbar).toBe(true)
  })

  it('handles save errors gracefully', async () => {
    const wrapper = createWrapper()
    
    const consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    
    mockSystemConfig.bulkUpdateConfigurations.mockRejectedValue(
      new Error('Save failed'),
    )
    
    await wrapper.vm.saveAllConfigurations()
    
    expect(consoleErrorSpy).toHaveBeenCalledWith(
      'Error saving configurations:',
      expect.any(Error),
    )
    
    consoleErrorSpy.mockRestore()
  })

  it('loads configurations on mount', async () => {
    mockSystemConfig.getGroupedConfigurations.mockResolvedValue({
      success: true,
      data: {},
    })
    
    createWrapper()
    
    expect(mockSystemConfig.getGroupedConfigurations).toHaveBeenCalled()
  })

  it('sets first group as active tab', async () => {
    const wrapper = createWrapper()
    
    await wrapper.vm.loadConfigurations()
    
    expect(wrapper.vm.activeTab).toBe('dashboard')
  })

  it('identifies long text fields correctly', () => {
    const wrapper = createWrapper()
    
    expect(wrapper.vm.isLongText('dashboard_marquee')).toBe(true)
    expect(wrapper.vm.isLongText('app_footer')).toBe(true)
    expect(wrapper.vm.isLongText('app_name')).toBe(false)
  })

  it('finds configuration by key', () => {
    const wrapper = createWrapper()
    
    const config = wrapper.vm.findConfigByKey('dashboard_jumbotron')
    
    expect(config).toBeDefined()
    expect(config.key).toBe('dashboard_jumbotron')
  })

  it('handles group changes detection', async () => {
    const wrapper = createWrapper()
    
    await wrapper.setData({
      configValues: {
        'dashboard_marquee': 'Changed',
      },
      originalValues: {
        'dashboard_marquee': 'Original',
      },
    })
    
    expect(wrapper.vm.hasGroupChanges('dashboard')).toBe(true)
    expect(wrapper.vm.hasGroupChanges('app')).toBe(false)
  })
})

describe('useSystemConfiguration composable', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('provides correct interface', () => {
    const systemConfig = useSystemConfiguration()
    
    expect(systemConfig).toHaveProperty('loading')
    expect(systemConfig).toHaveProperty('error')
    expect(systemConfig).toHaveProperty('configurations')
    expect(systemConfig).toHaveProperty('groupedConfigurations')
    expect(systemConfig).toHaveProperty('getConfigurations')
    expect(systemConfig).toHaveProperty('getGroupedConfigurations')
    expect(systemConfig).toHaveProperty('createConfiguration')
    expect(systemConfig).toHaveProperty('updateConfiguration')
    expect(systemConfig).toHaveProperty('deleteConfiguration')
    expect(systemConfig).toHaveProperty('bulkUpdateConfigurations')
    expect(systemConfig).toHaveProperty('getPublicConfigurations')
    expect(systemConfig).toHaveProperty('uploadConfigurationFile')
    expect(systemConfig).toHaveProperty('validateJsonValue')
    expect(systemConfig).toHaveProperty('formatConfigKey')
    expect(systemConfig).toHaveProperty('formatGroupName')
    expect(systemConfig).toHaveProperty('getGroupIcon')
    expect(systemConfig).toHaveProperty('isImageFile')
    expect(systemConfig).toHaveProperty('getFileUrl')
    expect(systemConfig).toHaveProperty('getFileName')
    expect(systemConfig).toHaveProperty('getFileAccept')
  })
})
