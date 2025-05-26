<template>
  <div class="menu-admin-page">
    <MenuManagement />
  </div>
</template>

<script setup>
import { definePageMeta } from '#imports'
import MenuManagement from '@/components/admin/menu/MenuManagement.vue'

// Define page metadata
definePageMeta({
  layout: 'blank',
  public: false,
  requiresAuth: true,
  requiresPermissions: ['menus.view'],
})
</script>

<style scoped>
.menu-admin-page {
  background: var(--v-theme-background);
  min-block-size: 100vh;
}
</style>
