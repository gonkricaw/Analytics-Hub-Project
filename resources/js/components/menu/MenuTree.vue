<template>
  <ul
    class="menu-tree"
    :class="`menu-level-${level}`"
  >
    <li 
      v-for="item in items" 
      :key="item.id"
      class="menu-item"
      :class="{
        'has-children': item.children && item.children.length > 0,
        'is-expanded': expandedItems.includes(item.id),
        'is-active': isActiveItem(item)
      }"
    >
      <div 
        class="menu-item-content"
        :title="item.name"
        @click="handleItemClick(item)"
      >
        <div class="menu-item-icon">
          <i
            v-if="item.icon"
            :class="item.icon"
          />
          <i
            v-else
            class="fas fa-circle menu-bullet"
          />
        </div>
        
        <span class="menu-item-text">{{ item.name }}</span>
        
        <div 
          v-if="item.children && item.children.length > 0"
          class="menu-expand-icon"
          @click.stop="toggleExpanded(item.id)"
        >
          <i
            class="fas fa-chevron-right"
            :class="{ 'rotated': expandedItems.includes(item.id) }"
          />
        </div>
        
        <div
          v-if="item.type === 'url'"
          class="menu-external-icon"
        >
          <i class="fas fa-external-link-alt" />
        </div>
      </div>
      
      <Transition name="slide">
        <MenuTree
          v-if="item.children && item.children.length > 0 && expandedItems.includes(item.id)"
          :items="item.children"
          :level="level + 1"
          @menu-click="(menu) => $emit('menu-click', menu)"
        />
      </Transition>
    </li>
  </ul>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
  level: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits(['menu-click'])

const route = useRoute()
const expandedItems = ref([])

const isActiveItem = item => {
  if (item.type === 'route' && item.route_or_url) {
    return route.path === item.route_or_url
  }
  if (item.type === 'content' && item.content) {
    return route.path === `/content/${item.content.slug}`
  }
  
  return false
}

const handleItemClick = item => {
  // Auto-expand if has children and not expanded
  if (item.children && item.children.length > 0 && !expandedItems.value.includes(item.id)) {
    toggleExpanded(item.id)
  }
  
  emit('menu-click', item)
}

const toggleExpanded = itemId => {
  const index = expandedItems.value.indexOf(itemId)
  if (index > -1) {
    expandedItems.value.splice(index, 1)
  } else {
    expandedItems.value.push(itemId)
  }
}

// Auto-expand active parent items
const expandActiveParents = () => {
  const findActiveParents = (items, parents = []) => {
    for (const item of items) {
      const currentParents = [...parents, item.id]
      
      if (isActiveItem(item)) {
        parents.forEach(parentId => {
          if (!expandedItems.value.includes(parentId)) {
            expandedItems.value.push(parentId)
          }
        })
        
        return true
      }
      
      if (item.children && findActiveParents(item.children, currentParents)) {
        return true
      }
    }
    
    return false
  }
  
  findActiveParents(props.items)
}

// Watch for route changes to expand active parents
watch(() => route.path, () => {
  expandActiveParents()
}, { immediate: true })
</script>

<style scoped>
.menu-tree {
  padding: 0;
  margin: 0;
  list-style: none;
}

.menu-level-0 {
  padding: 0;
}

.menu-level-1 {
  border-inline-start: 2px solid #f1f3f4;
  margin-inline-start: 24px;
  padding-inline-start: 20px;
}

.menu-level-2 {
  border-inline-start: 1px solid #f1f3f4;
  margin-inline-start: 20px;
  padding-inline-start: 16px;
}

.menu-item {
  margin-block-end: 2px;
}

.menu-item-content {
  position: relative;
  display: flex;
  align-items: center;
  border-radius: 6px;
  cursor: pointer;
  gap: 12px;
  padding-block: 10px;
  padding-inline: 16px;
  transition: all 0.2s ease;
}

.menu-item-content:hover {
  background-color: #f8f9fa;
}

.menu-item.is-active .menu-item-content {
  background-color: #e3f2fd;
  color: #1976d2;
  font-weight: 500;
}

.menu-item.is-active .menu-item-content:hover {
  background-color: #bbdefb;
}

.menu-item-icon {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  block-size: 20px;
  color: #6c757d;
  font-size: 14px;
  inline-size: 20px;
}

.menu-item.is-active .menu-item-icon {
  color: #1976d2;
}

.menu-bullet {
  font-size: 6px !important;
}

.menu-item-text {
  overflow: hidden;
  flex: 1;
  color: #333;
  font-size: 14px;
  line-height: 1.4;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.menu-item.is-active .menu-item-text {
  color: #1976d2;
}

.menu-expand-icon {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  block-size: 20px;
  color: #6c757d;
  font-size: 12px;
  inline-size: 20px;
  transition: transform 0.2s ease;
}

.menu-expand-icon:hover {
  color: #333;
}

.menu-expand-icon .rotated {
  transform: rotate(90deg);
}

.menu-external-icon {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  block-size: 16px;
  color: #6c757d;
  font-size: 10px;
  inline-size: 16px;
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
  max-block-size: 500px;
  opacity: 1;
}

/* Responsive design */
@media (max-width: 768px) {
  .menu-item-content {
    padding-block: 12px;
    padding-inline: 16px;
  }

  .menu-item-text {
    font-size: 15px;
  }

  .menu-level-1,
  .menu-level-2 {
    margin-inline-start: 16px;
    padding-inline-start: 16px;
  }
}
</style>
