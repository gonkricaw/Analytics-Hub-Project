<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1">
          Permission Management
        </h4>
        <p class="text-muted">
          Manage system permissions and access controls
        </p>
      </div>
      <VBtn
        color="primary"
        @click="showCreateDialog = true"
      >
        <VIcon
          icon="tabler-plus"
          class="me-2"
        />
        Add Permission
      </VBtn>
    </div>

    <!-- Filters and Search -->
    <VCard class="mb-4">
      <VCardText>
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="searchQuery"
              placeholder="Search permissions..."
              prepend-inner-icon="tabler-search"
              clearable
              @input="handleSearch"
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VSelect
              v-model="perPage"
              :items="perPageOptions"
              label="Items per page"
              @update:model-value="loadPermissions"
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VBtn
              color="secondary"
              variant="outlined"
              block
              @click="resetFilters"
            >
              Reset Filters
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Permissions Table -->
    <VCard>
      <VCardText>
        <VDataTableServer
          v-model:items-per-page="perPage"
          v-model:page="currentPage"
          :headers="headers"
          :items="permissions"
          :items-length="totalItems"
          :loading="loading"
          item-value="id"
          @update:options="handleTableUpdate"
        >
          <!-- Permission Name -->
          <template #item.name="{ item }">
            <div class="d-flex flex-column">
              <span class="font-weight-medium">{{ item.name }}</span>
              <small class="text-muted">{{ item.display_name }}</small>
            </div>
          </template>

          <!-- Description -->
          <template #item.description="{ item }">
            <span
              class="text-truncate"
              style="max-inline-size: 200px;"
            >
              {{ item.description }}
            </span>
          </template>

          <!-- Created Date -->
          <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="d-flex gap-2">
              <VBtn
                icon
                size="small"
                color="info"
                variant="text"
                @click="viewPermission(item)"
              >
                <VIcon icon="tabler-eye" />
                <VTooltip activator="parent">
                  View Details
                </VTooltip>
              </VBtn>
              <VBtn
                icon
                size="small"
                color="warning"
                variant="text"
                @click="editPermission(item)"
              >
                <VIcon icon="tabler-edit" />
                <VTooltip activator="parent">
                  Edit Permission
                </VTooltip>
              </VBtn>
              <VBtn
                icon
                size="small"
                color="error"
                variant="text"
                @click="deletePermission(item)"
              >
                <VIcon icon="tabler-trash" />
                <VTooltip activator="parent">
                  Delete Permission
                </VTooltip>
              </VBtn>
            </div>
          </template>
        </VDataTableServer>
      </VCardText>
    </VCard>

    <!-- Create/Edit Permission Dialog -->
    <VDialog
      v-model="showCreateDialog"
      max-width="600px"
      persistent
    >
      <VCard>
        <VCardTitle>
          <span class="text-h5">
            {{ editingPermission ? 'Edit Permission' : 'Create Permission' }}
          </span>
        </VCardTitle>

        <VCardText>
          <VForm
            ref="permissionForm"
            @submit.prevent="savePermission"
          >
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="permissionData.name"
                  label="Permission Name"
                  placeholder="e.g., users.create"
                  :rules="[rules.required, rules.permissionName]"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model="permissionData.display_name"
                  label="Display Name"
                  placeholder="e.g., Create Users"
                  :rules="[rules.required]"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="permissionData.description"
                  label="Description"
                  placeholder="Describe what this permission allows..."
                  rows="3"
                  :rules="[rules.required]"
                  required
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="error"
            variant="outlined"
            @click="closeDialog"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            @click="savePermission"
          >
            {{ editingPermission ? 'Update' : 'Create' }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- View Permission Dialog -->
    <VDialog
      v-model="showViewDialog"
      max-width="500px"
    >
      <VCard v-if="selectedPermission">
        <VCardTitle>
          <span class="text-h5">Permission Details</span>
        </VCardTitle>

        <VCardText>
          <VList>
            <VListItem>
              <VListItemTitle>Name</VListItemTitle>
              <VListItemSubtitle>{{ selectedPermission.name }}</VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Display Name</VListItemTitle>
              <VListItemSubtitle>{{ selectedPermission.display_name }}</VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Description</VListItemTitle>
              <VListItemSubtitle>{{ selectedPermission.description }}</VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Created</VListItemTitle>
              <VListItemSubtitle>{{ formatDate(selectedPermission.created_at) }}</VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Last Updated</VListItemTitle>
              <VListItemSubtitle>{{ formatDate(selectedPermission.updated_at) }}</VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="primary"
            @click="showViewDialog = false"
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
import { nextTick, onMounted, ref } from 'vue'

const { api } = useApi()
const { toast } = useToast()

// Data
const permissions = ref([])
const loading = ref(false)
const saving = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const perPage = ref(15)
const totalItems = ref(0)

// Dialogs
const showCreateDialog = ref(false)
const showViewDialog = ref(false)
const editingPermission = ref(null)
const selectedPermission = ref(null)

// Form data
const permissionData = ref({
  name: '',
  display_name: '',
  description: '',
})

// Form reference
const permissionForm = ref()

// Table configuration
const headers = [
  { title: 'Permission', key: 'name', sortable: true },
  { title: 'Description', key: 'description', sortable: false },
  { title: 'Created', key: 'created_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, width: 120 },
]

const perPageOptions = [10, 15, 25, 50, 100]

// Validation rules
const rules = {
  required: v => !!v || 'This field is required',
  permissionName: v => /^[a-z_]+(\.[a-z_]+)*$/.test(v) || 'Permission name must use lowercase letters, underscores, and dots only',
}

// Methods
const loadPermissions = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
      search: searchQuery.value,
    }

    const response = await api.get('/admin/permissions', { params })
    
    if (response.data.success) {
      permissions.value = response.data.data.data
      totalItems.value = response.data.data.total
    }
  } catch (error) {
    toast.error('Failed to load permissions')
    console.error('Error loading permissions:', error)
  } finally {
    loading.value = false
  }
}

const handleTableUpdate = options => {
  currentPage.value = options.page
  perPage.value = options.itemsPerPage
  loadPermissions()
}

const handleSearch = () => {
  currentPage.value = 1
  loadPermissions()
}

const resetFilters = () => {
  searchQuery.value = ''
  currentPage.value = 1
  perPage.value = 15
  loadPermissions()
}

const viewPermission = permission => {
  selectedPermission.value = permission
  showViewDialog.value = true
}

const editPermission = permission => {
  editingPermission.value = permission
  permissionData.value = {
    name: permission.name,
    display_name: permission.display_name,
    description: permission.description,
  }
  showCreateDialog.value = true
}

const savePermission = async () => {
  const form = await permissionForm.value?.validate()
  if (!form?.valid) return

  saving.value = true
  try {
    let response
    
    if (editingPermission.value) {
      response = await api.put(`/admin/permissions/${editingPermission.value.id}`, permissionData.value)
    } else {
      response = await api.post('/admin/permissions', permissionData.value)
    }

    if (response.data.success) {
      toast.success(editingPermission.value ? 'Permission updated successfully' : 'Permission created successfully')
      closeDialog()
      loadPermissions()
    }
  } catch (error) {
    toast.error('Failed to save permission')
    console.error('Error saving permission:', error)
  } finally {
    saving.value = false
  }
}

const deletePermission = async permission => {
  if (!confirm(`Are you sure you want to delete the permission "${permission.display_name}"?`)) {
    return
  }

  try {
    const response = await api.delete(`/admin/permissions/${permission.id}`)
    
    if (response.data.success) {
      toast.success('Permission deleted successfully')
      loadPermissions()
    }
  } catch (error) {
    toast.error('Failed to delete permission')
    console.error('Error deleting permission:', error)
  }
}

const closeDialog = () => {
  showCreateDialog.value = false
  editingPermission.value = null
  permissionData.value = {
    name: '',
    display_name: '',
    description: '',
  }
  nextTick(() => {
    permissionForm.value?.resetValidation()
  })
}

const formatDate = date => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// Lifecycle
onMounted(() => {
  loadPermissions()
})
</script>
