<script setup>
import { useFlashNotifications } from '@/composables/useFlashNotifications.js'
import { computed, onMounted, ref } from 'vue'

definePage({
  meta: {
    requiresAuth: true,
    requiresRole: 'admin',
  },
})

// Flash notifications
const { showSuccess, showError } = useFlashNotifications()

// State
const users = ref([])
const ipBlocks = ref([])
const isLoading = ref(false)
const isInviting = ref(false)
const isUnblocking = ref(false)

// Invitation form
const invitationForm = ref({
  email: '',
  role: 'user',
})

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Search
const searchQuery = ref('')

// Computed
const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value
  
  const query = searchQuery.value.toLowerCase()
  
  return users.value.filter(user => 
    user.name.toLowerCase().includes(query) ||
    user.email.toLowerCase().includes(query),
  )
})

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  
  return filteredUsers.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / itemsPerPage.value)
})

// Form validation
const isInvitationFormValid = computed(() => {
  const emailRegex = /^[^\s@]+@[^\s@][^\s.@]*\.[^\s@]+$/
  
  return invitationForm.value.email && emailRegex.test(invitationForm.value.email)
})

// API functions (placeholder - replace with actual API calls)
const fetchUsers = async () => {
  isLoading.value = true
  try {
    // TODO: Replace with actual API call
    const response = await fetch('/api/admin/users', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json',
      },
    })
    
    if (response.ok) {
      const data = await response.json()

      users.value = data.users || []
    } else {
      throw new Error('Failed to fetch users')
    }
  } catch (error) {
    showError('Failed to load users')
    console.error('Error fetching users:', error)
  } finally {
    isLoading.value = false
  }
}

const fetchIpBlocks = async () => {
  try {
    // TODO: Replace with actual API call
    const response = await fetch('/api/admin/ip-blocks', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json',
      },
    })
    
    if (response.ok) {
      const data = await response.json()

      ipBlocks.value = data.ip_blocks || []
    } else {
      throw new Error('Failed to fetch IP blocks')
    }
  } catch (error) {
    showError('Failed to load IP blocks')
    console.error('Error fetching IP blocks:', error)
  }
}

const inviteUser = async () => {
  if (!isInvitationFormValid.value) return
  
  isInviting.value = true
  try {
    // TODO: Replace with actual API call
    const response = await fetch('/api/admin/invite', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        email: invitationForm.value.email,
        role: invitationForm.value.role,
      }),
    })
    
    if (response.ok) {
      showSuccess('Invitation sent successfully!')
      invitationForm.value = { email: '', role: 'user' }
      await fetchUsers() // Refresh user list
    } else {
      const error = await response.json()
      throw new Error(error.message || 'Failed to send invitation')
    }
  } catch (error) {
    showError(error.message || 'Failed to send invitation')
  } finally {
    isInviting.value = false
  }
}

const unblockIp = async ipAddress => {
  isUnblocking.value = true
  try {
    // TODO: Replace with actual API call
    const response = await fetch(`/api/admin/ip-blocks/${encodeURIComponent(ipAddress)}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json',
      },
    })
    
    if (response.ok) {
      showSuccess(`IP address ${ipAddress} has been unblocked`)
      await fetchIpBlocks() // Refresh IP blocks list
    } else {
      throw new Error('Failed to unblock IP address')
    }
  } catch (error) {
    showError('Failed to unblock IP address')
  } finally {
    isUnblocking.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchUsers()
  fetchIpBlocks()
})
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <h2 class="text-h4 mb-6">
          <VIcon
            icon="tabler-shield-check"
            class="me-3"
          />
          Admin Dashboard
        </h2>
      </VCol>
    </VRow>

    <!-- User Invitation Section -->
    <VRow>
      <VCol cols="12">
        <VCard class="mb-6">
          <VCardTitle>
            <VIcon
              icon="tabler-user-plus"
              class="me-2"
            />
            Invite New User
          </VCardTitle>
          
          <VCardText>
            <VForm @submit.prevent="inviteUser">
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="invitationForm.email"
                    label="Email Address"
                    type="email"
                    placeholder="user@example.com"
                    :rules="[
                      (v) => !!v || 'Email is required',
                      (v) => /.+@.+\..+/.test(v) || 'Email must be valid'
                    ]"
                  />
                </VCol>
                
                <VCol
                  cols="12"
                  md="4"
                >
                  <AppSelect
                    v-model="invitationForm.role"
                    label="Role"
                    :items="[
                      { title: 'User', value: 'user' },
                      { title: 'Admin', value: 'admin' }
                    ]"
                  />
                </VCol>
                
                <VCol
                  cols="12"
                  md="2"
                  class="d-flex align-center"
                >
                  <VBtn
                    type="submit"
                    color="primary"
                    :loading="isInviting"
                    :disabled="!isInvitationFormValid"
                    block
                  >
                    <VIcon
                      icon="tabler-send"
                      start
                    />
                    Send Invite
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Users Management Section -->
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center">
            <span>
              <VIcon
                icon="tabler-users"
                class="me-2"
              />
              User Management
            </span>
            
            <VBtn
              color="primary"
              variant="outlined"
              size="small"
              :loading="isLoading"
              @click="fetchUsers"
            >
              <VIcon
                icon="tabler-refresh"
                start
              />
              Refresh
            </VBtn>
          </VCardTitle>
          
          <VCardText>
            <!-- Search -->
            <VRow class="mb-4">
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="searchQuery"
                  label="Search Users"
                  placeholder="Search by name or email..."
                  prepend-inner-icon="tabler-search"
                  clearable
                />
              </VCol>
            </VRow>

            <!-- Users Table -->
            <VDataTable
              :headers="[
                { title: 'Name', key: 'name' },
                { title: 'Email', key: 'email' },
                { title: 'Role', key: 'role' },
                { title: 'Status', key: 'status' },
                { title: 'Last Login', key: 'last_login_at' },
                { title: 'Created', key: 'created_at' },
              ]"
              :items="paginatedUsers"
              :loading="isLoading"
              item-key="id"
              class="elevation-1"
            >
              <template #item.role="{ item }">
                <VChip
                  :color="item.role === 'admin' ? 'primary' : 'default'"
                  size="small"
                >
                  {{ item.role }}
                </VChip>
              </template>
              
              <template #item.status="{ item }">
                <VChip
                  :color="item.email_verified_at ? 'success' : 'warning'"
                  size="small"
                >
                  {{ item.email_verified_at ? 'Verified' : 'Pending' }}
                </VChip>
              </template>
              
              <template #item.last_login_at="{ item }">
                {{ item.last_login_at ? new Date(item.last_login_at).toLocaleDateString() : 'Never' }}
              </template>
              
              <template #item.created_at="{ item }">
                {{ new Date(item.created_at).toLocaleDateString() }}
              </template>
            </VDataTable>

            <!-- Pagination -->
            <div
              v-if="totalPages > 1"
              class="d-flex justify-center mt-4"
            >
              <VPagination
                v-model="currentPage"
                :length="totalPages"
                :total-visible="7"
              />
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- IP Blocks Management Section -->
    <VRow class="mt-6">
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center">
            <span>
              <VIcon
                icon="tabler-shield-x"
                class="me-2"
              />
              Blocked IP Addresses
            </span>
            
            <VBtn
              color="primary"
              variant="outlined"
              size="small"
              @click="fetchIpBlocks"
            >
              <VIcon
                icon="tabler-refresh"
                start
              />
              Refresh
            </VBtn>
          </VCardTitle>
          
          <VCardText>
            <VDataTable
              :headers="[
                { title: 'IP Address', key: 'ip_address' },
                { title: 'Reason', key: 'reason' },
                { title: 'Failed Attempts', key: 'failed_attempts' },
                { title: 'Blocked At', key: 'created_at' },
                { title: 'Actions', key: 'actions', sortable: false },
              ]"
              :items="ipBlocks"
              item-key="id"
              class="elevation-1"
            >
              <template #item.reason="{ item }">
                <VChip
                  color="error"
                  size="small"
                >
                  {{ item.reason || 'Multiple failed attempts' }}
                </VChip>
              </template>
              
              <template #item.created_at="{ item }">
                {{ new Date(item.created_at).toLocaleDateString() }}
              </template>
              
              <template #item.actions="{ item }">
                <VBtn
                  color="success"
                  variant="outlined"
                  size="small"
                  :loading="isUnblocking"
                  @click="unblockIp(item.ip_address)"
                >
                  <VIcon
                    icon="tabler-shield-check"
                    start
                  />
                  Unblock
                </VBtn>
              </template>
            </VDataTable>
            
            <VAlert
              v-if="ipBlocks.length === 0"
              type="info"
              variant="tonal"
              class="mt-4"
            >
              <VIcon icon="tabler-info-circle" />
              No IP addresses are currently blocked.
            </VAlert>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.v-data-table {
  border-radius: 8px;
}
</style>
