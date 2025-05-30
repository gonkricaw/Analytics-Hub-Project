<script setup>
import { useIconSystem } from '@/composables/useIconSystem.js'
import { useResponsive } from '@/composables/useResponsive'
import { computed, defineAsyncComponent, onMounted, ref } from 'vue'

// Lazy-loaded components for better performance
const DigitalClockWidget = defineAsyncComponent(() => import('@/components/dashboard/DigitalClockWidget.vue'))
const FrequentContentWidget = defineAsyncComponent(() => import('@/components/dashboard/FrequentContentWidget.vue'))
const FrequentUsersWidget = defineAsyncComponent(() => import('@/components/dashboard/FrequentUsersWidget.vue'))
const JumbotronWidget = defineAsyncComponent(() => import('@/components/dashboard/JumbotronWidget.vue'))
const LoginStatsWidget = defineAsyncComponent(() => import('@/components/dashboard/LoginStatsWidget.vue'))
const MarqueeWidget = defineAsyncComponent(() => import('@/components/dashboard/MarqueeWidget.vue'))
const NotificationsWidget = defineAsyncComponent(() => import('@/components/dashboard/NotificationsWidget.vue'))
const OnlineUsersWidget = defineAsyncComponent(() => import('@/components/dashboard/OnlineUsersWidget.vue'))

definePage({
  meta: {
    layout: 'authenticated',
    requiresAuth: true,
  },
})

// Responsive utilities
const {
  isMobile,
  isTablet,
  isDesktop,
  responsiveClasses,
  layoutConfig,
  getGridColumns,
} = useResponsive()

// Dashboard state
const isLoading = ref(false)
const dashboardError = ref(null)

// Icon system
const { getNavigationIcon, getStatusIcon } = useIconSystem()

// Responsive grid configuration
const gridColumns = computed(() => {
  return {
    jumbotron: 12,
    marquee: 12,
    loginStats: isMobile.value ? 12 : 8,
    onlineUsers: isMobile.value ? 12 : 4,
    notifications: isMobile.value ? 12 : isTablet.value ? 6 : 4,
    frequentContent: isMobile.value ? 12 : isTablet.value ? 6 : 4,
    frequentUsers: isMobile.value ? 12 : isTablet.value ? 12 : 4,
  }
})

onMounted(() => {
  // console.log('Dashboard mounted with all widgets')
})
</script>

<template>
  <div 
    class="dashboard-page"
    :class="responsiveClasses"
  >
    <!-- Page Header -->
    <div class="page-header mb-6">
      <VRow align="center">
        <VCol
          :cols="isMobile ? 12 : 'auto'"
          :order="isMobile ? 2 : 1"
        >
          <div :class="{ 'text-center': isMobile }">
            <h1 class="text-h3 font-weight-bold mb-2">
              <VIcon
                :icon="getNavigationIcon('dashboard')"
                class="me-3"
              />
              Indonet Analytics Hub
            </h1>
            <p class="text-body-1 text-medium-emphasis">
              Comprehensive dashboard for real-time analytics and insights
            </p>
          </div>
        </VCol>
        <VCol
          :cols="isMobile ? 12 : 'auto'"
          :order="isMobile ? 1 : 2"
        >
          <div :class="{ 'text-center': isMobile, 'mb-4': isMobile }">
            <DigitalClockWidget />
          </div>
        </VCol>
      </VRow>
    </div>

    <!-- Marquee Announcements Banner -->
    <VRow class="mb-4">
      <VCol :cols="gridColumns.marquee">
        <MarqueeWidget />
      </VCol>
    </VRow>

    <!-- Main Dashboard Grid -->
    <VRow class="mb-6">
      <!-- Jumbotron Section - Full Width -->
      <VCol :cols="gridColumns.jumbotron">
        <JumbotronWidget />
      </VCol>
    </VRow>

    <!-- Analytics and Stats Row -->
    <VRow class="mb-6">
      <!-- Login Statistics Chart -->
      <VCol :cols="gridColumns.loginStats">
        <LoginStatsWidget />
      </VCol>
      
      <!-- Online Users Widget -->
      <VCol :cols="gridColumns.onlineUsers">
        <OnlineUsersWidget />
      </VCol>
    </VRow>

    <!-- User Activity and Content Analytics Row -->
    <VRow class="mb-6">
      <!-- Frequent Users -->
      <VCol :cols="gridColumns.frequentUsers">
        <FrequentUsersWidget />
      </VCol>
      
      <!-- Frequent Content -->
      <VCol :cols="gridColumns.frequentContent">
        <FrequentContentWidget />
      </VCol>
      
      <!-- Recent Notifications -->
      <VCol :cols="gridColumns.notifications">
        <NotificationsWidget />
      </VCol>
    </VRow>

    <!-- Loading Overlay -->
    <VOverlay
      v-model="isLoading"
      class="d-flex align-center justify-center"
      contained
    >
      <VProgressCircular
        indeterminate
        size="64"
        color="primary"
      />
      <div class="ml-4">
        <h4>Loading Dashboard...</h4>
        <p class="text-medium-emphasis mb-0">
          Fetching latest data
        </p>
      </div>
    </VOverlay>

    <!-- Error Snackbar -->
    <VSnackbar
      v-model="dashboardError"
      color="error"
      timeout="5000"
      top
    >
      <VIcon
        :icon="getStatusIcon('error')"
        class="me-2"
      />
      Failed to load some dashboard components
      <template #actions>
        <VBtn
          variant="text"
          @click="dashboardError = null"
        >
          Close
        </VBtn>
      </template>
    </VSnackbar>
  </div>
</template>

<style lang="scss" scoped>
@import "@/../styles/@core/base/_responsive";

.dashboard-page {
  padding: var(--spacing-md);
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  container-type: inline-size;
  min-block-size: 100vh;

  // Apply responsive classes
  &.is-mobile {
    padding: var(--spacing-sm);
  }

  &.is-desktop {
    padding: var(--spacing-lg);
  }
}

.page-header {
  position: relative;
  overflow: hidden;
  padding: var(--spacing-lg);
  border-radius: 16px;
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-secondary)) 100%);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 12%);
  color: white;
  margin-block-end: 2rem;

  &::before {
    position: absolute;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='36' cy='24' r='1.5'/%3E%3Ccircle cx='18' cy='36' r='1.5'/%3E%3Ccircle cx='6' cy='6' r='1.5'/%3E%3Ccircle cx='54' cy='54' r='1.5'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
    content: "";
    inset: 0;
    opacity: 0.3;
    pointer-events: none;
  }

  h1,
  p {
    position: relative;
    z-index: 1;
    color: white !important;
  }

  h1 {
    text-shadow: 0 2px 4px rgba(0, 0, 0, 20%);

    @include mobile {
      font-size: 1.75rem !important;
      text-align: center;
    }

    @include tablet {
      font-size: 2rem !important;
    }
  }

  p {
    @include mobile {
      font-size: 0.9rem;
      text-align: center;
    }
  }
}

// Enhanced widget container styling
:deep(.v-col) {
  .v-card {
    border: 1px solid rgba(255, 255, 255, 10%);
    border-radius: 12px;
    backdrop-filter: blur(10px);
    block-size: 100%;
    container-type: inline-size;
    transition: all var(--animation-duration-normal) var(--animation-easing-smooth);

    // Enhanced hover effects only for hover-capable devices
    @media (hover: hover) {
      &:hover {
        box-shadow: 0 12px 40px rgba(0, 0, 0, 15%);
        transform: translateY(-4px);
      }
    }

    // Touch-friendly styling for touch devices
    @media (hover: none) {
      &:active {
        transform: scale(0.98);
        transition-duration: var(--animation-duration-fast);
      }
    }

    // Container query adjustments
    @container (max-width: 400px) {
      .v-card-title {
        font-size: 1rem !important;
      }

      .v-card-text {
        padding: 12px !important;
      }
    }
  }
}

// Responsive grid system using modern CSS
.responsive-grid {
  display: grid;
  gap: var(--spacing-md);
  grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr));

  @include mobile {
    gap: var(--spacing-sm);
    grid-template-columns: 1fr;
  }
}

// Enhanced responsive breakpoint styles
@include mobile {
  .dashboard-page {
    padding: var(--spacing-sm);
  }

  .page-header {
    padding: var(--spacing-md);
    margin-block-end: 1rem;

    h1 {
      font-size: 1.5rem !important;
    }

    p {
      font-size: 0.9rem;
    }
  }

  // Stack all widgets on mobile
  :deep(.v-row .v-col) {
    margin-block-end: var(--spacing-sm);
  }
}

@include tablet {
  .dashboard-page {
    padding: var(--spacing-md);
  }

  .page-header {
    padding: var(--spacing-lg);
  }
}

@include desktop {
  .dashboard-page {
    padding: var(--spacing-lg);
  }
}

@include large-desktop {
  .dashboard-page {
    padding: var(--spacing-xl);
    margin-block: 0;
    margin-inline: auto;
    max-inline-size: 1400px;
  }
}

// Loading overlay styling
:deep(.v-overlay) {
  backdrop-filter: blur(4px);

  .v-progress-circular {
    margin-block-end: 1rem;
  }
}

// Snackbar custom styling
:deep(.v-snackbar) {
  .v-snackbar__wrapper {
    border-radius: 8px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 20%);
  }
}

// Animation for widget loading
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

:deep(.v-row .v-col) {
  animation: fadeInUp 0.6s ease-out;
  animation-fill-mode: both;

  &:nth-child(1) { animation-delay: 0.1s; }
  &:nth-child(2) { animation-delay: 0.2s; }
  &:nth-child(3) { animation-delay: 0.3s; }
  &:nth-child(4) { animation-delay: 0.4s; }
  &:nth-child(5) { animation-delay: 0.5s; }
  &:nth-child(6) { animation-delay: 0.6s; }
}

// Dark mode adjustments
:deep(.v-theme--dark) {
  .dashboard-page {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  }

  .page-header {
    box-shadow: 0 8px 32px rgba(0, 0, 0, 30%);
  }
}
</style>
