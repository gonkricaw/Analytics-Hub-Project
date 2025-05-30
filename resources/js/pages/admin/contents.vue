<template>
  <div class="content-admin-page">
    <ContentManagement />
  </div>
</template>

<script setup>
import ContentManagement from '@/components/admin/content/ContentManagement.vue'
import { useAuth } from '@/composables/useAuth'
import { onMounted } from 'vue'

// Initialize auth composable for permission checking
const { user, isLoggedIn } = useAuth()

// Check authentication and permissions on mount
onMounted(() => {
  // Authentication and permission checks would typically be handled
  // by Laravel middleware on the backend route level
  console.log('Content admin page loaded for user:', user.value?.name)
})
</script>

<style scoped>
.content-admin-page {
  background: var(--v-theme-background);
  min-block-size: 100vh;
}
</style>
