<template>
  <div class="menu-management">
    <div class="management-header">
      <div class="header-content">
        <h1 class="page-title">
          <i class="fas fa-bars" />
          Menu Management
        </h1>
        <p class="page-description">
          Manage hierarchical navigation menus with role-based access control
        </p>
      </div>
      
      <div class="header-actions">
        <button 
          v-if="canCreate"
          class="btn btn-primary"
          @click="showCreateModal = true"
        >
          <i class="fas fa-plus" />
          Add Menu Item
        </button>
        
        <button 
          class="btn btn-secondary"
          :disabled="loading"
          @click="refreshMenus"
        >
          <i
            class="fas fa-sync"
            :class="{ 'fa-spin': loading }"
          />
          Refresh
        </button>
      </div>
    </div>

    <div class="management-content">
      <!-- Menu Tree Display -->
      <div class="menu-tree-container">
        <div
          v-if="loading && menus.length === 0"
          class="loading-state"
        >
          <div class="spinner" />
          <span>Loading menus...</span>
        </div>
        
        <div
          v-else-if="error"
          class="error-state"
        >
          <i class="fas fa-exclamation-triangle" />
          <span>{{ error }}</span>
          <button
            class="btn btn-sm btn-primary"
            @click="refreshMenus"
          >
            Retry
          </button>
        </div>
        
        <div
          v-else-if="hierarchicalMenus.length === 0"
          class="empty-state"
        >
          <i class="fas fa-bars" />
          <h3>No menu items found</h3>
          <p>Create your first menu item to get started</p>
          <button 
            v-if="canCreate"
            class="btn btn-primary"
            @click="showCreateModal = true"
          >
            <i class="fas fa-plus" />
            Add Menu Item
          </button>
        </div>
        
        <AdminMenuTree
          v-else
          :items="hierarchicalMenus"
          :can-edit="canEdit"
          :can-delete="canDelete"
          :can-reorder="canReorder"
          @edit="editMenu"
          @delete="confirmDelete"
          @reorder="handleReorder"
        />
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <MenuFormModal
      v-if="showCreateModal || showEditModal"
      :menu="editingMenu"
      :parent-options="flatMenuOptions"
      :content-options="contentOptions"
      @save="handleSave"
      @cancel="closeModals"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      v-if="showDeleteModal"
      title="Delete Menu Item"
      :message="`Are you sure you want to delete '${deletingMenu?.name}'? This action cannot be undone.`"
      confirm-text="Delete"
      confirm-variant="danger"
      @confirm="handleDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import ConfirmationModal from '@/components/ConfirmationModal.vue'
import { useApi } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import { useContent } from '@/composables/useContent'
import { useMenu } from '@/composables/useMenu'
import { computed, onMounted, ref } from 'vue'
import AdminMenuTree from './AdminMenuTree.vue'
import MenuFormModal from './MenuFormModal.vue'

const { user } = useAuth()
const { apiCall } = useApi()
const { hierarchicalMenus, loading, error, fetchMenus } = useMenu()
const { contents, fetchContents } = useContent()

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const editingMenu = ref(null)
const deletingMenu = ref(null)

// Permissions
const canCreate = computed(() => user.value?.hasPermission('menus.create'))
const canEdit = computed(() => user.value?.hasPermission('menus.update'))
const canDelete = computed(() => user.value?.hasPermission('menus.delete'))
const canReorder = computed(() => user.value?.hasPermission('menus.reorder'))

// Flatten menu structure for parent selection
const flatMenuOptions = computed(() => {
  const options = []
  
  const flattenMenus = (items, level = 0) => {
    items.forEach(item => {
      options.push({
        value: item.id,
        label: '  '.repeat(level) + item.name,
        level,
      })
      
      if (item.children && item.children.length > 0) {
        flattenMenus(item.children, level + 1)
      }
    })
  }
  
  flattenMenus(hierarchicalMenus.value)
  
  return options
})

// Content options for menu linking
const contentOptions = computed(() => 
  contents.value.map(content => ({
    value: content.id,
    label: content.title,
    type: content.type,
  })),
)

const refreshMenus = async () => {
  await Promise.all([
    fetchMenus(),
    fetchContents(),
  ])
}

const editMenu = menu => {
  editingMenu.value = { ...menu }
  showEditModal.value = true
}

const confirmDelete = menu => {
  deletingMenu.value = menu
  showDeleteModal.value = true
}

const handleSave = async formData => {
  try {
    const isEditing = !!editingMenu.value?.id
    const endpoint = isEditing ? `/admin/menus/${editingMenu.value.id}` : '/admin/menus'
    const method = isEditing ? 'PUT' : 'POST'
    
    const response = await apiCall(endpoint, method, formData)
    
    if (response.success) {
      await refreshMenus()
      closeModals()
      
      // Show success notification
      $toast.success(isEditing ? 'Menu updated successfully' : 'Menu created successfully')
    } else {
      throw new Error(response.message || 'Failed to save menu')
    }
  } catch (error) {
    console.error('Error saving menu:', error)
    $toast.error(error.message || 'Failed to save menu')
  }
}

const handleDelete = async () => {
  try {
    const response = await apiCall(`/admin/menus/${deletingMenu.value.id}`, 'DELETE')
    
    if (response.success) {
      await refreshMenus()
      showDeleteModal.value = false
      deletingMenu.value = null
      
      $toast.success('Menu deleted successfully')
    } else {
      throw new Error(response.message || 'Failed to delete menu')
    }
  } catch (error) {
    console.error('Error deleting menu:', error)
    $toast.error(error.message || 'Failed to delete menu')
  }
}

const handleReorder = async reorderData => {
  try {
    const response = await apiCall('/admin/menus/reorder', 'POST', reorderData)
    
    if (response.success) {
      await refreshMenus()
      $toast.success('Menu order updated successfully')
    } else {
      throw new Error(response.message || 'Failed to reorder menus')
    }
  } catch (error) {
    console.error('Error reordering menus:', error)
    $toast.error(error.message || 'Failed to reorder menus')
  }
}

const closeModals = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingMenu.value = null
}

onMounted(() => {
  refreshMenus()
})
</script>

<style scoped>
.menu-management {
  padding: 24px;
  margin-block: 0;
  margin-inline: auto;
  max-inline-size: 1200px;
}

.management-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
  margin-block-end: 32px;
}

.header-content {
  flex: 1;
}

.page-title {
  display: flex;
  align-items: center;
  color: #333;
  font-size: 28px;
  font-weight: 600;
  gap: 12px;
  margin-block: 0 8px;
  margin-inline: 0;
}

.page-title i {
  color: #007bff;
}

.page-description {
  margin: 0;
  color: #6c757d;
  font-size: 16px;
  line-height: 1.5;
}

.header-actions {
  display: flex;
  flex-shrink: 0;
  gap: 12px;
}

.management-content {
  overflow: hidden;
  border-radius: 12px;
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 10%);
}

.menu-tree-container {
  min-block-size: 400px;
}

.loading-state,
.error-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #6c757d;
  padding-block: 60px;
  padding-inline: 20px;
}

.loading-state {
  gap: 16px;
}

.error-state {
  color: #dc3545;
  gap: 12px;
}

.empty-state {
  gap: 16px;
}

.empty-state i {
  color: #dee2e6;
  font-size: 48px;
  opacity: 0.5;
}

.empty-state h3 {
  margin: 0;
  color: #495057;
  font-size: 20px;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

.spinner {
  border: 3px solid #f3f3f4;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  block-size: 32px;
  border-block-start: 3px solid #007bff;
  inline-size: 32px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.btn {
  display: inline-flex;
  align-items: center;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  gap: 8px;
  padding-block: 8px;
  padding-inline: 16px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background-color: #545b62;
}

.btn-sm {
  font-size: 13px;
  padding-block: 6px;
  padding-inline: 12px;
}

/* Responsive design */
@media (max-width: 768px) {
  .menu-management {
    padding: 16px;
  }

  .management-header {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }

  .header-actions {
    justify-content: stretch;
  }

  .header-actions .btn {
    flex: 1;
    justify-content: center;
  }

  .page-title {
    font-size: 24px;
  }
}
</style>
