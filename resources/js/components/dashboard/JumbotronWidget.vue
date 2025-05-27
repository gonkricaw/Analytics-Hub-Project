<script setup>
import { useSystemConfigStore } from '@/stores/systemConfig'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import { Autoplay, Navigation, Pagination } from 'swiper/modules'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { onMounted, ref, storeToRefs } from 'vue'

const systemConfigStore = useSystemConfigStore()
const { dashboardConfig } = storeToRefs(systemConfigStore)

const isLoading = ref(true)
const error = ref(null)

const modules = [Navigation, Pagination, Autoplay]

// Default slides for fallback
const defaultSlides = [
  {
    id: 1,
    title: 'Welcome to Indonet Analytics Hub',
    subtitle: 'Comprehensive dashboard for real-time analytics and insights',
    image: '/images/hero/hero-bg-1.jpg',
    button_text: 'Get Started',
    button_link: '#dashboard',
  },
  {
    id: 2,
    title: 'Real-time Data Analytics',
    subtitle: 'Monitor your network performance with advanced analytics tools',
    image: '/images/hero/hero-bg-2.jpg',
    button_text: 'View Analytics',
    button_link: '#analytics',
  },
]

const slides = computed(() => {
  const configSlides = dashboardConfig.value.jumbotron.slides
  return Array.isArray(configSlides) && configSlides.length > 0 ? configSlides : defaultSlides
})

const settings = computed(() => dashboardConfig.value.jumbotron.settings)
const isEnabled = computed(() => dashboardConfig.value.jumbotron.enabled)

const swiperOptions = computed(() => ({
  modules,
  autoplay: settings.value.autoplay ? {
    delay: settings.value.interval,
    disableOnInteraction: false,
  } : false,
  pagination: settings.value.indicators ? {
    clickable: true,
    dynamicBullets: true,
  } : false,
  navigation: settings.value.controls,
  loop: true,
  effect: 'fade',
  fadeEffect: {
    crossFade: true,
  },
}))

onMounted(async () => {
  try {
    // Ensure configurations are loaded
    if (Object.keys(systemConfigStore.configurations).length === 0) {
      await systemConfigStore.fetchConfigurations()
    }
  } catch (err) {
    error.value = 'Failed to load jumbotron configuration'
    console.error('JumbotronWidget: Failed to load configuration:', err)
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="jumbotron-widget">
    <!-- Loading State -->
    <div v-if="isLoading" class="jumbotron-loading">
      <VCard class="h-100 d-flex align-center justify-center" height="400">
        <div class="text-center">
          <VProgressCircular
            indeterminate
            color="primary"
            size="48"
          />
          <p class="text-body-1 mt-4 text-medium-emphasis">
            Loading carousel...
          </p>
        </div>
      </VCard>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="jumbotron-error">
      <VCard class="h-100 d-flex align-center justify-center" height="400">
        <div class="text-center">
          <VIcon
            icon="fa-exclamation-triangle"
            size="48"
            color="error"
            class="mb-4"
          />
          <p class="text-body-1 text-medium-emphasis">
            {{ error }}
          </p>
          <VBtn
            @click="systemConfigStore.refreshCache()"
            color="primary"
            variant="outlined"
            class="mt-4"
          >
            Retry
          </VBtn>
        </div>
      </VCard>
    </div>

    <!-- Disabled State -->
    <div v-else-if="!isEnabled" class="jumbotron-disabled">
      <VCard class="h-100 d-flex align-center justify-center" height="400">
        <div class="text-center">
          <VIcon
            icon="fa-eye-slash"
            size="48"
            color="secondary"
            class="mb-4"
          />
          <p class="text-body-1 text-medium-emphasis">
            Jumbotron carousel is currently disabled
          </p>
        </div>
      </VCard>
    </div>

    <!-- Main Carousel -->
    <Swiper
      v-else-if="slides.length > 0"
      v-bind="swiperOptions"
      class="jumbotron-carousel"
    >
      <SwiperSlide
        v-for="slide in slides"
        :key="slide.id"
        class="jumbotron-slide"
      >
        <div
          class="slide-background"
          :style="{
            backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url(${slide.image})`,
          }"
        >
          <VContainer class="slide-content h-100">
            <VRow
              align="center"
              justify="center"
              class="h-100"
            >
              <VCol
                cols="12"
                md="8"
                lg="6"
                class="text-center"
              >
                <div class="slide-text">
                  <h1 class="text-h2 font-weight-bold text-white mb-4 slide-title">
                    {{ slide.title }}
                  </h1>
                  <p
                    v-if="slide.subtitle"
                    class="text-h6 text-white mb-6 slide-subtitle"
                  >
                    {{ slide.subtitle }}
                  </p>
                  <VBtn
                    v-if="slide.button_text"
                    :href="slide.button_link"
                    color="primary"
                    size="large"
                    rounded="pill"
                    class="slide-button px-8"
                  >
                    {{ slide.button_text }}
                    <VIcon
                      icon="fa-arrow-right"
                      class="ms-2"
                    />
                  </VBtn>
                </div>
              </VCol>
            </VRow>
          </VContainer>
        </div>
      </SwiperSlide>
    </Swiper>

    <!-- No Slides State -->
    <div v-else class="jumbotron-empty">
      <VCard class="h-100 d-flex align-center justify-center" height="400">
        <div class="text-center">
          <VIcon
            icon="fa-images"
            size="48"
            color="secondary"
            class="mb-4"
          />
          <p class="text-body-1 text-medium-emphasis">
            No carousel slides configured
          </p>
        </div>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.jumbotron-widget {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.jumbotron-carousel {
  height: 400px;
  
  :deep(.swiper-pagination) {
    bottom: 20px;
    
    .swiper-pagination-bullet {
      background: rgba(255, 255, 255, 0.7);
      width: 12px;
      height: 12px;
      margin: 0 8px;
      
      &.swiper-pagination-bullet-active {
        background: white;
        transform: scale(1.2);
      }
    }
  }
  
  :deep(.swiper-button-next),
  :deep(.swiper-button-prev) {
    color: white;
    background: rgba(255, 255, 255, 0.2);
    width: 44px;
    height: 44px;
    border-radius: 50%;
    backdrop-filter: blur(10px);
    
    &:after {
      font-size: 18px;
      font-weight: bold;
    }
    
    &:hover {
      background: rgba(255, 255, 255, 0.3);
    }
  }
}

.jumbotron-slide {
  height: 400px;
}

.slide-background {
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  display: flex;
  align-items: center;
  justify-content: center;
}

.slide-content {
  height: 100%;
}

.slide-text {
  animation: slideUp 0.8s ease-out;
}

.slide-title {
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
  animation: fadeInUp 0.8s ease-out;
}

.slide-subtitle {
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
  opacity: 0.9;
  animation: fadeInUp 0.8s ease-out 0.2s both;
}

.slide-button {
  animation: fadeInUp 0.8s ease-out 0.4s both;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
  }
}

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

@keyframes slideUp {
  from {
    transform: translateY(50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

// Responsive adjustments
@media (max-width: 768px) {
  .jumbotron-carousel {
    height: 300px;
  }
  
  .jumbotron-slide {
    height: 300px;
  }
  
  .slide-title {
    font-size: 1.75rem !important;
  }
  
  .slide-subtitle {
    font-size: 1.1rem !important;
  }
}
</style>
