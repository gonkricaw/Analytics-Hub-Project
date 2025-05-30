<template>
  <div class="admin-menu-tree">
    <div 
      v-for="(item, index) in items" 
      :key="item.id"
      class="menu-item-wrapper"
      :class="{ 'dragging': draggingItem?.id === item.id }"
    >
      <div 
        class="menu-item"
        :class="{
          'has-children': item.children && item.children.length > 0,
          'is-expanded': expandedItems.includes(item.id)
        }"
        draggable="true"
        @dragstart="handleDragStart(item, $event)"
        @dragend="handleDragEnd"
        @dragover="handleDragOver"
        @drop="handleDrop(item, $event)"
      >
        <div class="menu-item-content">
          <!-- Drag Handle -->
          <div
            v-if="canReorder"
            class="drag-handle"
          >
            <VIcon
              :icon="getActionIcon('move')"
              size="14"
            />
          </div>
          
          <!-- Expand/Collapse Button -->
          <button 
            v-if="item.children && item.children.length > 0"
            class="expand-btn"
            @click="toggleExpanded(item.id)"
          >
            <VIcon 
              :icon="getNavigationIcon('expand')"
              size="14"
              :class="{ 'rotated': expandedItems.includes(item.id) }"
            />
          </button>
          <div
            v-else
            class="expand-placeholder"
          />
          
          <!-- Menu Icon -->
          <div class="menu-icon">
            <i
              v-if="item.icon"
              :class="item.icon"
            />
            <VIcon
              v-else
              :icon="getStatusIcon('default')"
              size="8"
              class="menu-bullet"
            />
          </div>
          
          <!-- Menu Info -->
          <div class="menu-info">
            <div class="menu-name">
              {{ item.name }}
            </div>
            <div class="menu-details">
              <span class="menu-type">{{ formatType(item.type) }}</span>
              <span
                v-if="item.route_or_url"
                class="menu-url"
              >{{ item.route_or_url }}</span>
              <span
                v-if="item.content"
                class="menu-content"
              >{{ item.content.title }}</span>
              <span
                v-if="item.role_permissions_required"
                class="menu-permissions"
              >
                Requires: {{ item.role_permissions_required }}
              </span>
            </div>
          </div>
          
          <!-- Status Indicators -->
          <div class="menu-status">
            <span
              v-if="item.type === 'url'"
              class="status-badge external"
            >
              <VIcon
                :icon="getNavigationIcon('external')"
                size="12"
              />
              External
            </span>
            <span
              v-if="item.content?.type === 'embed'"
              class="status-badge embed"
            >
              <VIcon
                :icon="getEntityIcon('code')"
                size="12"
              />
              Embed
            </span>
            <span
              v-if="item.role_permissions_required"
              class="status-badge protected"
            >
              <VIcon
                :icon="getStatusIcon('warning')"
                size="12"
              />
              Protected
            </span>
          </div>
          
          <!-- Actions -->
          <div class="menu-actions">
            <button 
              v-if="canEdit"
              class="action-btn edit-btn"
              title="Edit menu item"
              @click="$emit('edit', item)"
            >
              <VIcon
                :icon="getActionIcon('edit')"
                size="14"
              />
            </button>
            
            <button 
              v-if="canDelete"
              class="action-btn delete-btn"
              title="Delete menu item"
              @click="$emit('delete', item)"
            >
              <VIcon
                :icon="getActionIcon('remove')"
                size="14"
              />
            </button>
            
            <button 
              v-if="canEdit"
              class="action-btn add-btn"
              title="Add child menu item"
              @click="$emit('add-child', item)"
            >
              <VIcon
                :icon="getActionIcon('add')"
                size="14"
              />
            </button>
          </div>
        </div>
      </div>
      
      <!-- Children -->
      <Transition name="slide">
        <AdminMenuTree
          v-if="item.children && item.children.length > 0 && expandedItems.includes(item.id)"
          :items="item.children"
          :level="level + 1"
          :can-edit="canEdit"
          :can-delete="canDelete"
          :can-reorder="canReorder"
          class="child-tree"
          @edit="$emit('edit', $event)"
          @delete="$emit('delete', $event)"
          @add-child="$emit('add-child', $event)"
          @reorder="$emit('reorder', $event)"
        />
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { useIconSystem } from '@/composables/useIconSystem'
import { ref } from 'vue'

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
  level: {
    type: Number,
    default: 0,
  },
  canEdit: {
    type: Boolean,
    default: false,
  },
  canDelete: {
    type: Boolean,
    default: false,
  },
  canReorder: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['edit', 'delete', 'add-child', 'reorder'])

const { getNavigationIcon, getActionIcon, getStatusIcon } = useIconSystem()

const expandedItems = ref([])
const draggingItem = ref(null)

const toggleExpanded = itemId => {
  const index = expandedItems.value.indexOf(itemId)
  if (index > -1) {
    expandedItems.value.splice(index, 1)
  } else {
    expandedItems.value.push(itemId)
  }
}

const formatType = type => {
  switch (type) {
  case 'route': return 'Internal Route'
  case 'url': return 'External URL'
  case 'content': return 'Content Page'
  case 'embed': return 'Embedded Content'
  default: return type
  }
}

// Drag and Drop handlers
const handleDragStart = (item, event) => {
  if (!props.canReorder) return
  
  draggingItem.value = item
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', item.id)
}

const handleDragEnd = () => {
  draggingItem.value = null
}

const handleDragOver = event => {
  if (!props.canReorder || !draggingItem.value) return
  
  event.preventDefault()
  event.dataTransfer.dropEffect = 'move'
}

const handleDrop = (targetItem, event) => {
  if (!props.canReorder || !draggingItem.value) return
  
  event.preventDefault()
  
  const draggedId = draggingItem.value.id
  const targetId = targetItem.id
  
  if (draggedId === targetId) return
  
  // Emit reorder event with drag data
  emit('reorder', {
    draggedId,
    targetId,
    action: 'move', // Could be 'before', 'after', 'into' for more complex reordering
  })
  
  draggingItem.value = null
}

// Auto-expand items with children on mount
onMounted(() => {
  props.items.forEach(item => {
    if (item.children && item.children.length > 0) {
      expandedItems.value.push(item.id)
    }
  })
})
</script>

<style scoped>
.admin-menu-tree {
  padding-block: 16px;
  padding-inline: 0;
}

.menu-item-wrapper {
  margin-block-end: 8px;
}

.menu-item-wrapper.dragging {
  opacity: 0.5;
}

.menu-item {
  border: 1px solid #e9ecef;
  border-radius: 8px;
  background: white;
  transition: all 0.2s ease;
}

.menu-item:hover {
  border-color: #007bff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 10%);
}

.menu-item-content {
  display: flex;
  align-items: center;
  padding: 16px;
  gap: 12px;
}

.drag-handle {
  display: flex;
  align-items: center;
  justify-content: center;
  block-size: 20px;
  color: #6c757d;
  cursor: grab;
  font-size: 12px;
  inline-size: 20px;
}

.drag-handle:active {
  cursor: grabbing;
}

.expand-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 4px;
  background: none;
  block-size: 24px;
  color: #6c757d;
  cursor: pointer;
  font-size: 12px;
  inline-size: 24px;
  transition: all 0.2s ease;
}

.expand-btn:hover {
  background-color: #f8f9fa;
  color: #333;
}

.expand-btn .rotated {
  transform: rotate(90deg);
}

.expand-placeholder {
  block-size: 24px;
  inline-size: 24px;
}

.menu-icon {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  background-color: #f8f9fa;
  block-size: 32px;
  color: #6c757d;
  font-size: 16px;
  inline-size: 32px;
}

.menu-bullet {
  font-size: 8px !important;
}

.menu-info {
  flex: 1;
  min-inline-size: 0;
}

.menu-name {
  color: #333;
  font-size: 16px;
  font-weight: 500;
  margin-block-end: 4px;
}

.menu-details {
  display: flex;
  flex-wrap: wrap;
  font-size: 13px;
  gap: 12px;
}

.menu-type {
  color: #007bff;
  font-weight: 500;
}

.menu-url,
.menu-content {
  overflow: hidden;
  color: #6c757d;
  max-inline-size: 200px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.menu-permissions {
  color: #dc3545;
  font-weight: 500;
}

.menu-status {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 500;
  gap: 4px;
  letter-spacing: 0.5px;
  padding-block: 4px;
  padding-inline: 8px;
  text-transform: uppercase;
}

.status-badge.external {
  background-color: #e3f2fd;
  color: #1976d2;
}

.status-badge.embed {
  background-color: #f3e5f5;
  color: #7b1fa2;
}

.status-badge.protected {
  background-color: #fff3e0;
  color: #f57c00;
}

.menu-actions {
  display: flex;
  flex-shrink: 0;
  gap: 4px;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 6px;
  background: none;
  block-size: 32px;
  cursor: pointer;
  font-size: 14px;
  inline-size: 32px;
  transition: all 0.2s ease;
}

.edit-btn {
  color: #007bff;
}

.edit-btn:hover {
  background-color: #e3f2fd;
}

.delete-btn {
  color: #dc3545;
}

.delete-btn:hover {
  background-color: #ffebee;
}

.add-btn {
  color: #28a745;
}

.add-btn:hover {
  background-color: #e8f5e8;
}

.child-tree {
  border-inline-start: 2px solid #f1f3f4;
  margin-inline-start: 40px;
  padding-inline-start: 20px;
}

/* Slide transition */
.slide-enter-active,
.slide-leave-active {
  overflow: hidden;
  transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  max-block-size: 0;
  opacity: 0;
}

.slide-enter-to,
.slide-leave-from {
  max-block-size: 1000px;
  opacity: 1;
}

/* Responsive design */
@media (max-width: 768px) {
  .menu-item-content {
    flex-wrap: wrap;
    gap: 8px;
  }

  .menu-details {
    flex-direction: column;
    gap: 4px;
  }

  .menu-status {
    inline-size: 100%;
  }

  .child-tree {
    margin-inline-start: 20px;
    padding-inline-start: 16px;
  }
}
</style>
