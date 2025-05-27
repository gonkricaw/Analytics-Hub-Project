<script setup>
import DigitalClockWidget from '@/components/dashboard/DigitalClockWidget.vue'
import FrequentContentWidget from '@/components/dashboard/FrequentContentWidget.vue'
import FrequentUsersWidget from '@/components/dashboard/FrequentUsersWidget.vue'
import JumbotronWidget from '@/components/dashboard/JumbotronWidget.vue'
import LoginStatsWidget from '@/components/dashboard/LoginStatsWidget.vue'
import MarqueeWidget from '@/components/dashboard/MarqueeWidget.vue'
import NotificationsWidget from '@/components/dashboard/NotificationsWidget.vue'
import OnlineUsersWidget from '@/components/dashboard/OnlineUsersWidget.vue'
import { onMounted, ref } from 'vue'

definePage({
  meta: {
    layout: 'authenticated',
    requiresAuth: true,
  },
})

// Dashboard state
const isLoading = ref(false)
const dashboardError = ref(null)

onMounted(() => {
  // console.log('Dashboard mounted with all widgets')
})
</script>

<template>
  <div class="dashboard-page">
    <!-- Page Header -->
    <div class="page-header mb-6">
      <VRow align="center">
        <VCol>
          <h1 class="text-h3 font-weight-bold mb-2">
            <VIcon
              icon="fa-home"
              class="me-3"
            />
            Indonet Analytics Hub
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            Comprehensive dashboard for real-time analytics and insights
          </p>
        </VCol>
        <VCol cols="auto">
          <DigitalClockWidget />
        </VCol>
      </VRow>
    </div>

    <!-- Marquee Announcements Banner -->
    <VRow class="mb-4">
      <VCol cols="12">
        <MarqueeWidget />
      </VCol>
    </VRow>

    <!-- Main Dashboard Grid -->
    <VRow class="mb-6">
      <!-- Jumbotron Section - Full Width -->
      <VCol cols="12">
        <JumbotronWidget />
      </VCol>
    </VRow>

    <!-- Analytics and Stats Row -->
    <VRow class="mb-6">
      <!-- Login Statistics Chart -->
      <VCol cols="12" lg="8">
        <LoginStatsWidget />
      </VCol>
      
      <!-- Online Users Widget -->
      <VCol cols="12" lg="4">
        <OnlineUsersWidget />
      </VCol>
    </VRow>

    <!-- User Activity and Content Analytics Row -->
    <VRow class="mb-6">
      <!-- Frequent Users -->
      <VCol cols="12" md="6" lg="4">
        <FrequentUsersWidget />
      </VCol>
      
      <!-- Frequent Content -->
      <VCol cols="12" md="6" lg="4">
        <FrequentContentWidget />
      </VCol>
      
      <!-- Recent Notifications -->
      <VCol cols="12" lg="4">
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
        <p class="text-medium-emphasis mb-0">Fetching latest data</p>
      </div>
    </VOverlay>

    <!-- Error Snackbar -->
    <VSnackbar
      v-model="dashboardError"
      color="error"
      timeout="5000"
      top
    >
      <VIcon icon="fa-exclamation-triangle" class="me-2" />
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
.dashboard-page {
  padding: 1.5rem;
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.page-header {
  padding: 2rem;
  border-radius: 12px;
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-secondary)) 100%);
  color: white;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='36' cy='24' r='1.5'/%3E%3Ccircle cx='18' cy='36' r='1.5'/%3E%3Ccircle cx='6' cy='6' r='1.5'/%3E%3Ccircle cx='54' cy='54' r='1.5'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
    opacity: 0.3;
    pointer-events: none;
  }

  h1,
  p {
    color: white !important;
    position: relative;
    z-index: 1;
  }

  h1 {
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  }
}

// Widget container styling
:deep(.v-col) {
  .v-card {
    height: 100%;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    
    &:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }
  }
}

// Responsive grid adjustments
@media (max-width: 1280px) {
  .dashboard-page {
    padding: 1rem;
  }
  
  .page-header {
    padding: 1.5rem;
    
    h1 {
      font-size: 1.8rem !important;
    }
  }
}

@media (max-width: 960px) {
  .page-header {
    text-align: center;
    
    .v-col:last-child {
      margin-top: 1rem;
    }
  }
}

@media (max-width: 600px) {
  .dashboard-page {
    padding: 0.5rem;
  }
  
  .page-header {
    padding: 1rem;
    margin-bottom: 1rem !important;
    
    h1 {
      font-size: 1.5rem !important;
    }
    
    p {
      font-size: 0.9rem;
    }
  }
}

// Loading overlay styling
:deep(.v-overlay) {
  backdrop-filter: blur(4px);
  
  .v-progress-circular {
    margin-bottom: 1rem;
  }
}

// Snackbar custom styling
:deep(.v-snackbar) {
  .v-snackbar__wrapper {
    border-radius: 8px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
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
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  }
}
</style>
