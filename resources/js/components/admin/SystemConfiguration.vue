<template>
  <div>
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center">
            <span>
              <VIcon icon="fas-cogs" class="me-2" />
              System Configuration
            </span>
            <VBtn
              color="success"
              :disabled="systemConfig.loading.value || !hasChanges"
              :loading="systemConfig.loading.value"
              @click="saveAllConfigurations"
            >
              <VIcon icon="fas-save" start />
              Save All Changes
            </VBtn>
          </VCardTitle>

          <VCardText>
            <!-- Loading State -->
            <div v-if="systemConfig.loading.value" class="text-center py-8">
              <VProgressCircular indeterminate color="primary" size="64" />
              <div class="mt-4">Loading configurations...</div>
            </div>

            <!-- Error State -->
            <VAlert
              v-if="systemConfig.error.value"
              type="error"
              variant="tonal"
              class="mb-4"
            >
              <template #prepend>
                <VIcon icon="fas-exclamation-triangle" />
              </template>
              {{ systemConfig.error.value }}
              <template #append>
                <VBtn
                  variant="outlined"
                  size="small"
                  @click="loadConfigurations"
                >
                  <VIcon icon="fas-refresh" start />
                  Retry
                </VBtn>
              </template>
            </VAlert>

            <!-- Configuration Tabs -->
            <div v-if="!systemConfig.loading.value && !systemConfig.error.value">
              <VTabs v-model="activeTab" bg-color="primary" dark>
                <VTab
                  v-for="(group, groupName) in systemConfig.groupedConfigurations.value"
                  :key="groupName"
                  :value="groupName"
                >
                  <VIcon :icon="systemConfig.getGroupIcon(groupName)" start />
                  {{ systemConfig.formatGroupName(groupName) }}
                  <VChip
                    v-if="hasGroupChanges(groupName)"
                    color="warning"
                    size="x-small"
                    class="ms-2"
                  >
                    *
                  </VChip>
                </VTab>
              </VTabs>

              <VWindow v-model="activeTab" class="mt-4">
                <VWindowItem
                  v-for="(group, groupName) in systemConfig.groupedConfigurations.value"
                  :key="groupName"
                  :value="groupName"
                >
                  <VRow>
                    <VCol
                      v-for="config in group"
                      :key="config.key"
                      cols="12"
                      md="6"
                      class="mb-4"
                    >
                      <VCard height="100%" :class="{ 'border-warning': isModified(config.key) }">
                        <VCardTitle class="d-flex align-center">
                          {{ systemConfig.formatConfigKey(config.key) }}
                          <VSpacer />
                          <VChip
                            :color="config.is_public ? 'success' : 'secondary'"
                            size="small"
                          >
                            {{ config.is_public ? 'Public' : 'Private' }}
                          </VChip>
                        </VCardTitle>

                        <VCardSubtitle class="text-wrap">
                          {{ config.description || 'No description available' }}
                        </VCardSubtitle>

                        <VCardText>
                          <!-- String/Number Input -->
                          <div v-if="config.type === 'string' || config.type === 'number'">
                            <VTextarea
                              v-if="isLongText(config.key)"
                              v-model="configValues[config.key]"
                              :label="systemConfig.formatConfigKey(config.key)"
                              :placeholder="String(config.value)"
                              rows="3"
                              variant="outlined"
                              :class="{ 'border-warning': isModified(config.key) }"
                            />
                            <VTextField
                              v-else
                              v-model="configValues[config.key]"
                              :type="config.type === 'number' ? 'number' : 'text'"
                              :label="systemConfig.formatConfigKey(config.key)"
                              :placeholder="String(config.value)"
                              variant="outlined"
                              :class="{ 'border-warning': isModified(config.key) }"
                            />
                          </div>

                          <!-- Boolean Input -->
                          <div v-else-if="config.type === 'boolean'">
                            <VSwitch
                              v-model="configValues[config.key]"
                              :label="configValues[config.key] ? 'Enabled' : 'Disabled'"
                              color="success"
                              inset
                            />
                          </div>

                          <!-- JSON Input -->
                          <div v-else-if="config.type === 'json'">
                            <VTextarea
                              v-model="configValues[config.key]"
                              :label="systemConfig.formatConfigKey(config.key)"
                              :placeholder="JSON.stringify(config.value, null, 2)"
                              rows="6"
                              variant="outlined"
                              class="font-monospace"
                              :class="{ 'border-warning': isModified(config.key) }"
                              :error="!!jsonErrors[config.key]"
                              :error-messages="jsonErrors[config.key]"
                            />
                          </div>

                          <!-- File Input -->
                          <div v-else-if="config.type === 'file'">
                            <VFileInput
                              :label="systemConfig.formatConfigKey(config.key)"
                              :accept="systemConfig.getFileAccept(config.key)"
                              variant="outlined"
                              @change="handleFileUpload(config.key, $event)"
                            />
                            <div v-if="config.value" class="mt-2">
                              <VImg
                                v-if="systemConfig.isImageFile(config.value)"
                                :src="systemConfig.getFileUrl(config.value)"
                                :alt="config.key"
                                max-height="100"
                                max-width="200"
                                class="rounded"
                              />
                              <div v-else class="text-medium-emphasis">
                                <VIcon icon="fas-file" class="me-2" />
                                Current file: {{ systemConfig.getFileName(config.value) }}
                              </div>
                            </div>
                          </div>

                          <!-- Reset Button -->
                          <div v-if="isModified(config.key)" class="mt-4">
                            <VBtn
                              variant="outlined"
                              color="secondary"
                              size="small"
                              @click="resetConfig(config.key)"
                            >
                              <VIcon icon="fas-undo" start />
                              Reset
                            </VBtn>
                          </div>
                        </VCardText>
                      </VCard>
                    </VCol>
                  </VRow>
                </VWindowItem>
              </VWindow>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Success Snackbar -->
    <VSnackbar
      v-model="showSuccessSnackbar"
      color="success"
      timeout="3000"
      location="bottom right"
    >
      <VIcon icon="fas-check-circle" start />
      {{ successMessage }}
    </VSnackbar>
  </div>
</template>

<script setup>
import { useSystemConfiguration } from '@/composables/useSystemConfiguration'
import { computed, onMounted, ref, watch } from 'vue'

// Composables
const systemConfig = useSystemConfiguration()

// Component state
const configValues = ref({})
const originalValues = ref({})
const activeTab = ref('dashboard')
const jsonErrors = ref({})
const successMessage = ref('')
const showSuccessSnackbar = ref(false)
const fileUploads = ref({})

// Computed
const hasChanges = computed(() => {
  return Object.keys(configValues.value).some(key => isModified(key))
})

// Methods
const loadConfigurations = async () => {
  try {
    await systemConfig.getGroupedConfigurations()
    initializeConfigValues()
    
    // Set first group as active tab if no tab is set
    if (!activeTab.value && Object.keys(systemConfig.groupedConfigurations.value).length > 0) {
      activeTab.value = Object.keys(systemConfig.groupedConfigurations.value)[0]
    }
  } catch (error) {
    console.error('Error loading configurations:', error)
  }
}

const initializeConfigValues = () => {
  configValues.value = {}
  originalValues.value = {}
  
  Object.values(systemConfig.groupedConfigurations.value).forEach(group => {
    group.forEach(config => {
      const value = config.type === 'json' 
        ? JSON.stringify(config.value, null, 2)
        : config.value
      
      configValues.value[config.key] = value
      originalValues.value[config.key] = value
    })
  })
}

const isModified = (key) => {
  return configValues.value[key] !== originalValues.value[key] || fileUploads.value[key]
}

const hasGroupChanges = (groupName) => {
  const group = systemConfig.groupedConfigurations.value[groupName] || []
  return group.some(config => isModified(config.key))
}

const resetConfig = (key) => {
  configValues.value[key] = originalValues.value[key]
  delete fileUploads.value[key]
  delete jsonErrors.value[key]
}

const validateJsonConfig = (key, value) => {
  const validation = systemConfig.validateJsonValue(value)
  if (validation.valid) {
    delete jsonErrors.value[key]
  } else {
    jsonErrors.value[key] = validation.error
  }
  return validation.valid
}

const handleFileUpload = (key, files) => {
  if (files && files.length > 0) {
    fileUploads.value[key] = files[0]
  }
}

const saveAllConfigurations = async () => {
  try {
    // Validate all JSON configurations
    let hasJsonErrors = false
    Object.values(systemConfig.groupedConfigurations.value).forEach(group => {
      group.forEach(config => {
        if (config.type === 'json' && isModified(config.key)) {
          if (!validateJsonConfig(config.key, configValues.value[config.key])) {
            hasJsonErrors = true
          }
        }
      })
    })

    if (hasJsonErrors) {
      return
    }

    // Handle file uploads separately
    for (const [key, file] of Object.entries(fileUploads.value)) {
      if (file) {
        await systemConfig.uploadConfigurationFile(key, file)
      }
    }

    // Prepare configurations for bulk update
    const configurations = []
    
    Object.values(systemConfig.groupedConfigurations.value).forEach(group => {
      group.forEach(config => {
        if (isModified(config.key) && !fileUploads.value[config.key]) {
          let value = configValues.value[config.key]
          
          // Handle JSON type
          if (config.type === 'json') {
            value = value.trim() ? JSON.parse(value) : null
          }
          
          configurations.push({
            key: config.key,
            value: value
          })
        }
      })
    })

    // Bulk update configurations
    if (configurations.length > 0) {
      await systemConfig.bulkUpdateConfigurations(configurations)
    }

    // Reload configurations to get updated values
    await loadConfigurations()
    
    successMessage.value = 'System configurations saved successfully'
    showSuccessSnackbar.value = true
    
  } catch (error) {
    console.error('Error saving configurations:', error)
  }
}

const isLongText = (key) => {
  return key.includes('marquee') || key.includes('footer') || key.includes('description')
}

const findConfigByKey = (key) => {
  for (const group of Object.values(systemConfig.groupedConfigurations.value)) {
    const config = group.find(c => c.key === key)
    if (config) return config
  }
  return null
}

// Watchers
watch(
  configValues,
  (newValues) => {
    // Validate JSON configs on change
    Object.keys(newValues).forEach(key => {
      const config = findConfigByKey(key)
      if (config && config.type === 'json') {
        validateJsonConfig(key, newValues[key])
      }
    })
  },
  { deep: true }
)

// Lifecycle
onMounted(() => {
  loadConfigurations()
})
</script>

<style scoped>
.border-warning {
  border: 1px solid rgb(var(--v-theme-warning)) !important;
}

.font-monospace {
  font-family: 'Courier New', Courier, monospace;
  font-size: 0.875rem;
}
</style>
