<template>
  <div class="loading-component">
    <div class="loading-content">
      <div class="loading-spinner">
        <VProgressCircular
          :size="size"
          :width="width"
          color="primary"
          indeterminate
        />
      </div>
      <div v-if="showText" class="loading-text">
        <h6 class="text-h6 mt-4">{{ title }}</h6>
        <p v-if="subtitle" class="text-body-2 text-medium-emphasis mt-2">
          {{ subtitle }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  size: {
    type: [Number, String],
    default: 40,
  },
  width: {
    type: [Number, String],
    default: 4,
  },
  title: {
    type: String,
    default: 'Loading...',
  },
  subtitle: {
    type: String,
    default: '',
  },
  showText: {
    type: Boolean,
    default: true,
  },
  fullscreen: {
    type: Boolean,
    default: false,
  },
})
</script>

<style scoped>
.loading-component {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  min-block-size: 200px;
}

.loading-component.fullscreen {
  position: fixed;
  z-index: 9999;
  background: rgba(255, 255, 255, 90%);
  inset: 0;
  min-block-size: 100vh;
}

.loading-content {
  max-inline-size: 300px;
  text-align: center;
}

.loading-spinner {
  margin-block-end: 16px;
}

.loading-text {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 600px) {
  .loading-component {
    padding: 16px;
    min-block-size: 150px;
  }

  .loading-content {
    max-inline-size: 250px;
  }
}
</style>
