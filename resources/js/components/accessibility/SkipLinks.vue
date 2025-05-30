<template>
  <div class="skip-links">
    <a 
      href="#main-content" 
      class="skip-link"
      @click="handleSkipToMain"
    >
      Skip to main content
    </a>
    <a 
      href="#navigation" 
      class="skip-link"
      @click="handleSkipToNav"
    >
      Skip to navigation
    </a>
    <a 
      href="#search" 
      class="skip-link"
      @click="handleSkipToSearch"
    >
      Skip to search
    </a>
  </div>
</template>

<script setup>
import { useAccessibility } from '@/composables/useAccessibility'

const { focusMainContent, focusNavigation, focusSearch } = useAccessibility()

const handleSkipToMain = event => {
  event.preventDefault()
  focusMainContent()
}

const handleSkipToNav = event => {
  event.preventDefault()
  focusNavigation()
}

const handleSkipToSearch = event => {
  event.preventDefault()
  focusSearch()
}
</script>

<style lang="scss" scoped>
.skip-links {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 9999;
  pointer-events: none;
}

.skip-link {
  position: absolute;
  top: -100px;
  left: 6px;
  z-index: 10000;
  padding: 8px 16px;
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
  text-decoration: none;
  font-weight: 600;
  border-radius: 4px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  pointer-events: auto;
  
  transition: 
    top var(--animation-duration-standard) var(--animation-easing-standard),
    box-shadow var(--animation-duration-fast) var(--animation-easing-standard);

  &:focus,
  &:focus-visible {
    top: 6px;
    outline: 2px solid rgb(var(--v-theme-on-primary));
    outline-offset: 2px;
  }

  &:hover:focus {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    transform: translateY(-1px);
  }

  // Ensure skip links work in high contrast mode
  @media (prefers-contrast: high) {
    border: 2px solid rgb(var(--v-theme-on-primary));
    
    &:focus {
      background: rgb(var(--v-theme-on-primary));
      color: rgb(var(--v-theme-primary));
      border-color: rgb(var(--v-theme-primary));
    }
  }

  // Support for reduced motion
  @media (prefers-reduced-motion: reduce) {
    transition: none;
    
    &:hover:focus {
      transform: none;
    }
  }
}

// Multiple skip links positioning
.skip-link:nth-child(2) {
  &:focus {
    top: 50px; // Stack below first skip link
  }
}

.skip-link:nth-child(3) {
  &:focus {
    top: 94px; // Stack below second skip link
  }
}

// Dark mode support
.v-theme--dark .skip-link {
  background: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
  border: 1px solid rgba(var(--v-theme-on-surface), 0.3);
  
  &:focus {
    background: rgb(var(--v-theme-primary));
    color: rgb(var(--v-theme-on-primary));
    border-color: rgb(var(--v-theme-primary));
  }
}
</style>
