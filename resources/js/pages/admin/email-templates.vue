<template>
  <div>
    <VCard class="mb-6">
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <h4 class="text-h4 font-weight-semibold mb-0">
              Email Templates
            </h4>
            <p class="text-body-1 mb-0">
              Manage email templates for system communications
            </p>
          </VCol>
          <VCol cols="12" md="6" class="d-flex justify-end align-center">
            <VBtn 
              color="primary" 
              @click="openCreateDialog"
              prepend-icon="tabler-plus"
            >
              Create Template
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Templates List -->
    <VCard>
      <VCardText>
        <!-- Filters -->
        <VRow class="mb-4">
          <VCol cols="12" md="4">
            <VTextField
              v-model="searchQuery"
              placeholder="Search templates..."
              prepend-inner-icon="tabler-search"
              clearable
              @input="debouncedSearch"
            />
          </VCol>
          <VCol cols="12" md="3">
            <VSelect
              v-model="selectedType"
              :items="typeOptions"
              label="Template Type"
              clearable
              @update:model-value="fetchTemplates"
            />
          </VCol>
          <VCol cols="12" md="3">
            <VSelect
              v-model="selectedStatus"
              :items="statusOptions"
              label="Status"
              clearable
              @update:model-value="fetchTemplates"
            />
          </VCol>
        </VRow>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-6">
          <VProgressCircular indeterminate />
          <p class="mt-2">Loading templates...</p>
        </div>

        <!-- Templates Data Table -->
        <VDataTableServer
          v-else
          v-model:items-per-page="perPage"
          :headers="headers"
          :items="templates"
          :items-length="totalTemplates"
          :loading="loading"
          class="elevation-1"
          @update:options="loadItems"
        >
          <!-- Type Column -->
          <template #item.type="{ item }">
            <VChip 
              :color="getTypeColor(item.type)"
              size="small"
              variant="tonal"
            >
              {{ formatType(item.type) }}
            </VChip>
          </template>

          <!-- Status Column -->
          <template #item.is_active="{ item }">
            <VChip 
              :color="item.is_active ? 'success' : 'error'"
              size="small"
              variant="tonal"
            >
              {{ item.is_active ? 'Active' : 'Inactive' }}
            </VChip>
          </template>

          <!-- Actions Column -->
          <template #item.actions="{ item }">
            <div class="d-flex gap-1">
              <VTooltip text="Preview">
                <template #activator="{ props }">
                  <VBtn
                    icon
                    size="small"
                    color="info"
                    variant="text"
                    v-bind="props"
                    @click="previewTemplate(item)"
                  >
                    <VIcon icon="tabler-eye" />
                  </VBtn>
                </template>
              </VTooltip>

              <VTooltip text="Edit">
                <template #activator="{ props }">
                  <VBtn
                    icon
                    size="small"
                    color="primary"
                    variant="text"
                    v-bind="props"
                    @click="editTemplate(item)"
                  >
                    <VIcon icon="tabler-edit" />
                  </VBtn>
                </template>
              </VTooltip>

              <VTooltip text="Clone">
                <template #activator="{ props }">
                  <VBtn
                    icon
                    size="small"
                    color="secondary"
                    variant="text"
                    v-bind="props"
                    @click="cloneTemplate(item)"
                  >
                    <VIcon icon="tabler-copy" />
                  </VBtn>
                </template>
              </VTooltip>

              <VTooltip :text="item.is_active ? 'Deactivate' : 'Activate'">
                <template #activator="{ props }">
                  <VBtn
                    icon
                    size="small"
                    :color="item.is_active ? 'warning' : 'success'"
                    variant="text"
                    v-bind="props"
                    @click="toggleStatus(item)"
                  >
                    <VIcon :icon="item.is_active ? 'tabler-toggle-right' : 'tabler-toggle-left'" />
                  </VBtn>
                </template>
              </VTooltip>

              <VTooltip text="Delete">
                <template #activator="{ props }">
                  <VBtn
                    icon
                    size="small"
                    color="error"
                    variant="text"
                    v-bind="props"
                    @click="deleteTemplate(item)"
                  >
                    <VIcon icon="tabler-trash" />
                  </VBtn>
                </template>
              </VTooltip>
            </div>
          </template>
        </VDataTableServer>
      </VCardText>
    </VCard>

    <!-- Create/Edit Dialog -->
    <VDialog
      v-model="showDialog"
      max-width="1000px"
      persistent
    >
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          <span>{{ editingTemplate ? 'Edit' : 'Create' }} Email Template</span>
          <VBtn
            icon
            size="small"
            @click="closeDialog"
          >
            <VIcon icon="tabler-x" />
          </VBtn>
        </VCardTitle>

        <VCardText>
          <VForm ref="formRef" v-model="formValid">
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="formData.name"
                  label="Template Name"
                  :rules="[v => !!v || 'Name is required']"
                  required
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="formData.type"
                  :items="templateTypes"
                  label="Template Type"
                  :rules="[v => !!v || 'Type is required']"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model="formData.subject"
                  label="Email Subject"
                  :rules="[v => !!v || 'Subject is required']"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="formData.description"
                  label="Description"
                  rows="3"
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="formData.html_content"
                  label="HTML Content"
                  rows="10"
                  :rules="[v => !!v || 'HTML content is required']"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="formData.text_content"
                  label="Text Content (Fallback)"
                  rows="6"
                />
              </VCol>
              <VCol cols="12">
                <VSwitch
                  v-model="formData.is_active"
                  label="Active"
                  color="primary"
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="outlined"
            @click="closeDialog"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            :disabled="!formValid"
            @click="saveTemplate"
          >
            {{ editingTemplate ? 'Update' : 'Create' }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Preview Dialog -->
    <VDialog
      v-model="showPreviewDialog"
      max-width="800px"
    >
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          <span>Email Preview: {{ previewData?.name }}</span>
          <VBtn
            icon
            size="small"
            @click="showPreviewDialog = false"
          >
            <VIcon icon="tabler-x" />
          </VBtn>
        </VCardTitle>

        <VCardText>
          <VTabs v-model="previewTab">
            <VTab value="html">HTML Preview</VTab>
            <VTab value="text">Text Preview</VTab>
          </VTabs>

          <VWindow v-model="previewTab" class="mt-4">
            <VWindowItem value="html">
              <div class="preview-container" v-html="previewData?.html_content" />
            </VWindowItem>
            <VWindowItem value="text">
              <pre class="text-content-preview">{{ previewData?.text_content }}</pre>
            </VWindowItem>
          </VWindow>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            @click="showPreviewDialog = false"
          >
            Close
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<script setup>
import { useApi } from '@/composables/useApi'
import { useToast } from '@/composables/useToast'
import { debounce } from 'lodash-es'
import { computed, onMounted, ref } from 'vue'

// Composables
const { api } = useApi()
const { showToast } = useToast()

// Data
const templates = ref([])
const loading = ref(false)
const saving = ref(false)
const totalTemplates = ref(0)
const perPage = ref(10)
const searchQuery = ref('')
const selectedType = ref(null)
const selectedStatus = ref(null)

// Dialog states
const showDialog = ref(false)
const showPreviewDialog = ref(false)
const formValid = ref(false)
const editingTemplate = ref(null)
const previewData = ref(null)
const previewTab = ref('html')

// Form
const formRef = ref()
const formData = ref({
  name: '',
  type: '',
  subject: '',
  description: '',
  html_content: '',
  text_content: '',
  is_active: true
})

// Table headers
const headers = [
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Type', key: 'type', sortable: true },
  { title: 'Subject', key: 'subject', sortable: false },
  { title: 'Status', key: 'is_active', sortable: true },
  { title: 'Created', key: 'created_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, width: '200px' }
]

// Options
const templateTypes = [
  { title: 'Invitation', value: 'invitation' },
  { title: 'Password Reset', value: 'password_reset' },
  { title: 'Welcome', value: 'welcome' },
  { title: 'Notification', value: 'notification' },
  { title: 'General', value: 'general' }
]

const typeOptions = computed(() => [
  { title: 'All Types', value: null },
  ...templateTypes
])

const statusOptions = [
  { title: 'All Status', value: null },
  { title: 'Active', value: true },
  { title: 'Inactive', value: false }
]

// Methods
const fetchTemplates = async (options = {}) => {
  loading.value = true
  try {
    const params = {
      page: options.page || 1,
      per_page: options.itemsPerPage || perPage.value,
      sort_by: options.sortBy?.[0]?.key || 'created_at',
      sort_order: options.sortBy?.[0]?.order || 'desc',
      search: searchQuery.value,
      type: selectedType.value,
      is_active: selectedStatus.value
    }

    const response = await api.get('/admin/email-templates', { params })
    templates.value = response.data.data
    totalTemplates.value = response.data.total
  } catch (error) {
    showToast('Error fetching templates', 'error')
    console.error('Error fetching templates:', error)
  } finally {
    loading.value = false
  }
}

const loadItems = (options) => {
  fetchTemplates(options)
}

const debouncedSearch = debounce(() => {
  fetchTemplates()
}, 300)

const openCreateDialog = () => {
  editingTemplate.value = null
  resetForm()
  showDialog.value = true
}

const editTemplate = (template) => {
  editingTemplate.value = template
  formData.value = { ...template }
  showDialog.value = true
}

const closeDialog = () => {
  showDialog.value = false
  resetForm()
}

const resetForm = () => {
  formData.value = {
    name: '',
    type: '',
    subject: '',
    description: '',
    html_content: '',
    text_content: '',
    is_active: true
  }
  formRef.value?.resetValidation()
}

const saveTemplate = async () => {
  if (!formRef.value?.validate()) return

  saving.value = true
  try {
    if (editingTemplate.value) {
      await api.put(`/admin/email-templates/${editingTemplate.value.id}`, formData.value)
      showToast('Template updated successfully', 'success')
    } else {
      await api.post('/admin/email-templates', formData.value)
      showToast('Template created successfully', 'success')
    }
    
    closeDialog()
    fetchTemplates()
  } catch (error) {
    showToast('Error saving template', 'error')
    console.error('Error saving template:', error)
  } finally {
    saving.value = false
  }
}

const previewTemplate = async (template) => {
  try {
    const response = await api.post(`/admin/email-templates/${template.id}/preview`, {
      sample_data: {
        app_name: 'Analytics Hub',
        user_name: 'John Doe',
        reset_link: 'https://example.com/reset',
        login_url: 'https://example.com/login'
      }
    })
    previewData.value = response.data
    showPreviewDialog.value = true
  } catch (error) {
    showToast('Error loading preview', 'error')
    console.error('Error loading preview:', error)
  }
}

const cloneTemplate = async (template) => {
  try {
    await api.post(`/admin/email-templates/${template.id}/clone`)
    showToast('Template cloned successfully', 'success')
    fetchTemplates()
  } catch (error) {
    showToast('Error cloning template', 'error')
    console.error('Error cloning template:', error)
  }
}

const toggleStatus = async (template) => {
  try {
    await api.post(`/admin/email-templates/${template.id}/toggle-status`)
    showToast(`Template ${template.is_active ? 'deactivated' : 'activated'} successfully`, 'success')
    fetchTemplates()
  } catch (error) {
    showToast('Error updating template status', 'error')
    console.error('Error updating template status:', error)
  }
}

const deleteTemplate = async (template) => {
  if (!confirm(`Are you sure you want to delete "${template.name}"?`)) return

  try {
    await api.delete(`/admin/email-templates/${template.id}`)
    showToast('Template deleted successfully', 'success')
    fetchTemplates()
  } catch (error) {
    showToast('Error deleting template', 'error')
    console.error('Error deleting template:', error)
  }
}

const getTypeColor = (type) => {
  const colors = {
    invitation: 'primary',
    password_reset: 'warning',
    welcome: 'success',
    notification: 'info',
    general: 'secondary'
  }
  return colors[type] || 'secondary'
}

const formatType = (type) => {
  return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

// Lifecycle
onMounted(() => {
  fetchTemplates()
})
</script>

<style scoped>
.preview-container {
  padding: 16px;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 4px;
  max-block-size: 400px;
  overflow-y: auto;
}

.text-content-preview {
  padding: 16px;
  border-radius: 4px;
  background-color: rgba(var(--v-theme-surface-variant), 0.1);
  max-block-size: 400px;
  overflow-y: auto;
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>
