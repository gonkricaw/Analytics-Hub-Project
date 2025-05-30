<script setup>
import { useSystemConfigStore } from '@/stores/systemConfig.js'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

definePage({
  meta: {
    layout: 'guest',
    public: true,
  },
})

// System configuration store
const systemConfigStore = useSystemConfigStore()

// Computed properties
const appBranding = computed(() => systemConfigStore.appBranding)
const generalSettings = computed(() => systemConfigStore.generalSettings)
const appTitle = computed(() => appBranding.value.name || themeConfig.app.title)

const maintenanceMessage = computed(() => 
  generalSettings.value.maintenanceMessage || 'System is currently under maintenance. Please try again later.',
)
</script>

<template>
  <div class="misc-wrapper">
    <div class="misc-avatar w-100 text-center">
      <VAvatar
        :size="200"
        class="mb-6"
        color="primary"
        variant="tonal"
      >
        <VIcon 
          icon="tabler-tools"
          size="100"
        />
      </VAvatar>
    </div>

    <div class="text-center">
      <h1 class="text-h1 mb-4">
        Under Maintenance
      </h1>
      
      <p
        class="text-body-1 mb-6 mx-auto"
        style="max-width: 500px;"
      >
        {{ maintenanceMessage }}
      </p>

      <div class="d-flex align-center justify-center gap-x-3 mb-6">
        <VNodeRenderer :nodes="themeConfig.app.logo" />
        <h2 class="text-h4">
          {{ appTitle }}
        </h2>
      </div>

      <VBtn
        color="primary"
        @click="$router.push('/login')"
      >
        Back to Login
      </VBtn>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.misc-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 2rem;
}
</style>
