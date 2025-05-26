<template>
  <div
    v-if="hasError"
    class="error-boundary"
  >
    <VCard
      class="mx-auto"
      max-width="500"
    >
      <VCardTitle class="text-error">
        <VIcon
          icon="mdi-alert-circle"
          class="me-2"
        />
        Something went wrong
      </VCardTitle>
      <VCardText>
        <p class="mb-4">
          An unexpected error occurred. Please try refreshing the page.
        </p>
        <VAlert
          v-if="showDetails && errorMessage"
          type="error"
          variant="outlined"
          class="mb-4"
        >
          <details>
            <summary class="cursor-pointer">
              Error Details
            </summary>
            <pre class="mt-2 text-caption">{{ errorMessage }}</pre>
          </details>
        </VAlert>
      </VCardText>
      <VCardActions>
        <VBtn
          color="primary"
          variant="outlined"
          @click="refresh"
        >
          <VIcon
            icon="mdi-refresh"
            class="me-1"
          />
          Refresh Page
        </VBtn>
        <VBtn
          color="secondary"
          variant="text"
          @click="goHome"
        >
          <VIcon
            icon="mdi-home"
            class="me-1"
          />
          Go Home
        </VBtn>
        <VSpacer />
        <VBtn
          v-if="!showDetails"
          color="grey"
          variant="text"
          size="small"
          @click="showDetails = true"
        >
          Show Details
        </VBtn>
      </VCardActions>
    </VCard>
  </div>
  <slot v-else />
</template>

<script setup>
import { onErrorCaptured, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const hasError = ref(false)
const errorMessage = ref('')
const showDetails = ref(false)

onErrorCaptured((error, instance, info) => {
  console.error('Error captured by boundary:', error)
  console.error('Error info:', info)
  
  hasError.value = true
  errorMessage.value = `${error.message}\n\nStack trace:\n${error.stack}\n\nComponent info: ${info}`
  
  // Report error to monitoring service if available
  if (window.reportError) {
    window.reportError(error, { context: 'ErrorBoundary', info })
  }
  
  return false // Prevent error from propagating
})

const refresh = () => {
  window.location.reload()
}

const goHome = () => {
  hasError.value = false
  router.push('/')
}

// Reset error state when route changes
router.afterEach(() => {
  if (hasError.value) {
    hasError.value = false
    errorMessage.value = ''
    showDetails.value = false
  }
})
</script>

<style scoped>
.error-boundary {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  min-block-size: 400px;
}

.cursor-pointer {
  cursor: pointer;
}

pre {
  font-size: 0.8em;
  max-block-size: 200px;
  overflow-y: auto;
  white-space: pre-wrap;
  word-break: break-word;
}
</style>
