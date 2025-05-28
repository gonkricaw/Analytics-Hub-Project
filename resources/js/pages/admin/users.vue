<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1">
          User Management
        </h4>
        <p class="text-muted">
          Manage system users and their profiles
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
        Add User
      </VBtn>
    </div>

    <!-- Filters and Search -->
    <VCard class="mb-4">
      <VCardText>
        <VRow>
          <VCol
            cols="12"
            md="4"
          >
            <VTextField
              v-model="searchQuery"
              placeholder="Search users..."
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
              v-model="statusFilter"
              :items="statusOptions"
              label="Status"
              clearable
              @update:model-value="loadUsers"
            />
          </VCol>
          <VCol
            cols="12"
            md="2"
          >
            <VSelect
              v-model="perPage"
              :items="perPageOptions"
              label="Items per page"
              @update:model-value="loadUsers"
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
                size="40"
                class="me-3"
              >
                <VImg
                  v-if="item.avatar"
                  :src="item.avatar"
                  :alt="item.name"
                />
                <span
                  v-else
                  class="text-sm"
                >
                  {{ getUserInitials(item.name) }}
                </span>
              </VAvatar>
              <div class="d-flex flex-column">
                <span class="font-weight-medium">{{ item.name }}</span>
                <small class="text-muted">{{ item.email }}</small>
              </div>
            </div>
          </template>

          <!-- Role -->
          <template #item.role="{ item }">
            <VChip
              :color="getRoleColor(item.role)"
              size="small"
              variant="outlined"
            >
              {{ item.role?.display_name || 'No Role' }}
            </VChip>
          </template>

          <!-- Status -->
          <template #item.status="{ item }">
            <VChip
              :color="getStatusColor(item.status)"
              size="small"
            >
              {{ item.status }}
            </VChip>
          </template>

          <!-- Last Login -->
          <template #item.last_login_at="{ item }">
            <span v-if="item.last_login_at">
              {{ formatDate(item.last_login_at) }}
            </span>
            <span
              v-else
              class="text-muted"
            >
              Never
            </span>
          </template>

          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="d-flex gap-2">
              <VBtn
                icon
                size="small"
                color="info"
                variant="text"
                @click="viewUser(item)"
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
                @click="editUser(item)"
              >
                <VIcon icon="tabler-edit" />
                <VTooltip activator="parent">
                  Edit User
                </VTooltip>
              </VBtn>
              <VBtn
                icon
                size="small"
                :color="item.status === 'active' ? 'error' : 'success'"
                variant="text"
                @click="toggleUserStatus(item)"
              >
                <VIcon :icon="item.status === 'active' ? 'tabler-user-off' : 'tabler-user-check'" />
                <VTooltip activator="parent">
                  {{ item.status === 'active' ? 'Deactivate' : 'Activate' }} User
                </VTooltip>
              </VBtn>
              <VBtn
                v-if="!isCurrentUser(item.id)"
                icon
                size="small"
                color="error"
                variant="text"
                @click="deleteUser(item)"
              >
                <VIcon icon="tabler-trash" />
                <VTooltip activator="parent">
                  Delete User
                </VTooltip>
              </VBtn>
            </div>
          </template>
        </VDataTableServer>
      </VCardText>
    </VCard>

    <!-- Create/Edit User Dialog -->
    <VDialog
      v-model="showCreateDialog"
      max-width="600px"
      persistent
    >
      <VCard>
        <VCardTitle>
          <span class="text-h5">
            {{ editingUser ? 'Edit User' : 'Create User' }}
          </span>
        </VCardTitle>

        <VCardText>
          <VForm
            ref="userForm"
            @submit.prevent="saveUser"
          >
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="userData.name"
                  label="Full Name"
                  placeholder="John Doe"
                  :rules="[rules.required]"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model="userData.email"
                  label="Email Address"
                  placeholder="john@example.com"
                  type="email"
                  :rules="[rules.required, rules.email]"
                  required
                />
              </VCol>
              <VCol
                v-if="!editingUser"
                cols="12"
              >
                <VTextField
                  v-model="userData.password"
                  label="Password"
                  type="password"
                  :rules="[rules.required, rules.password]"
                  required
                />
              </VCol>
              <VCol
                v-if="!editingUser"
                cols="12"
              >
                <VTextField
                  v-model="userData.password_confirmation"
                  label="Confirm Password"
                  type="password"
                  :rules="[rules.required, rules.passwordConfirm]"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VSelect
                  v-model="userData.role_id"
                  :items="availableRoles"
                  item-title="display_name"
                  item-value="id"
                  label="User Role"
                  :rules="[rules.required]"
                  required
                />
              </VCol>
              <VCol cols="12">
                <VSelect
                  v-model="userData.status"
                  :items="statusOptions"
                  label="Status"
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
            @click="closeCreateDialog"
          >
            Cancel
          </VBtn>
          <VBtn
            color="primary"
            :loading="saving"
            @click="saveUser"
          >
            {{ editingUser ? 'Update' : 'Create' }} User
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- View User Dialog -->
    <VDialog
      v-model="showViewDialog"
      max-width="600px"
    >
      <VCard v-if="selectedUser">
        <VCardTitle>
          <span class="text-h5">User Details</span>
        </VCardTitle>

        <VCardText>
          <VList>
            <VListItem>
              <VListItemTitle>Avatar</VListItemTitle>
              <VListItemSubtitle>
                <VAvatar
                  size="60"
                  class="mt-2"
                >
                  <VImg
                    v-if="selectedUser.avatar"
                    :src="selectedUser.avatar"
                    :alt="selectedUser.name"
                  />
                  <span
                    v-else
                    class="text-lg"
                  >
                    {{ getUserInitials(selectedUser.name) }}
                  </span>
                </VAvatar>
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Full Name</VListItemTitle>
              <VListItemSubtitle>{{ selectedUser.name }}</VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Email</VListItemTitle>
              <VListItemSubtitle>{{ selectedUser.email }}</VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Role</VListItemTitle>
              <VListItemSubtitle>
                <VChip
                  :color="getRoleColor(selectedUser.role)"
                  size="small"
                  variant="outlined"
                >
                  {{ selectedUser.role?.display_name || 'No Role' }}
                </VChip>
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Status</VListItemTitle>
              <VListItemSubtitle>
                <VChip
                  :color="getStatusColor(selectedUser.status)"
                  size="small"
                >
                  {{ selectedUser.status }}
                </VChip>
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Last Login</VListItemTitle>
              <VListItemSubtitle>
                {{ selectedUser.last_login_at ? formatDate(selectedUser.last_login_at) : 'Never' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle>Created</VListItemTitle>
              <VListItemSubtitle>{{ formatDate(selectedUser.created_at) }}</VListItemSubtitle>
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
import { useAuth } from '@/composables/useAuth'
import { useFlashNotifications } from '@/composables/useFlashNotifications'
import { computed, nextTick, onMounted, ref } from 'vue'

const { api } = useApi()
const { showSuccess, showError } = useFlashNotifications()
const { user: currentUser } = useAuth()

// Data
const users = ref([])
const roles = ref([])
const loading = ref(false)
const saving = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const perPage = ref(15)
const totalItems = ref(0)

// Dialogs
const showCreateDialog = ref(false)
const showViewDialog = ref(false)
const editingUser = ref(null)
const selectedUser = ref(null)

// Form data
const userData = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_id: '',
  status: 'active',
})

// Form reference
const userForm = ref()

// Table configuration
const headers = [
  { title: 'User', key: 'user', sortable: true },
  { title: 'Role', key: 'role', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Last Login', key: 'last_login_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, width: 200 },
]

const perPageOptions = [10, 15, 25, 50, 100]

const statusOptions = [
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
  { title: 'Suspended', value: 'suspended' },
]

// Validation rules
const rules = {
  required: v => !!v || 'This field is required',
  email: v => /.[^\n\r@\u2028\u2029]*@.+\..+/.test(v) || 'Enter a valid email address',
  password: v => v.length >= 8 || 'Password must be at least 8 characters',
  passwordConfirm: v => v === userData.value.password || 'Passwords do not match',
}

// Computed
const availableRoles = computed(() => {
  return roles.value.filter(role => role.name !== 'super_admin' || currentUser.value?.role?.name === 'super_admin')
})

// Methods
const loadUsers = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
      search: searchQuery.value,
      status: statusFilter.value,
    }

    const response = await api.get('/api/admin/users', { params })
    
    users.value = response.data.data
    totalItems.value = response.data.total
    currentPage.value = response.data.current_page
  } catch (error) {
    showError('Failed to load users')
    console.error('Load users error:', error)
  } finally {
    loading.value = false
  }
}

const loadRoles = async () => {
  try {
    const response = await api.get('/api/admin/roles')

    roles.value = response.data.data || response.data
  } catch (error) {
    console.error('Load roles error:', error)
  }
}

const handleSearch = () => {
  currentPage.value = 1
  loadUsers()
}

const handleTableUpdate = ({ page, itemsPerPage }) => {
  currentPage.value = page
  perPage.value = itemsPerPage
  loadUsers()
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  currentPage.value = 1
  loadUsers()
}

const getUserInitials = name => {
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .substring(0, 2)
}

const getRoleColor = role => {
  if (!role) return 'default'
  
  const colors = {
    super_admin: 'error',
    admin: 'warning',
    manager: 'info',
    editor: 'success',
    user: 'primary',
  }
  
  return colors[role.name] || 'default'
}

const getStatusColor = status => {
  const colors = {
    active: 'success',
    inactive: 'warning',
    suspended: 'error',
  }
  
  return colors[status] || 'default'
}

const isCurrentUser = userId => {
  return currentUser.value?.id === userId
}

const formatDate = date => {
  if (!date) return 'N/A'
  
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const viewUser = user => {
  selectedUser.value = user
  showViewDialog.value = true
}

const editUser = user => {
  editingUser.value = user
  userData.value = {
    name: user.name,
    email: user.email,
    role_id: user.role?.id || '',
    status: user.status,
  }
  showCreateDialog.value = true
}

const closeCreateDialog = () => {
  showCreateDialog.value = false
  editingUser.value = null
  userData.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: '',
    status: 'active',
  }
  
  nextTick(() => {
    userForm.value?.resetValidation()
  })
}

const saveUser = async () => {
  const { valid } = await userForm.value.validate()
  if (!valid) return

  saving.value = true
  try {
    if (editingUser.value) {
      // Update user
      await api.put(`/api/admin/users/${editingUser.value.id}`, userData.value)
      showSuccess('User updated successfully')
    } else {
      // Create user
      await api.post('/api/admin/users', userData.value)
      showSuccess('User created successfully')
    }
    
    closeCreateDialog()
    loadUsers()
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to save user'

    showError(message)
    console.error('Save user error:', error)
  } finally {
    saving.value = false
  }
}

const toggleUserStatus = async user => {
  if (isCurrentUser(user.id)) {
    showError('You cannot change your own status')
    
    return
  }

  try {
    const newStatus = user.status === 'active' ? 'inactive' : 'active'

    await api.patch(`/api/admin/users/${user.id}/status`, { status: newStatus })
    
    showSuccess(`User ${newStatus === 'active' ? 'activated' : 'deactivated'} successfully`)
    loadUsers()
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to update user status'

    showError(message)
    console.error('Toggle user status error:', error)
  }
}

const deleteUser = async user => {
  if (isCurrentUser(user.id)) {
    showError('You cannot delete your own account')
    
    return
  }

  if (!confirm(`Are you sure you want to delete user "${user.name}"?`)) {
    return
  }

  try {
    await api.delete(`/api/admin/users/${user.id}`)
    showSuccess('User deleted successfully')
    loadUsers()
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to delete user'

    showError(message)
    console.error('Delete user error:', error)
  }
}

// Lifecycle
onMounted(() => {
  loadUsers()
  loadRoles()
})
</script>

<style scoped>
.text-truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
