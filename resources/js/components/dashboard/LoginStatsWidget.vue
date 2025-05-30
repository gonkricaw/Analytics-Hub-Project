<script setup>
import { useIconSystem } from '@/composables/useIconSystem'
import {
  CategoryScale,
  Chart as ChartJS,
  Filler,
  Legend,
  LinearScale,
  LineElement,
  PointElement,
  Title,
  Tooltip,
} from 'chart.js'
import { Line } from 'vue-chartjs'

const props = defineProps({
  chartData: {
    type: Array,
    default: () => [],
  },
  totalLogins: {
    type: Number,
    default: 0,
  },
  totalUniqueUsers: {
    type: Number,
    default: 0,
  },
})

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
)

const { getStatusIcon, getEntityIcon } = useIconSystem()

const chartDataset = computed(() => ({
  labels: props.chartData.map(item => item.date),
  datasets: [
    {
      label: 'Total Logins',
      data: props.chartData.map(item => item.logins),
      borderColor: 'rgb(var(--v-theme-primary))',
      backgroundColor: 'rgba(var(--v-theme-primary), 0.1)',
      fill: true,
      tension: 0.4,
      pointBackgroundColor: 'rgb(var(--v-theme-primary))',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6,
    },
    {
      label: 'Unique Users',
      data: props.chartData.map(item => item.unique_users),
      borderColor: 'rgb(var(--v-theme-secondary))',
      backgroundColor: 'rgba(var(--v-theme-secondary), 0.1)',
      fill: true,
      tension: 0.4,
      pointBackgroundColor: 'rgb(var(--v-theme-secondary))',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6,
    },
  ],
}))

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: {
        usePointStyle: true,
        padding: 20,
        color: 'rgb(var(--v-theme-on-surface))',
      },
    },
    title: {
      display: false,
    },
    tooltip: {
      mode: 'index',
      intersect: false,
      backgroundColor: 'rgba(var(--v-theme-surface), 0.95)',
      titleColor: 'rgb(var(--v-theme-on-surface))',
      bodyColor: 'rgb(var(--v-theme-on-surface))',
      borderColor: 'rgba(var(--v-theme-outline), 0.2)',
      borderWidth: 1,
      cornerRadius: 8,
      padding: 12,
    },
  },
  scales: {
    x: {
      grid: {
        color: 'rgba(var(--v-theme-outline), 0.1)',
      },
      ticks: {
        color: 'rgb(var(--v-theme-on-surface-variant))',
      },
    },
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(var(--v-theme-outline), 0.1)',
      },
      ticks: {
        color: 'rgb(var(--v-theme-on-surface-variant))',
        stepSize: 1,
      },
    },
  },
  interaction: {
    mode: 'nearest',
    axis: 'x',
    intersect: false,
  },
  elements: {
    point: {
      hoverBackgroundColor: '#fff',
    },
  },
}))
</script>

<template>
  <VCard class="login-stats-widget">
    <VCardTitle class="d-flex align-center justify-space-between">
      <div class="d-flex align-center">
        <VIcon
          :icon="getStatusIcon('info')"
          class="me-3"
          color="primary"
        />
        Login Statistics (Last 15 Days)
      </div>
      <VChip
        color="primary"
        variant="tonal"
        size="small"
      >
        Live Data
      </VChip>
    </VCardTitle>
    
    <VCardText>
      <!-- Summary Stats -->
      <VRow class="mb-4">
        <VCol
          cols="6"
          class="text-center"
        >
          <div class="stat-card">
            <VIcon
              :icon="getStatusIcon('success')"
              color="primary"
              size="20"
              class="mb-2"
            />
            <div class="stat-value text-primary">
              {{ totalLogins.toLocaleString() }}
            </div>
            <div class="stat-label">
              Total Logins
            </div>
          </div>
        </VCol>
        <VCol
          cols="6"
          class="text-center"
        >
          <div class="stat-card">
            <VIcon
              :icon="getEntityIcon('user')"
              color="secondary"
              size="20"
              class="mb-2"
            />
            <div class="stat-value text-secondary">
              {{ totalUniqueUsers.toLocaleString() }}
            </div>
            <div class="stat-label">
              Unique Users
            </div>
          </div>
        </VCol>
      </VRow>
      
      <!-- Chart -->
      <div class="chart-container">
        <Line
          :data="chartDataset"
          :options="chartOptions"
          class="login-chart"
        />
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.login-stats-widget {
  height: 100%;
  transition: all 0.3s ease;
  
  &:hover {
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }
}

.stat-card {
  padding: 1rem;
  border-radius: 8px;
  background: rgba(var(--v-theme-surface-variant), 0.3);
  transition: all 0.3s ease;
  
  &:hover {
    background: rgba(var(--v-theme-surface-variant), 0.5);
    transform: translateY(-2px);
  }
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.875rem;
  color: rgb(var(--v-theme-on-surface-variant));
  margin-top: 0.25rem;
}

.chart-container {
  height: 250px;
  position: relative;
  padding: 1rem 0;
}

.login-chart {
  animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

// Responsive adjustments
@media (max-width: 768px) {
  .chart-container {
    height: 200px;
  }
  
  .stat-value {
    font-size: 1.25rem;
  }
  
  .stat-label {
    font-size: 0.75rem;
  }
}
</style>
