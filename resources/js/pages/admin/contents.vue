<template>
  <div class="content-admin-page">
    <ContentManagement />
  </div>
</template>

<script setup>
import { definePageMeta } from '#imports'
import ContentManagement from '@/components/admin/content/ContentManagement.vue'

// Define page metadata
definePageMeta({
  layout: 'blank',
  public: false,
  requiresAuth: true,
  requiresPermissions: ['contents.view'],
})
</script>

<style scoped>
.content-admin-page {
  background: var(--v-theme-background);
  min-block-size: 100vh;
}
</style>
