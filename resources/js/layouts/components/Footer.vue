<script setup>
import { useSystemConfigStore } from '@/stores/systemConfig'
import { storeToRefs } from 'pinia'

const systemConfigStore = useSystemConfigStore()
const { appBranding } = storeToRefs(systemConfigStore)
</script>

<template>
  <div class="h-100 d-flex align-center justify-md-space-between justify-center">
    <!-- 👉 Footer: left content -->
    <span class="d-flex align-center text-medium-emphasis">
      <template v-if="appBranding.footerText">
        <span v-html="appBranding.footerText" />
      </template>
      <template v-else>
        &copy;
        {{ new Date().getFullYear() }}
        Made With
        <VIcon
          icon="tabler-heart-filled"
          color="error"
          size="1.25rem"
          class="mx-1"
        />
        By <a
          href="https://pixinvent.com"
          target="_blank"
          rel="noopener noreferrer"
          class="text-primary ms-1"
        >Pixinvent</a>
      </template>
    </span>
    <!-- 👉 Footer: right content -->
    <span class="d-md-flex gap-x-4 text-primary d-none">
      <a
        href="https://themeforest.net/licenses/standard"
        target="noopener noreferrer"
      >License</a>
      <a
        href="https://1.envato.market/pixinvent_portfolio"
        target="noopener noreferrer"
      >More Themes</a>
      <a
        href="https://demos.pixinvent.com/vuexy-vuejs-admin-template/documentation/guide/laravel-integration/folder-structure.html"
        target="noopener noreferrer"
      >Documentation</a>
      <a
        href="https://pixinvent.ticksy.com/"
        target="noopener noreferrer"
      >Support</a>
    </span>
  </div>
</template>
