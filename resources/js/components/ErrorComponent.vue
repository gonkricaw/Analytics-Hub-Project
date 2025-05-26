<template>
  <div class="error-component">
    <VCard
      class="mx-auto"
      max-width="400"
      variant="outlined"
    >
      <VCardText class="text-center pa-6">
        <VIcon
          icon="mdi-alert-circle-outline"
          size="48"
          color="error"
          class="mb-4"
        />
        <h6 class="text-h6 mb-2">
          {{ title }}
        </h6>
        <p class="text-body-2 text-medium-emphasis mb-4">
          {{ message }}
        </p>
        <div class="d-flex flex-column flex-sm-row gap-2 justify-center">
          <VBtn
            color="primary"
            variant="outlined"
            @click="retry"
          >
            <VIcon
              icon="mdi-refresh"
              class="me-1"
            />
            Try Again
          </VBtn>
          <VBtn
            color="secondary"
            variant="text"
            @click="goBack"
          >
            <VIcon
              icon="mdi-arrow-left"
              class="me-1"
            />
            Go Back
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const props = defineProps({
  title: {
    type: String,
    default: 'Failed to Load',
  },
  message: {
    type: String,
    default: 'This component failed to load. Please try again.',
  },
})

const emit = defineEmits(['retry'])

const router = useRouter()

const retry = () => {
  emit('retry')

  // Force reload the current route
  window.location.reload()
}

const goBack = () => {
  if (window.history.length > 1) {
    router.go(-1)
  } else {
    router.push('/')
  }
}
</script>

<style scoped>
.error-component {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  min-block-size: 300px;
}

@media (max-width: 600px) {
  .error-component {
    padding: 16px;
    min-block-size: 250px;
  }
}
</style>
