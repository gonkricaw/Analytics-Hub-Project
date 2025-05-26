<template>
  <div class="content-management">
    <div class="management-header">
      <div class="header-content">
        <h1 class="page-title">
          <i class="fas fa-file-alt" />
          Content Management
        </h1>
        <p class="page-description">
          Manage content items with rich text editing and media support
        </p>
      </div>
      
      <div class="header-actions">
        <button 
          v-if="canCreate"
          class="btn btn-primary"
          @click="showCreateModal = true"
        >
          <i class="fas fa-plus" />
          Add Content
        </button>
        
        <button 
          class="btn btn-secondary"
          :disabled="loading"
          @click="refreshContent"
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
      <!-- Content Grid Display -->
      <div class="content-grid-container">
        <div
          v-if="loading && contents.length === 0"
          class="loading-state"
        >
          <div class="spinner" />
          <span>Loading content...</span>
        </div>
        
        <div
          v-else-if="error"
          class="error-state"
        >
          <i class="fas fa-exclamation-triangle" />
          <span>{{ error }}</span>
          <button
            class="btn btn-sm btn-primary"
            @click="refreshContent"
          >
            Retry
          </button>
        </div>
        
        <div
          v-else-if="contents.length === 0"
          class="empty-state"
        >
          <i class="fas fa-file-alt" />
          <h3>No Content Items</h3>
          <p>Create your first content item to get started.</p>
          <button 
            v-if="canCreate"
            class="btn btn-primary"
            @click="showCreateModal = true"
          >
            <i class="fas fa-plus" />
            Add Content
          </button>
        </div>
        
        <div
          v-else
          class="content-grid"
        >
          <div 
            v-for="content in paginatedContents" 
            :key="content.id"
            class="content-card"
            @click="viewContent(content)"
          >
            <div class="content-card-header">
              <div
                class="content-type-badge"
                :class="`type-${content.type}`"
              >
                <i :class="getContentTypeIcon(content.type)" />
                {{ content.type }}
              </div>
              <div class="content-actions">
                <button 
                  v-if="canEdit"
                  class="btn btn-sm btn-outline"
                  title="Edit"
                  @click.stop="editContent(content)"
                >
                  <i class="fas fa-edit" />
                </button>
                <button 
                  v-if="canCreate"
                  class="btn btn-sm btn-outline"
                  title="Duplicate"
                  @click.stop="duplicateContent(content)"
                >
                  <i class="fas fa-copy" />
                </button>
                <button 
                  v-if="canDelete"
                  class="btn btn-sm btn-danger"
                  title="Delete"
                  @click.stop="deleteContent(content)"
                >
                  <i class="fas fa-trash" />
                </button>
              </div>
            </div>
            
            <div class="content-card-body">
              <h3 class="content-title">
                {{ content.title }}
              </h3>
              <p class="content-slug">
                {{ content.slug }}
              </p>
              <div class="content-meta">
                <span class="created-by">
                  <i class="fas fa-user" />
                  {{ content.created_by?.name || 'System' }}
                </span>
                <span class="created-date">
                  <i class="fas fa-calendar" />
                  {{ formatDate(content.created_at) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div
          v-if="totalPages > 1"
          class="pagination-container"
        >
          <nav class="pagination">
            <button 
              class="page-btn"
              :disabled="currentPage === 1"
              @click="currentPage = 1"
            >
              <i class="fas fa-angle-double-left" />
            </button>
            <button 
              class="page-btn"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              <i class="fas fa-angle-left" />
            </button>
            
            <span class="page-info">
              Page {{ currentPage }} of {{ totalPages }}
            </span>
            
            <button 
              class="page-btn"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              <i class="fas fa-angle-right" />
            </button>
            <button 
              class="page-btn"
              :disabled="currentPage === totalPages"
              @click="currentPage = totalPages"
            >
              <i class="fas fa-angle-double-right" />
            </button>
          </nav>
        </div>
      </div>
    </div>

    <!-- Content Form Modal -->
    <ContentFormModal 
      v-if="showCreateModal || showEditModal"
      :content="selectedContent"
      :show="showCreateModal || showEditModal"
      @close="closeModal"
      @saved="handleContentSaved"
    />

    <!-- Content Preview Modal -->
    <ContentPreviewModal 
      v-if="showPreviewModal"
      :content="selectedContent"
      :show="showPreviewModal"
      @close="showPreviewModal = false"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      v-if="showDeleteModal"
      :show="showDeleteModal"
      title="Delete Content"
      :message="`Are you sure you want to delete '${selectedContent?.title}'? This action cannot be undone.`"
      confirm-text="Delete"
      cancel-text="Cancel"
      variant="danger"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import ConfirmationModal from '@/components/common/ConfirmationModal.vue'
import { useNotification } from '@/composables/useNotification'
import { useAuthStore } from '@/stores/auth'
import { useContentStore } from '@/stores/content'
import { computed, onMounted, ref, watch } from 'vue'
import ContentFormModal from './ContentFormModal.vue'
import ContentPreviewModal from './ContentPreviewModal.vue'

// Store instances
const contentStore = useContentStore()
const authStore = useAuthStore()
const { showNotification } = useNotification()

// Reactive data
const loading = ref(false)
const error = ref(null)
const contents = ref([])
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showPreviewModal = ref(false)
const showDeleteModal = ref(false)
const selectedContent = ref(null)
const currentPage = ref(1)
const itemsPerPage = ref(12)

// Computed properties
const canCreate = computed(() => authStore.hasPermission('content.create'))
const canEdit = computed(() => authStore.hasPermission('content.update'))
const canDelete = computed(() => authStore.hasPermission('content.delete'))

const paginatedContents = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  
  return contents.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(contents.value.length / itemsPerPage.value)
})

// Methods
const refreshContent = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await contentStore.fetchContents()

    contents.value = response.data || []
  } catch (err) {
    error.value = err.message || 'Failed to load content'
    showNotification('Failed to load content', 'error')
  } finally {
    loading.value = false
  }
}

const viewContent = content => {
  selectedContent.value = content
  showPreviewModal.value = true
}

const editContent = content => {
  selectedContent.value = content
  showEditModal.value = true
}

const deleteContent = content => {
  selectedContent.value = content
  showDeleteModal.value = true
}

const duplicateContent = async content => {
  loading.value = true
  
  try {
    await contentStore.duplicateContent(content.id)
    await refreshContent()
    showNotification('Content duplicated successfully', 'success')
  } catch (err) {
    showNotification('Failed to duplicate content', 'error')
  } finally {
    loading.value = false
  }
}

const confirmDelete = async () => {
  if (!selectedContent.value) return
  
  loading.value = true
  
  try {
    await contentStore.deleteContent(selectedContent.value.id)
    await refreshContent()
    showNotification('Content deleted successfully', 'success')
    showDeleteModal.value = false
    selectedContent.value = null
  } catch (err) {
    showNotification('Failed to delete content', 'error')
  } finally {
    loading.value = false
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedContent.value = null
}

const handleContentSaved = () => {
  closeModal()
  refreshContent()
  showNotification('Content saved successfully', 'success')
}

const getContentTypeIcon = type => {
  const icons = {
    'custom': 'fas fa-file-alt',
    'embed_url': 'fas fa-external-link-alt',
    'file': 'fas fa-file',
    'video': 'fas fa-video',
    'image': 'fas fa-image',
  }
  
  return icons[type] || 'fas fa-file'
}

const formatDate = date => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

// Lifecycle
onMounted(() => {
  refreshContent()
})

// Watch for page changes
watch(currentPage, () => {
  // Optionally scroll to top when page changes
  window.scrollTo({ top: 0, behavior: 'smooth' })
})
</script>

<style scoped>
.content-management {
  padding: 2rem;
  background: var(--v-theme-background);
  min-block-size: 100vh;
}

.management-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  border-block-end: 1px solid var(--v-theme-surface-variant);
  margin-block-end: 2rem;
  padding-block-end: 1rem;
}

.header-content {
  flex: 1;
}

.page-title {
  display: flex;
  align-items: center;
  color: var(--v-theme-on-background);
  font-size: 2rem;
  font-weight: 700;
  gap: 0.75rem;
  margin-block: 0 0.5rem;
  margin-inline: 0;
}

.page-title i {
  color: var(--v-theme-primary);
  font-size: 1.8rem;
}

.page-description {
  margin: 0;
  color: var(--v-theme-on-background-variant);
  font-size: 1rem;
}

.header-actions {
  display: flex;
  flex-shrink: 0;
  gap: 0.75rem;
}

.btn {
  display: flex;
  align-items: center;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  gap: 0.5rem;
  padding-block: 0.75rem;
  padding-inline: 1.25rem;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-primary {
  background: var(--v-theme-primary);
  color: var(--v-theme-on-primary);
}

.btn-primary:hover:not(:disabled) {
  background: var(--v-theme-primary-darken-1);
  box-shadow: 0 4px 12px rgba(var(--v-theme-primary-rgb), 0.3);
  transform: translateY(-1px);
}

.btn-secondary {
  background: var(--v-theme-surface-variant);
  color: var(--v-theme-on-surface-variant);
}

.btn-secondary:hover:not(:disabled) {
  background: var(--v-theme-surface-variant-darken-1);
}

.btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.management-content {
  overflow: hidden;
  border-radius: 12px;
  background: var(--v-theme-surface);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 10%);
}

.content-grid-container {
  padding: 2rem;
}

.loading-state,
.error-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--v-theme-on-surface-variant);
  padding-block: 4rem;
  padding-inline: 2rem;
  text-align: center;
}

.loading-state .spinner {
  border: 3px solid var(--v-theme-surface-variant);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  block-size: 40px;
  border-block-start: 3px solid var(--v-theme-primary);
  inline-size: 40px;
  margin-block-end: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state i,
.empty-state i {
  color: var(--v-theme-error);
  font-size: 3rem;
  margin-block-end: 1rem;
}

.empty-state i {
  color: var(--v-theme-on-surface-variant);
}

.empty-state h3 {
  color: var(--v-theme-on-surface);
  font-size: 1.5rem;
  margin-block: 0 0.5rem;
  margin-inline: 0;
}

.empty-state p {
  margin-block: 0 1.5rem;
  margin-inline: 0;
}

.content-grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
}

.content-card {
  overflow: hidden;
  border: 1px solid var(--v-theme-surface-variant);
  border-radius: 12px;
  background: var(--v-theme-background);
  cursor: pointer;
  transition: all 0.2s ease;
}

.content-card:hover {
  border-color: var(--v-theme-primary);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 15%);
  transform: translateY(-2px);
}

.content-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  background: var(--v-theme-surface-variant);
  border-block-end: 1px solid var(--v-theme-surface-variant);
}

.content-type-badge {
  display: flex;
  align-items: center;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 500;
  gap: 0.5rem;
  padding-block: 0.25rem;
  padding-inline: 0.75rem;
  text-transform: uppercase;
}

.content-type-badge.type-custom {
  background: rgba(var(--v-theme-primary-rgb), 0.1);
  color: var(--v-theme-primary);
}

.content-type-badge.type-embed_url {
  background: rgba(var(--v-theme-secondary-rgb), 0.1);
  color: var(--v-theme-secondary);
}

.content-type-badge.type-file {
  background: rgba(var(--v-theme-info-rgb), 0.1);
  color: var(--v-theme-info);
}

.content-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-sm {
  padding: 0.5rem;
  border-radius: 6px;
  font-size: 0.8rem;
}

.btn-outline {
  border: 1px solid var(--v-theme-surface-variant);
  background: transparent;
  color: var(--v-theme-on-surface-variant);
}

.btn-outline:hover {
  background: var(--v-theme-surface-variant);
}

.btn-danger {
  background: var(--v-theme-error);
  color: var(--v-theme-on-error);
}

.btn-danger:hover {
  background: var(--v-theme-error-darken-1);
}

.content-card-body {
  padding: 1.5rem;
}

.content-title {
  color: var(--v-theme-on-background);
  font-size: 1.2rem;
  font-weight: 600;
  line-height: 1.4;
  margin-block: 0 0.5rem;
  margin-inline: 0;
}

.content-slug {
  display: inline-block;
  border-radius: 4px;
  background: var(--v-theme-surface-variant);
  color: var(--v-theme-on-background-variant);
  font-family: monospace;
  font-size: 0.9rem;
  margin-block: 0 1rem;
  margin-inline: 0;
  padding-block: 0.25rem;
  padding-inline: 0.5rem;
}

.content-meta {
  display: flex;
  color: var(--v-theme-on-surface-variant);
  font-size: 0.8rem;
  gap: 1rem;
}

.content-meta span {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.pagination-container {
  display: flex;
  justify-content: center;
  margin-block-start: 2rem;
}

.pagination {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.page-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  border: 1px solid var(--v-theme-surface-variant);
  border-radius: 6px;
  background: var(--v-theme-background);
  block-size: 40px;
  color: var(--v-theme-on-background);
  cursor: pointer;
  inline-size: 40px;
  transition: all 0.2s ease;
}

.page-btn:hover:not(:disabled) {
  border-color: var(--v-theme-primary);
  background: var(--v-theme-primary);
  color: var(--v-theme-on-primary);
}

.page-btn:disabled {
  cursor: not-allowed;
  opacity: 0.4;
}

.page-info {
  color: var(--v-theme-on-surface-variant);
  font-weight: 500;
  padding-block: 0;
  padding-inline: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .content-management {
    padding: 1rem;
  }

  .management-header {
    flex-direction: column;
    gap: 1rem;
  }

  .header-actions {
    justify-content: stretch;
    inline-size: 100%;
  }

  .content-grid {
    grid-template-columns: 1fr;
  }

  .content-meta {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>
