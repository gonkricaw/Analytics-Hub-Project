<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1">User Role Management</h4>
        <p class="text-muted">Assign and manage user roles and permissions</p>
      </div>
    </div>

    <!-- Filters and Search -->
    <VCard class="mb-4">
      <VCardText>
        <VRow>
          <VCol cols="12" md="4">
            <VTextField
              v-model="searchQuery"
              placeholder="Search users..."
              prepend-inner-icon="tabler-search"
              clearable
              @input="handleSearch"
            />
          </VCol>
          <VCol cols="12" md="3">
            <VSelect
              v-model="selectedRoleFilter"
              :items="roleFilterOptions"
              label="Filter by role"
              clearable
              @update:model-value="loadUsers"
            />
          </VCol>
          <VCol cols="12" md="2">
            <VSelect
              v-model="perPage"
              :items="perPageOptions"
              label="Items per page"
              @update:model-value="loadUsers"
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

    <!-- Users Table -->
    <VCard>
      <VCardText>
        <VDataTableServer
          v-model:items-per-page="perPage"
          v-model:page="currentPage"
          :headers="headers"
          :items="users"
          :items-length="totalItems"
          :loading="loading"
          item-value="id"
          @update:options="handleTableUpdate"
        >
          <!-- User Info -->
          <template #item.user="{ item }">
            <div class="d-flex align-center">
              <VAvatar
                :color="getAvatarColor(item.name)"
                class="me-3"
                size="40"
              >
                <span class="text-white font-weight-medium">
                  {{ getInitials(item.name) }}
                </span>
              </VAvatar>
              <div class="d-flex flex-column">
                <span class="font-weight-medium">{{ item.name }}</span>
                <small class="text-muted">{{ item.email }}</small>
              </div>
            </div>
          </template>

          <!-- Current Roles -->
          <template #item.roles="{ item }">
            <div class="d-flex flex-wrap gap-1">
              <VChip
                v-for="role in item.roles"
                :key="role.id"
                :color="getRoleColor(role.name)"
                size="small"
                variant="outlined"
              >
                {{ role.display_name }}
              </VChip>
              <VChip
                v-if="!item.roles.length"
                color="gray"
                size="small"
                variant="outlined"
              >
                No roles assigned
              </VChip>
            </div>
          </template>

          <!-- Status -->
          <template #item.status="{ item }">
            <VChip
              :color="item.email_verified_at ? 'success' : 'warning'"
              size="small"
              variant="outlined"
            >
              {{ item.email_verified_at ? 'Verified' : 'Unverified' }}
            </VChip>
          </template>

          <!-- Last Active -->
          <template #item.last_active_at="{ item }">
            <span v-if="item.last_active_at">
              {{ formatDate(item.last_active_at) }}
            </span>
            <span v-else class="text-muted">Never</span>
          </template>

          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="d-flex gap-2">
              <VBtn
                icon
                size="small"
                color="info"
                variant="text"
                @click="viewUserRoles(item)"
              >
                <VIcon icon="tabler-eye" />
                <VTooltip activator="parent">View Details</VTooltip>
              </VBtn>
              <VBtn
                icon
                size="small"
                color="primary"
                variant="text"
                @click="manageUserRoles(item)"
              >
                <VIcon icon="tabler-settings" />
                <VTooltip activator="parent">Manage Roles</VTooltip>
              </VBtn>
            </div>
          </template>
        </VDataTableServer>
      </VCardText>
    </VCard>

    <!-- Manage User Roles Dialog -->
    <VDialog
      v-model="showManageDialog"
      max-width="600px"
      persistent
    >
      <VCard v-if="selectedUser">
        <VCardTitle>
          <span class="text-h5">Manage Roles - {{ selectedUser.name }}</span>
        </VCardTitle>

        <VCardText>
          <div class="mb-4">
            <h6 class="text-h6 mb-3">Current Roles</h6>
            <div class="d-flex flex-wrap gap-2">
              <VChip
                v-for="role in selectedUser.roles"
                :key="role.id"
                :color="getRoleColor(role.name)"
                variant="outlined"
                closable
                @click:close="removeUserRole(role)"
              >
                {{ role.display_name }}
              </VChip>
              <VChip
                v-if="!selectedUser.roles.length"
                color="gray"
                variant="outlined"
              >
                No roles assigned
              </VChip>
            </div>
          </div>

          <VDivider class="my-4" />

          <div>
            <h6 class="text-h6 mb-3">Available Roles</h6>
            <VSelect
              v-model="roleToAssign"
              :items="availableRoles"
              item-title="display_name"
              item-value="id"
              label="Select role to assign"
              placeholder="Choose a role..."
              variant="outlined"
            />
          </div>

          <div v-if="roleToAssign" class="mt-4">
            <VAlert
              type="info"
              variant="tonal"
              class="mb-0"
            >
              <div class="d-flex align-center">
                <VIcon icon="tabler-info-circle" class="me-2" />
                <div>
                  <strong>{{ selectedRoleInfo?.display_name }}</strong>
                  <br>
                  <small>{{ selectedRoleInfo?.description }}</small>
                  <br>
                  <small class="text-muted">
                    {{ selectedRoleInfo?.permissions_count || 0 }} permissions included
                  </small>
                </div>
              </div>
            </VAlert>
          </div>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="error"
            variant="outlined"
            @click="closeManageDialog"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :disabled="!roleToAssign"
            :loading="assigning"
            @click="assignUserRole"
          >
            Assign Role
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- View User Details Dialog -->
    <VDialog
      v-model="showViewDialog"
      max-width="700px"
    >
      <VCard v-if="selectedUser">
        <VCardTitle>
          <span class="text-h5">User Details - {{ selectedUser.name }}</span>
        </VCardTitle>

        <VCardText>
          <VRow>
            <VCol cols="12" md="6">
              <VList>
                <VListItem>
                  <VListItemTitle>Name</VListItemTitle>
                  <VListItemSubtitle>{{ selectedUser.name }}</VListItemSubtitle>
                </VListItem>
                <VListItem>
                  <VListItemTitle>Email</VListItemTitle>
                  <VListItemSubtitle>{{ selectedUser.email }}</VListItemSubtitle>
                </VListItem>
                <VListItem>
                  <VListItemTitle>Status</VListItemTitle>
                  <VListItemSubtitle>
                    <VChip
                      :color="selectedUser.email_verified_at ? 'success' : 'warning'"
                      size="small"
                      variant="outlined"
                    >
                      {{ selectedUser.email_verified_at ? 'Verified' : 'Unverified' }}
                    </VChip>
                  </VListItemSubtitle>
                </VListItem>
                <VListItem>
                  <VListItemTitle>Last Active</VListItemTitle>
                  <VListItemSubtitle>
                    {{ selectedUser.last_active_at ? formatDate(selectedUser.last_active_at) : 'Never' }}
                  </VListItemSubtitle>
                </VListItem>
                <VListItem>
                  <VListItemTitle>Joined</VListItemTitle>
                  <VListItemSubtitle>{{ formatDate(selectedUser.created_at) }}</VListItemSubtitle>
                </VListItem>
              </VList>
            </VCol>
            <VCol cols="12" md="6">
              <div class="mb-4">
                <h6 class="text-h6 mb-3">Assigned Roles</h6>
                <div class="d-flex flex-column gap-2">
                  <VCard
                    v-for="role in selectedUser.roles"
                    :key="role.id"
                    variant="outlined"
                    class="pa-3"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <div>
                        <div class="font-weight-medium">{{ role.display_name }}</div>
                        <small class="text-muted">{{ role.description }}</small>
                      </div>
                      <VChip
                        :color="getRoleColor(role.name)"
                        size="small"
                        variant="outlined"
                      >
                        {{ role.permissions?.length || 0 }} permissions
                      </VChip>
                    </div>
                  </VCard>
                  <div v-if="!selectedUser.roles.length" class="text-center text-muted py-4">
                    No roles assigned
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>

          <VDivider class="my-4" />

          <div v-if="userPermissions.length">
            <h6 class="text-h6 mb-3">All Permissions ({{ userPermissions.length }})</h6>
            <div class="d-flex flex-wrap gap-2">
              <VChip
                v-for="permission in userPermissions"
                :key="permission.id"
                size="small"
                color="primary"
                variant="outlined"
              >
                {{ permission.display_name }}
              </VChip>
            </div>
          </div>
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
import { computed, onMounted, ref } from 'vue'

const { api } = useApi()
const { toast } = useToast()

// Data
const users = ref([])
const roles = ref([])
const loading = ref(false)
const assigning = ref(false)
const searchQuery = ref('')
const selectedRoleFilter = ref(null)
const currentPage = ref(1)
const perPage = ref(15)
const totalItems = ref(0)

// Dialogs
const showManageDialog = ref(false)
const showViewDialog = ref(false)
const selectedUser = ref(null)
const roleToAssign = ref(null)

// Table configuration
const headers = [
  { title: 'User', key: 'user', sortable: true },
  { title: 'Current Roles', key: 'roles', sortable: false },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Last Active', key: 'last_active_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, width: 100 }
]

const perPageOptions = [10, 15, 25, 50, 100]

// Computed
const roleFilterOptions = computed(() => {
  return roles.value.map(role => ({
    title: role.display_name,
    value: role.id
  }))
})

const availableRoles = computed(() => {
  if (!selectedUser.value) return roles.value
  
  const userRoleIds = selectedUser.value.roles.map(role => role.id)
  return roles.value.filter(role => !userRoleIds.includes(role.id))
})

const selectedRoleInfo = computed(() => {
  return roles.value.find(role => role.id === roleToAssign.value)
})

const userPermissions = computed(() => {
  if (!selectedUser.value?.roles) return []
  
  const permissionsMap = new Map()
  
  selectedUser.value.roles.forEach(role => {
    role.permissions?.forEach(permission => {
      permissionsMap.set(permission.id, permission)
    })
  })
  
  return Array.from(permissionsMap.values()).sort((a, b) => a.display_name.localeCompare(b.display_name))
})

// Methods
const loadUsers = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
      search: searchQuery.value
    }

    if (selectedRoleFilter.value) {
      params.role_id = selectedRoleFilter.value
    }

    const response = await api.get('/admin/user-roles', { params })
    
    if (response.data.success) {
      users.value = response.data.data.data
      totalItems.value = response.data.data.total
    }
  } catch (error) {
    toast.error('Failed to load users')
    console.error('Error loading users:', error)
  } finally {
    loading.value = false
  }
}

const loadRoles = async () => {
  try {
    const response = await api.get('/admin/roles', {
      params: { per_page: 1000 }
    })
    
    if (response.data.success) {
      roles.value = response.data.data.data
    }
  } catch (error) {
    console.error('Error loading roles:', error)
  }
}

const handleTableUpdate = (options) => {
  currentPage.value = options.page
  perPage.value = options.itemsPerPage
  loadUsers()
}

const handleSearch = () => {
  currentPage.value = 1
  loadUsers()
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedRoleFilter.value = null
  currentPage.value = 1
  perPage.value = 15
  loadUsers()
}

const viewUserRoles = async (user) => {
  try {
    const response = await api.get(`/admin/user-roles/users/${user.id}`)
    if (response.data.success) {
      selectedUser.value = response.data.data.user
      showViewDialog.value = true
    }
  } catch (error) {
    toast.error('Failed to load user details')
  }
}

const manageUserRoles = async (user) => {
  try {
    const response = await api.get(`/admin/user-roles/users/${user.id}`)
    if (response.data.success) {
      selectedUser.value = response.data.data.user
      roleToAssign.value = null
      showManageDialog.value = true
    }
  } catch (error) {
    toast.error('Failed to load user roles')
  }
}

const assignUserRole = async () => {
  if (!roleToAssign.value) return

  assigning.value = true
  try {
    const response = await api.post(`/admin/user-roles/users/${selectedUser.value.id}/roles`, {
      role_id: roleToAssign.value
    })

    if (response.data.success) {
      toast.success('Role assigned successfully')
      
      // Update the selected user with new role
      const newRole = roles.value.find(role => role.id === roleToAssign.value)
      selectedUser.value.roles.push(newRole)
      
      // Update the users list
      const userIndex = users.value.findIndex(u => u.id === selectedUser.value.id)
      if (userIndex !== -1) {
        users.value[userIndex].roles.push(newRole)
      }
      
      roleToAssign.value = null
    }
  } catch (error) {
    toast.error('Failed to assign role')
    console.error('Error assigning role:', error)
  } finally {
    assigning.value = false
  }
}

const removeUserRole = async (role) => {
  if (!confirm(`Remove role "${role.display_name}" from ${selectedUser.value.name}?`)) {
    return
  }

  try {
    const response = await api.delete(`/admin/user-roles/users/${selectedUser.value.id}/roles/${role.id}`)

    if (response.data.success) {
      toast.success('Role removed successfully')
      
      // Update the selected user
      selectedUser.value.roles = selectedUser.value.roles.filter(r => r.id !== role.id)
      
      // Update the users list
      const userIndex = users.value.findIndex(u => u.id === selectedUser.value.id)
      if (userIndex !== -1) {
        users.value[userIndex].roles = users.value[userIndex].roles.filter(r => r.id !== role.id)
      }
    }
  } catch (error) {
    toast.error('Failed to remove role')
    console.error('Error removing role:', error)
  }
}

const closeManageDialog = () => {
  showManageDialog.value = false
  selectedUser.value = null
  roleToAssign.value = null
}

// Helper functions
const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const getAvatarColor = (name) => {
  const colors = ['primary', 'secondary', 'success', 'info', 'warning', 'error']
  const index = name.length % colors.length
  return colors[index]
}

const getRoleColor = (roleName) => {
  const roleColors = {
    super_admin: 'error',
    admin: 'warning',
    manager: 'info',
    analyst: 'success',
    viewer: 'secondary',
    user: 'primary'
  }
  return roleColors[roleName] || 'primary'
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
  loadUsers()
  loadRoles()
})
</script>
