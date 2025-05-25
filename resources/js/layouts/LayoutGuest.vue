<script setup>
const { injectSkinClasses } = useSkins()

injectSkinClasses()

// SECTION: Loading Indicator
const isFallbackStateActive = ref(false)
const refLoadingIndicator = ref(null)

watch([
  isFallbackStateActive,
  refLoadingIndicator,
], () => {
  if (isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.fallbackHandle()
  if (!isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.resolveHandle()
}, { immediate: true })
// !SECTION
</script>

<template>
  <div class="layout-wrapper layout-nav-type-vertical layout-navbar-sticky">
    <AppLoadingIndicator ref="refLoadingIndicator" />

    <!-- Guest layout content -->
    <div class="layout-page">
      <RouterView v-slot="{ Component }">
        <Suspense
          :timeout="0"
          @fallback="isFallbackStateActive = true"
          @resolve="isFallbackStateActive = false"
        >
          <Component :is="Component" />
        </Suspense>
      </RouterView>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.layout-wrapper {
  background-color: rgb(var(--v-theme-background));
  min-block-size: 100vh;
}

.layout-page {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  min-block-size: 100vh;
}
</style>
