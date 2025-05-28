<template>
  <nav
    v-if="menus.length > 0"
    class="menu-container"
  >
    <div class="menu-wrapper">
      <MenuTree 
        :items="menus" 
        :level="0"
        @menu-click="handleMenuClick"
      />
    </div>
  </nav>
  <div
    v-else-if="loading"
    class="menu-loading"
  >
    <div class="spinner" />
    <span>Loading menu...</span>
  </div>
  <div
    v-else
    class="menu-empty"
  >
    <i :class="getNavigationIcon('menu')" />
    <span>No menu items available</span>
  </div>
</template>

<script setup>
import { useIconSystem } from '@/composables/useIconSystem.js'
import { useAuthStore } from '@/stores/authStore'
import { useMenuStore } from '@/stores/menuStore'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import MenuTree from './MenuTree.vue'

const router = useRouter()
const menuStore = useMenuStore()
const authStore = useAuthStore()

// Icon system
const { getNavigationIcon } = useIconSystem()

const loading = ref(false)

const menus = computed(() => menuStore.hierarchicalMenus)

const handleMenuClick = menu => {
  if (menu.type === 'route' && menu.route_or_url) {
    router.push(menu.route_or_url)
  } else if (menu.type === 'url' && menu.route_or_url) {
    window.open(menu.route_or_url, '_blank')
  } else if (menu.type === 'content' && menu.content) {
    router.push(`/content/${menu.content.slug}`)
  } else if (menu.type === 'embed' && menu.content?.embed_url_uuid) {
    const embedUrl = `/app/embed/${menu.content.embed_url_uuid}`

    window.open(embedUrl, '_blank')
  }
}

const loadMenus = async () => {
  if (!authStore.user) return
  
  loading.value = true
  try {
    await menuStore.fetchMenus()
  } catch (error) {
    console.error('Failed to load menus:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadMenus()
})

// Watch for auth changes
watch(() => authStore.user, newUser => {
  if (newUser) {
    loadMenus()
  } else {
    menuStore.clearMenus()
  }
}, { immediate: true })
</script>

<style scoped>
.menu-container {
  overflow: hidden;
  border-radius: 8px;
  background-color: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 10%);
}

.menu-wrapper {
  padding-block: 8px;
  padding-inline: 0;
}

.menu-loading,
.menu-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6c757d;
  font-size: 14px;
  gap: 12px;
  padding-block: 40px;
  padding-inline: 20px;
}

.spinner {
  border: 2px solid #f3f3f3;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  block-size: 20px;
  border-block-start: 2px solid #007bff;
  inline-size: 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.menu-empty i {
  font-size: 24px;
  opacity: 0.5;
}
</style>
