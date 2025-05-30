<script setup>
import SystemConfiguration from '@/components/admin/SystemConfiguration.vue'

// Define page metadata
definePage({
  meta: {
    action: 'read',
    subject: 'Admin',
    requiresAuth: true,
    layout: 'admin',
  },
})
</script>

<template>
  <div>
    <SystemConfiguration />
  </div>
</template>
