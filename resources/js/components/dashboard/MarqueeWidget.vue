<template>
  <div class="marquee-widget bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-md p-4 h-full overflow-hidden relative">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-sm font-semibold text-white flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
        </svg>
        Announcements
      </h3>
      <div class="flex items-center space-x-2">
        <button @click="togglePause" 
                class="text-white/80 hover:text-white transition-colors duration-200"
                :title="isPaused ? 'Resume' : 'Pause'">
          <svg v-if="isPaused" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
          </svg>
          <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
        </button>
        <button @click="changeSpeed" 
                class="text-white/80 hover:text-white transition-colors duration-200 text-xs px-2 py-1 rounded border border-white/20"
                :title="`Speed: ${speedLabels[currentSpeedIndex]}`">
          {{ speedLabels[currentSpeedIndex] }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center h-20">
      <div class="animate-spin rounded-full h-6 w-6 border-2 border-white border-t-transparent"></div>
      <span class="ml-2 text-white/80 text-sm">Loading announcements...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center h-20">
      <div class="text-center">
        <svg class="w-6 h-6 mx-auto text-white/60 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-white/80 text-sm">Failed to load announcements</p>
        <button @click="fetchData" class="mt-1 text-white hover:text-white/80 text-xs underline">
          Retry
        </button>
      </div>
    </div>

    <!-- Marquee Container -->
    <div v-else-if="announcements.length > 0" class="relative h-20 overflow-hidden">
      <!-- Gradient Overlays -->
      <div class="absolute left-0 top-0 w-8 h-full bg-gradient-to-r from-blue-600 to-transparent z-10 pointer-events-none"></div>
      <div class="absolute right-0 top-0 w-8 h-full bg-gradient-to-l from-purple-600 to-transparent z-10 pointer-events-none"></div>
      
      <!-- Scrolling Content -->
      <div class="marquee-content flex items-center h-full whitespace-nowrap" 
           :class="{ 'paused': isPaused }"
           :style="{ animationDuration: animationDuration }">
        <div v-for="(announcement, index) in repeatableAnnouncements" 
             :key="`${announcement.id}-${index}`"
             class="flex items-center mr-12 flex-shrink-0">
          <!-- Announcement Icon -->
          <div class="flex-shrink-0 mr-3">
            <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
              <svg v-if="announcement.type === 'info'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <svg v-else-if="announcement.type === 'warning'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
              <svg v-else-if="announcement.type === 'success'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <svg v-else class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
              </svg>
            </div>
          </div>

          <!-- Announcement Content -->
          <div class="flex flex-col">
            <div class="text-white font-medium text-sm">
              {{ announcement.title }}
            </div>
            <div v-if="announcement.message" class="text-white/80 text-xs mt-1">
              {{ announcement.message }}
            </div>
          </div>

          <!-- Separator -->
          <div class="mx-6 w-px h-8 bg-white/20"></div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="flex items-center justify-center h-20">
      <div class="text-center">
        <svg class="w-6 h-6 mx-auto text-white/60 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
        </svg>
        <p class="text-white/80 text-sm">No announcements available</p>
      </div>
    </div>

    <!-- Footer Stats -->
    <div v-if="announcements.length > 0" class="mt-3 pt-3 border-t border-white/20">
      <div class="flex items-center justify-between text-xs text-white/80">
        <span>{{ announcements.length }} announcement{{ announcements.length !== 1 ? 's' : '' }}</span>
        <span>Updated {{ formatLastUpdate() }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { computed, onMounted, onUnmounted, ref } from 'vue'

export default {
  name: 'MarqueeWidget',
  setup() {
    const announcements = ref([])
    const loading = ref(false)
    const error = ref(false)
    const isPaused = ref(false)
    const currentSpeedIndex = ref(1) // Default to normal speed
    const lastUpdated = ref(new Date())
    let refreshInterval = null

    const speeds = [20, 30, 40] // seconds for one full loop
    const speedLabels = ['Fast', 'Normal', 'Slow']

    const animationDuration = computed(() => {
      return `${speeds[currentSpeedIndex.value]}s`
    })

    // Repeat announcements to ensure smooth infinite scrolling
    const repeatableAnnouncements = computed(() => {
      if (announcements.value.length === 0) return []
      
      // Repeat announcements to fill at least 3 cycles for smooth scrolling
      const repeatCount = Math.max(3, Math.ceil(15 / announcements.value.length))
      const repeated = []
      
      for (let i = 0; i < repeatCount; i++) {
        repeated.push(...announcements.value)
      }
      
      return repeated
    })

    const fetchData = async () => {
      loading.value = true
      error.value = false
      
      try {
        const response = await axios.get('/api/dashboard/marquee-text')
        announcements.value = response.data.data || []
        lastUpdated.value = new Date()
        error.value = false
      } catch (err) {
        console.error('Failed to fetch marquee text:', err)
        error.value = true
      } finally {
        loading.value = false
      }
    }

    const togglePause = () => {
      isPaused.value = !isPaused.value
    }

    const changeSpeed = () => {
      currentSpeedIndex.value = (currentSpeedIndex.value + 1) % speeds.length
    }

    const formatLastUpdate = () => {
      const now = new Date()
      const diff = Math.floor((now - lastUpdated.value) / 1000)
      
      if (diff < 60) return 'just now'
      if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
      return `${Math.floor(diff / 3600)}h ago`
    }

    onMounted(() => {
      fetchData()
      // Refresh data every 10 minutes
      refreshInterval = setInterval(fetchData, 10 * 60 * 1000)
    })

    onUnmounted(() => {
      if (refreshInterval) {
        clearInterval(refreshInterval)
      }
    })

    return {
      announcements,
      repeatableAnnouncements,
      loading,
      error,
      isPaused,
      currentSpeedIndex,
      speedLabels,
      animationDuration,
      fetchData,
      togglePause,
      changeSpeed,
      formatLastUpdate
    }
  }
}
</script>

<style scoped>
@keyframes marquee {
  0% {
    transform: translateX(100%);
  }
  100% {
    transform: translateX(-100%);
  }
}

.marquee-content {
  animation: marquee linear infinite;
  animation-play-state: running;
}

.marquee-content.paused {
  animation-play-state: paused;
}

/* Hover effects */
.marquee-widget:hover .marquee-content {
  animation-play-state: paused;
}

.marquee-widget:hover .marquee-content.paused {
  animation-play-state: paused;
}

/* Smooth transitions */
.marquee-content {
  transition: animation-duration 0.3s ease;
}
</style>
