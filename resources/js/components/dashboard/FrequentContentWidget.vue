<template>
  <div class="frequent-content-widget bg-white rounded-lg shadow-md p-6 h-full">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800 flex items-center">
        <svg
          class="w-5 h-5 mr-2 text-purple-600"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
          />
        </svg>
        Popular Content
      </h3>
      <div class="text-xs text-gray-500">
        {{ refreshTime }}
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="space-y-3"
    >
      <div
        v-for="i in 5"
        :key="i"
        class="animate-pulse"
      >
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-gray-200 rounded-full flex-shrink-0" />
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 rounded w-3/4" />
            <div class="h-3 bg-gray-200 rounded w-1/2" />
          </div>
          <div class="w-12 h-6 bg-gray-200 rounded" />
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="text-center py-8"
    >
      <svg
        class="w-12 h-12 mx-auto text-gray-400 mb-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
        />
      </svg>
      <p class="text-gray-500">
        Failed to load content data
      </p>
      <button
        class="mt-2 text-purple-600 hover:text-purple-800 text-sm font-medium"
        @click="fetchData"
      >
        Try Again
      </button>
    </div>

    <!-- Content List -->
    <div
      v-else-if="contentList.length > 0"
      class="space-y-3"
    >
      <div
        v-for="(content, index) in contentList"
        :key="content.id" 
        class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200"
        :class="getRankingClass(index)"
      >
        <!-- Ranking Badge -->
        <div class="flex-shrink-0">
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold"
            :class="getRankingBadgeClass(index)"
          >
            {{ index + 1 }}
          </div>
        </div>

        <!-- Content Info -->
        <div class="flex-1 min-w-0">
          <h4 class="text-sm font-medium text-gray-900 truncate">
            {{ content.title }}
          </h4>
          <p class="text-xs text-gray-500 flex items-center mt-1">
            <svg
              class="w-3 h-3 mr-1"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            {{ content.type }} • {{ formatDate(content.last_visited) }}
          </p>
        </div>

        <!-- Visit Count -->
        <div class="flex-shrink-0 text-right">
          <div class="text-sm font-semibold text-gray-900">
            {{ formatNumber(content.visit_count) }}
          </div>
          <div class="text-xs text-gray-500">
            visits
          </div>
        </div>

        <!-- Trend Indicator -->
        <div class="flex-shrink-0">
          <div
            v-if="content.trend === 'up'"
            class="text-green-500"
          >
            <svg
              class="w-4 h-4"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div
            v-else-if="content.trend === 'down'"
            class="text-red-500"
          >
            <svg
              class="w-4 h-4"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div
            v-else
            class="text-gray-400"
          >
            <svg
              class="w-4 h-4"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="text-center py-8"
    >
      <svg
        class="w-12 h-12 mx-auto text-gray-400 mb-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
        />
      </svg>
      <p class="text-gray-500">
        No content visits yet
      </p>
    </div>

    <!-- Footer -->
    <div
      v-if="contentList.length > 0"
      class="mt-4 pt-4 border-t border-gray-200"
    >
      <button
        class="w-full text-center text-sm text-purple-600 hover:text-purple-800 font-medium"
        @click="viewAllContent"
      >
        View All Content Analytics →
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { computed, onMounted, onUnmounted, ref } from 'vue'

export default {
  name: 'FrequentContentWidget',
  setup() {
    const contentList = ref([])
    const loading = ref(false)
    const error = ref(false)
    const lastUpdated = ref(new Date())
    let refreshInterval = null

    const refreshTime = computed(() => {
      const now = new Date()
      const diff = Math.floor((now - lastUpdated.value) / 1000)
      
      if (diff < 60) return 'Just updated'
      if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
      
      return `${Math.floor(diff / 3600)}h ago`
    })

    const fetchData = async () => {
      loading.value = true
      error.value = false
      
      try {
        const response = await axios.get('/api/dashboard/frequent-content')

        contentList.value = response.data.data || []
        lastUpdated.value = new Date()
        error.value = false
      } catch (err) {
        console.error('Failed to fetch frequent content:', err)
        error.value = true
      } finally {
        loading.value = false
      }
    }

    const getRankingClass = index => {
      if (index === 0) return 'bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-400'
      if (index === 1) return 'bg-gradient-to-r from-gray-50 to-gray-100 border-l-4 border-gray-400'
      if (index === 2) return 'bg-gradient-to-r from-orange-50 to-red-50 border-l-4 border-orange-400'
      
      return ''
    }

    const getRankingBadgeClass = index => {
      if (index === 0) return 'bg-yellow-500 text-white'
      if (index === 1) return 'bg-gray-500 text-white'
      if (index === 2) return 'bg-orange-500 text-white'
      
      return 'bg-purple-100 text-purple-700'
    }

    const formatNumber = num => {
      if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
      if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
      
      return num.toString()
    }

    const formatDate = dateString => {
      const date = new Date(dateString)
      const now = new Date()
      const diff = Math.floor((now - date) / 1000)
      
      if (diff < 60) return 'Just now'
      if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
      if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
      if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`
      
      return date.toLocaleDateString()
    }

    const viewAllContent = () => {
      // Navigate to content analytics page
      console.log('Navigate to content analytics')
    }

    onMounted(() => {
      fetchData()

      // Refresh data every 5 minutes
      refreshInterval = setInterval(fetchData, 5 * 60 * 1000)
    })

    onUnmounted(() => {
      if (refreshInterval) {
        clearInterval(refreshInterval)
      }
    })

    return {
      contentList,
      loading,
      error,
      refreshTime,
      fetchData,
      getRankingClass,
      getRankingBadgeClass,
      formatNumber,
      formatDate,
      viewAllContent,
    }
  },
}
</script>

<style scoped>
.frequent-content-widget {
  min-height: 400px;
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.animate-slide-in {
  animation: slideInRight 0.3s ease-out;
}
</style>
