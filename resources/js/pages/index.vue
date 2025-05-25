<script setup>
definePage({
  meta: {
    layout: 'authenticated',
    requiresAuth: true,
  },
})

// Placeholder data for dashboard widgets
const dashboardStats = ref([
  {
    title: 'Total Users',
    value: '0',
    icon: 'fa-users',
    color: 'primary',
  },
  {
    title: 'Active Sessions',
    value: '0',
    icon: 'fa-chart-line',
    color: 'success',
  },
  {
    title: 'System Status',
    value: 'Online',
    icon: 'fa-server',
    color: 'info',
  },
  {
    title: 'Last Updated',
    value: new Date().toLocaleDateString(),
    icon: 'fa-clock',
    color: 'warning',
  },
])
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
            Welcome to Indonet Analytics Hub
          </h1>
          <p class="text-body-1 text-medium-emphasis">
            Your centralized platform for analytics and data insights
          </p>
        </VCol>
      </VRow>
    </div>

    <!-- Dashboard Stats -->
    <VRow class="mb-6">
      <VCol
        v-for="(stat, index) in dashboardStats"
        :key="index"
        cols="12"
        sm="6"
        md="3"
      >
        <VCard class="dashboard-card">
          <VCardText class="d-flex align-center">
            <VAvatar
              :color="stat.color"
              size="48"
              class="me-4"
            >
              <VIcon
                :icon="stat.icon"
                size="24"
                color="white"
              />
            </VAvatar>
            <div>
              <h4 class="text-h5 font-weight-bold">
                {{ stat.value }}
              </h4>
              <p class="text-body-2 text-medium-emphasis mb-0">
                {{ stat.title }}
              </p>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Placeholder for Future Widgets -->
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex align-center">
            <VIcon
              icon="fa-chart-bar"
              class="me-3"
            />
            Dashboard Widgets
          </VCardTitle>
          <VCardText>
            <VAlert
              type="info"
              variant="tonal"
              class="mb-0"
            >
              <template #prepend>
                <VIcon icon="fa-info-circle" />
              </template>
              <div>
                <strong>Dashboard widgets will be implemented in Phase 5.</strong>
                <br>
                This area will contain dynamic widgets including Jumbotron carousel, 
                Marquee text, analytics charts, and other configurable components.
              </div>
            </VAlert>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style lang="scss" scoped>
.dashboard-page {
  padding: 1.5rem;
}

.page-header {
  padding: 2rem;
  border-radius: 8px;
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-secondary)) 100%);
  color: white;

  h1,
 p {
    color: white !important;
  }
}

.dashboard-card {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;

  &:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 12%);
    transform: translateY(-2px);
  }
}
</style>
