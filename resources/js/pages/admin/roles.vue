<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1">Role Management</h4>
        <p class="text-muted">Manage user roles and permissions</p>
      </div>
      <VBtn
        color="primary"
        @click="showCreateDialog = true"
      >
        <VIcon icon="tabler-plus" class="me-2" />
        Add Role
      </VBtn>
    </div>

    <!-- Filters and Search -->
    <VCard class="mb-4">
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <VTextField
              v-model="searchQuery"
              placeholder="Search roles..."
              prepend-inner-icon="tabler-search"
              clearable
              @input="handleSearch"
            />
          </VCol>
          <VCol cols="12" md="3">
            <VSelect
              v-model="perPage"
              :items="perPageOptions"
              label="Items per page"
              @update:model-value="loadRoles"
            />
          </VCol>
          <VCol cols="12" md="3">
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

    <!-- Roles Table -->
    <VCard>
      <VCardText>
        <VDataTableServer
          v-model:items-per-page="perPage"
          v-model:page="currentPage"
          :headers="headers"
          :items="roles"
          :items-length="totalItems"
          :loading="loading"
          item-value="id"
          @update:options="handleTableUpdate"
        >
          <!-- Role Name -->
          <template #item.name="{ item }">
            <div class="d-flex flex-column">
              <span class="font-weight-medium">{{ item.display_name }}</span>
              <small class="text-muted">{{ item.name }}</small>
            </div>
          </template>

          <!-- Permissions Count -->
          <template #item.permissions_count="{ item }">
            <VChip
              color="primary"
              size="small"
              variant="outlined"
            >
              {{ item.permissions_count || 0 }} permissions
            </VChip>
          </template>

          <!-- Description -->
          <template #item.description="{ item }">
            <span class="text-truncate" style="max-inline-size: 200px;">
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
                @click="viewRole(item)"
              >
                <VIcon icon="tabler-eye" />
                <VTooltip activator="parent">View Details</VTooltip>
              </VBtn>
              <VBtn
                icon
                size="small"
                color="success"
                variant="text"
                @click="managePermissions(item)"
              >
                <VIcon icon="tabler-shield-check" />
                <VTooltip activator="parent">Manage Permissions</VTooltip>
              </VBtn>
              <VBtn
                icon
                size="small"
                color="warning"
                variant="text"
                @click="editRole(item)"
              >
                <VIcon icon="tabler-edit" />
                <VTooltip activator="parent">Edit Role</VTooltip>
              </VBtn>
              <VBtn
                v-if="!isSystemRole(item.name)"
                icon
                size="small"
                color="error"
                variant="text"
                @click="deleteRole(item)"
              >
                <VIcon icon="tabler-trash" />
                <VTooltip activator="parent">Delete Role</VTooltip>
              </VBtn>
            </div>
          </template>
        </VDataTableServer>
      </VCardText>
    </VCard>

    <!-- Create/Edit Role Dialog -->
    <VDialog
      v-model="showCreateDialog"
      max-width="600px"
      persistent
    >
      <VCard>
        <VCardTitle>
          <span class="text-h5">
            {{ editingRole ? 'Edit Role' : 'Create Role' }}
          </span>
        </VCardTitle>

        <VCardText>
          <VForm ref="roleForm" @submit.prevent="saveRole">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="roleData.name"
                  label="Role Name"
                  placeholder="e.g., editor"
                  :rules="[rules.required, rules.roleName]"
                  :disabled="editingRole && isSystemRole(editingRole.name)"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model="roleData.display_name"
                  label="Display Name"
                  placeholder="e.g., Content Editor"
                  :rules="[rules.required]"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="roleData.description"
                  label="Description"
                  placeholder="Describe what this role does..."
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
            @click="saveRole"
          >
            {{ editingRole ? 'Update' : 'Create' }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Manage Permissions Dialog -->
    <VDialog
      v-model="showPermissionsDialog"
      max-width="800px"
      persistent
    >
      <VCard v-if="selectedRole">
        <VCardTitle>
          <span class="text-h5">Manage Permissions - {{ selectedRole.display_name }}</span>
        </VCardTitle>

        <VCardText>
          <VTextField
            v-model="permissionSearch"
            placeholder="Search permissions..."
            prepend-inner-icon="tabler-search"
            clearable
            class="mb-4"
          />

          <div class="permission-groups">
            <div
              v-for="(groupPermissions, group) in groupedPermissions"
              :key="group"
              class="mb-4"
            >
              <VRow class="align-center mb-2">
                <VCol>
                  <h6 class="text-h6 text-capitalize">{{ group.replace('_', ' ') }}</h6>
                </VCol>
                <VCol cols="auto">
                  <VBtn
                    size="small"
                    variant="outlined"
                    @click="toggleGroupPermissions(group, groupPermissions)"
                  >
                    {{ isGroupSelected(groupPermissions) ? 'Deselect All' : 'Select All' }}
                  </VBtn>
                </VCol>
              </VRow>
              
              <VRow>
                <VCol
                  v-for="permission in groupPermissions"
                  :key="permission.id"
                  cols="12"
                  md="6"
                  lg="4"
                >
                  <VCheckbox
                    v-model="selectedPermissions"
                    :value="permission.id"
                    :label="permission.display_name"
                    :hint="permission.description"
                    persistent-hint
                    density="compact"
                  />
                </VCol>
              </VRow>
            </div>
          </div>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="error"
            variant="outlined"
            @click="closePermissionsDialog"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="savingPermissions"
            @click="saveRolePermissions"
          >
            Save Permissions
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- View Role Dialog -->
    <VDialog
      v-model="showViewDialog"
      max-width="600px"
    >
      <VCard v-if="selectedRole">
        <VCardTitle>
          <span class="text-h5">Role Details</span>
        </VCardTitle>

        <VCardText>
          <VList>
            <VListItem>
              <VListItemTitle>Display Name</VListItemTitle>
              <VListItemSubtitle>{{ selectedRole.display_name }}</VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>System Name</VListItemTitle>
              <VListItemSubtitle>{{ selectedRole.name }}</VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Description</VListItemTitle>
              <VListItemSubtitle>{{ selectedRole.description }}</VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Permissions</VListItemTitle>
              <VListItemSubtitle>
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <VChip
                    v-for="permission in selectedRole.permissions"
                    :key="permission.id"
                    size="small"
                    color="primary"
                    variant="outlined"
                  >
                    {{ permission.display_name }}
                  </VChip>
                </div>
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Created</VListItemTitle>
              <VListItemSubtitle>{{ formatDate(selectedRole.created_at) }}</VListItemSubtitle>
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
import { computed, nextTick, onMounted, ref } from 'vue'

const { api } = useApi()
const { toast } = useToast()

// Data
const roles = ref([])
const permissions = ref([])
const loading = ref(false)
const saving = ref(false)
const savingPermissions = ref(false)
const searchQuery = ref('')
const permissionSearch = ref('')
const currentPage = ref(1)
const perPage = ref(15)
const totalItems = ref(0)

// Dialogs
const showCreateDialog = ref(false)
const showViewDialog = ref(false)
const showPermissionsDialog = ref(false)
const editingRole = ref(null)
const selectedRole = ref(null)
const selectedPermissions = ref([])

// Form data
const roleData = ref({
  name: '',
  display_name: '',
  description: ''
})

// Form reference
const roleForm = ref()

// Table configuration
const headers = [
  { title: 'Role', key: 'name', sortable: true },
  { title: 'Permissions', key: 'permissions_count', sortable: true },
  { title: 'Description', key: 'description', sortable: false },
  { title: 'Created', key: 'created_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, width: 160 }
]

const perPageOptions = [10, 15, 25, 50, 100]

// System roles that shouldn't be deleted
const systemRoles = ['super_admin', 'admin']

// Validation rules
const rules = {
  required: v => !!v || 'This field is required',
  roleName: v => /^[a-z_]+$/.test(v) || 'Role name must use lowercase letters and underscores only'
}

// Computed
const groupedPermissions = computed(() => {
  const filtered = permissions.value.filter(permission =>
    permission.display_name.toLowerCase().includes(permissionSearch.value.toLowerCase()) ||
    permission.name.toLowerCase().includes(permissionSearch.value.toLowerCase())
  )

  return filtered.reduce((groups, permission) => {
    const group = permission.name.split('.')[0]
    if (!groups[group]) {
      groups[group] = []
    }
    groups[group].push(permission)
    return groups
  }, {})
})

// Methods
const loadRoles = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
      search: searchQuery.value,
      include: 'permissions'
    }

    const response = await api.get('/admin/roles', { params })
    
    if (response.data.success) {
      roles.value = response.data.data.data
      totalItems.value = response.data.data.total
    }
  } catch (error) {
    toast.error('Failed to load roles')
    console.error('Error loading roles:', error)
  } finally {
    loading.value = false
  }
}

const loadPermissions = async () => {
  try {
    const response = await api.get('/admin/permissions', {
      params: { per_page: 1000 } // Get all permissions
    })
    
    if (response.data.success) {
      permissions.value = response.data.data.data
    }
  } catch (error) {
    console.error('Error loading permissions:', error)
  }
}

const handleTableUpdate = (options) => {
  currentPage.value = options.page
  perPage.value = options.itemsPerPage
  loadRoles()
}

const handleSearch = () => {
  currentPage.value = 1
  loadRoles()
}

const resetFilters = () => {
  searchQuery.value = ''
  currentPage.value = 1
  perPage.value = 15
  loadRoles()
}

const isSystemRole = (roleName) => {
  return systemRoles.includes(roleName)
}

const viewRole = async (role) => {
  try {
    const response = await api.get(`/admin/roles/${role.id}`)
    if (response.data.success) {
      selectedRole.value = response.data.data
      showViewDialog.value = true
    }
  } catch (error) {
    toast.error('Failed to load role details')
  }
}

const editRole = (role) => {
  editingRole.value = role
  roleData.value = {
    name: role.name,
    display_name: role.display_name,
    description: role.description
  }
  showCreateDialog.value = true
}

const managePermissions = async (role) => {
  try {
    const response = await api.get(`/admin/roles/${role.id}`)
    if (response.data.success) {
      selectedRole.value = response.data.data
      selectedPermissions.value = selectedRole.value.permissions.map(p => p.id)
      showPermissionsDialog.value = true
    }
  } catch (error) {
    toast.error('Failed to load role permissions')
  }
}

const saveRole = async () => {
  const form = await roleForm.value?.validate()
  if (!form?.valid) return

  saving.value = true
  try {
    let response
    
    if (editingRole.value) {
      response = await api.put(`/admin/roles/${editingRole.value.id}`, roleData.value)
    } else {
      response = await api.post('/admin/roles', roleData.value)
    }

    if (response.data.success) {
      toast.success(editingRole.value ? 'Role updated successfully' : 'Role created successfully')
      closeDialog()
      loadRoles()
    }
  } catch (error) {
    toast.error('Failed to save role')
    console.error('Error saving role:', error)
  } finally {
    saving.value = false
  }
}

const saveRolePermissions = async () => {
  savingPermissions.value = true
  try {
    const response = await api.post(`/admin/roles/${selectedRole.value.id}/permissions`, {
      permission_ids: selectedPermissions.value
    })

    if (response.data.success) {
      toast.success('Role permissions updated successfully')
      closePermissionsDialog()
      loadRoles()
    }
  } catch (error) {
    toast.error('Failed to update role permissions')
    console.error('Error updating role permissions:', error)
  } finally {
    savingPermissions.value = false
  }
}

const deleteRole = async (role) => {
  if (!confirm(`Are you sure you want to delete the role "${role.display_name}"?`)) {
    return
  }

  try {
    const response = await api.delete(`/admin/roles/${role.id}`)
    
    if (response.data.success) {
      toast.success('Role deleted successfully')
      loadRoles()
    }
  } catch (error) {
    toast.error('Failed to delete role')
    console.error('Error deleting role:', error)
  }
}

const isGroupSelected = (groupPermissions) => {
  return groupPermissions.every(permission => selectedPermissions.value.includes(permission.id))
}

const toggleGroupPermissions = (group, groupPermissions) => {
  const allSelected = isGroupSelected(groupPermissions)
  
  if (allSelected) {
    // Remove all group permissions
    groupPermissions.forEach(permission => {
      const index = selectedPermissions.value.indexOf(permission.id)
      if (index > -1) {
        selectedPermissions.value.splice(index, 1)
      }
    })
  } else {
    // Add all group permissions
    groupPermissions.forEach(permission => {
      if (!selectedPermissions.value.includes(permission.id)) {
        selectedPermissions.value.push(permission.id)
      }
    })
  }
}

const closeDialog = () => {
  showCreateDialog.value = false
  editingRole.value = null
  roleData.value = {
    name: '',
    display_name: '',
    description: ''
  }
  nextTick(() => {
    roleForm.value?.resetValidation()
  })
}

const closePermissionsDialog = () => {
  showPermissionsDialog.value = false
  selectedRole.value = null
  selectedPermissions.value = []
  permissionSearch.value = ''
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  loadRoles()
  loadPermissions()
})
</script>
