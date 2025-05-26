<script setup>
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import { Autoplay, Navigation, Pagination } from 'swiper/modules'
import { Swiper, SwiperSlide } from 'swiper/vue'

const props = defineProps({
  slides: {
    type: Array,
    default: () => [],
  },
  settings: {
    type: Object,
    default: () => ({
      autoplay: true,
      interval: 5000,
      indicators: true,
      controls: true,
    }),
  },
})

const modules = [Navigation, Pagination, Autoplay]

const swiperOptions = computed(() => ({
  modules,
  autoplay: props.settings.autoplay ? {
    delay: props.settings.interval,
    disableOnInteraction: false,
  } : false,
  pagination: props.settings.indicators ? {
    clickable: true,
    dynamicBullets: true,
  } : false,
  navigation: props.settings.controls,
  loop: true,
  effect: 'fade',
  fadeEffect: {
    crossFade: true,
  },
}))
</script>

<template>
  <div class="jumbotron-widget">
    <Swiper
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
