import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useDisplay } from 'vuetify'

/**
 * Enhanced responsive utilities composable
 * Provides comprehensive responsive design utilities for Vue components
 */
export function useResponsive() {
  const display = useDisplay()
  
  // Viewport size state
  const viewportWidth = ref(window.innerWidth)
  const viewportHeight = ref(window.innerHeight)
  
  // Breakpoint definitions (matching our SCSS system)
  const breakpoints = {
    xs: 0,
    sm: 600,
    md: 960,
    lg: 1264,
    xl: 1904,
    xxl: 2560,
  }
  
  // Update viewport size
  const updateViewportSize = () => {
    viewportWidth.value = window.innerWidth
    viewportHeight.value = window.innerHeight
  }
  
  // Set up resize listener
  onMounted(() => {
    window.addEventListener('resize', updateViewportSize)
    updateViewportSize()
  })
  
  onUnmounted(() => {
    window.removeEventListener('resize', updateViewportSize)
  })
  
  // Computed responsive states
  const isMobile = computed(() => viewportWidth.value < breakpoints.sm)
  const isTablet = computed(() => viewportWidth.value >= breakpoints.sm && viewportWidth.value < breakpoints.md)
  const isDesktop = computed(() => viewportWidth.value >= breakpoints.md)
  const isLargeDesktop = computed(() => viewportWidth.value >= breakpoints.lg)
  const isExtraLarge = computed(() => viewportWidth.value >= breakpoints.xl)
  
  // Current breakpoint
  const currentBreakpoint = computed(() => {
    const width = viewportWidth.value
    if (width >= breakpoints.xxl) return 'xxl'
    if (width >= breakpoints.xl) return 'xl'
    if (width >= breakpoints.lg) return 'lg'
    if (width >= breakpoints.md) return 'md'
    if (width >= breakpoints.sm) return 'sm'
    
    return 'xs'
  })
  
  // Device orientation
  const isLandscape = computed(() => viewportWidth.value > viewportHeight.value)
  const isPortrait = computed(() => viewportWidth.value <= viewportHeight.value)
  
  // Touch support detection
  const supportsTouch = computed(() => {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0
  })
  
  // Hover support detection
  const supportsHover = computed(() => {
    return window.matchMedia('(hover: hover)').matches
  })
  
  // Container query support
  const getContainerClasses = containerWidth => {
    return {
      'container-xs': containerWidth < 400,
      'container-sm': containerWidth >= 400 && containerWidth < 600,
      'container-md': containerWidth >= 600 && containerWidth < 900,
      'container-lg': containerWidth >= 900 && containerWidth < 1200,
      'container-xl': containerWidth >= 1200,
    }
  }
  
  // Grid column calculations
  const getGridColumns = (minColumnWidth = 280, maxColumns = 4) => {
    const availableWidth = viewportWidth.value - 32 // Account for padding
    const columns = Math.floor(availableWidth / minColumnWidth)
    
    return Math.min(Math.max(columns, 1), maxColumns)
  }
  
  // Responsive spacing calculator
  const getResponsiveSpacing = (baseSpacing = 16) => {
    if (isMobile.value) return baseSpacing * 0.75
    if (isTablet.value) return baseSpacing
    
    return baseSpacing * 1.25
  }
  
  // Responsive font size calculator
  const getResponsiveFontSize = (baseFontSize = 16) => {
    const scale = viewportWidth.value / 1440 // Based on 1440px design width
    const clampedScale = Math.max(0.8, Math.min(1.2, scale))
    
    return Math.round(baseFontSize * clampedScale)
  }
  
  // CSS classes based on current state
  const responsiveClasses = computed(() => ({
    [`breakpoint-${currentBreakpoint.value}`]: true,
    'is-mobile': isMobile.value,
    'is-tablet': isTablet.value,
    'is-desktop': isDesktop.value,
    'is-large-desktop': isLargeDesktop.value,
    'is-extra-large': isExtraLarge.value,
    'is-landscape': isLandscape.value,
    'is-portrait': isPortrait.value,
    'supports-touch': supportsTouch.value,
    'supports-hover': supportsHover.value,
  }))
  
  // Navigation drawer behavior
  const shouldShowDrawer = computed(() => {
    // Show drawer as overlay on mobile/tablet, as sidebar on desktop
    return {
      overlay: isMobile.value || isTablet.value,
      sidebar: isDesktop.value,
      defaultOpen: isDesktop.value,
    }
  })
  
  // Responsive layout configuration
  const layoutConfig = computed(() => ({
    contentPadding: getResponsiveSpacing(24),
    cardSpacing: getResponsiveSpacing(16),
    headerHeight: isMobile.value ? 56 : 64,
    sidebarWidth: isDesktop.value ? 260 : 0,
    gridColumns: getGridColumns(),
    fontSize: {
      base: getResponsiveFontSize(16),
      small: getResponsiveFontSize(14),
      large: getResponsiveFontSize(18),
      heading: getResponsiveFontSize(24),
    },
  }))
  
  // Modal configuration
  const getModalConfig = () => ({
    maxWidth: isMobile.value ? '95vw' : isTablet.value ? '80vw' : '60vw',
    margin: isMobile.value ? '10px' : '20px',
    borderRadius: isMobile.value ? '12px' : '16px',
    fullscreen: isMobile.value,
  })
  
  // Chart configuration
  const getChartConfig = () => ({
    height: isMobile.value ? 200 : isTablet.value ? 250 : 300,
    legend: {
      display: !isMobile.value,
      position: isMobile.value ? 'bottom' : 'right',
    },
    maintainAspectRatio: !isMobile.value,
    scales: {
      x: {
        ticks: {
          maxRotation: isMobile.value ? 45 : 0,
        },
      },
    },
  })
  
  // Table configuration
  const getTableConfig = () => ({
    itemsPerPage: isMobile.value ? 5 : 10,
    showSelect: !isMobile.value,
    showExpand: isMobile.value,
    density: isMobile.value ? 'compact' : 'default',
    mobileBreakpoint: breakpoints.md,
  })
  
  // Form configuration
  const getFormConfig = () => ({
    variant: isMobile.value ? 'filled' : 'outlined',
    density: isMobile.value ? 'compact' : 'default',
    hideDetails: isMobile.value ? 'auto' : false,
    stackColumns: isMobile.value,
  })
  
  return {
    // Viewport state
    viewportWidth: computed(() => viewportWidth.value),
    viewportHeight: computed(() => viewportHeight.value),
    
    // Breakpoint checks
    isMobile,
    isTablet,
    isDesktop,
    isLargeDesktop,
    isExtraLarge,
    currentBreakpoint,
    
    // Orientation
    isLandscape,
    isPortrait,
    
    // Device capabilities
    supportsTouch,
    supportsHover,
    
    // Utility functions
    getContainerClasses,
    getGridColumns,
    getResponsiveSpacing,
    getResponsiveFontSize,
    
    // Configuration objects
    responsiveClasses,
    shouldShowDrawer,
    layoutConfig,
    getModalConfig,
    getChartConfig,
    getTableConfig,
    getFormConfig,
    
    // Vuetify display (for compatibility)
    ...display,
  }
}

/**
 * Container query hook for modern browsers
 */
export function useContainerQuery(containerRef) {
  const containerWidth = ref(0)
  const containerHeight = ref(0)
  
  let resizeObserver = null
  
  onMounted(() => {
    if (containerRef.value && window.ResizeObserver) {
      resizeObserver = new ResizeObserver(entries => {
        for (const entry of entries) {
          containerWidth.value = entry.contentRect.width
          containerHeight.value = entry.contentRect.height
        }
      })
      
      resizeObserver.observe(containerRef.value)
    }
  })
  
  onUnmounted(() => {
    if (resizeObserver) {
      resizeObserver.disconnect()
    }
  })
  
  const containerClasses = computed(() => ({
    'container-xs': containerWidth.value < 400,
    'container-sm': containerWidth.value >= 400 && containerWidth.value < 600,
    'container-md': containerWidth.value >= 600 && containerWidth.value < 900,
    'container-lg': containerWidth.value >= 900 && containerWidth.value < 1200,
    'container-xl': containerWidth.value >= 1200,
  }))
  
  return {
    containerWidth: computed(() => containerWidth.value),
    containerHeight: computed(() => containerHeight.value),
    containerClasses,
  }
}

/**
 * Responsive image hook
 */
export function useResponsiveImage(imageSrc, sizes = {}) {
  const { isMobile, isTablet } = useResponsive()
  
  const responsiveImageSrc = computed(() => {
    if (isMobile.value && sizes.mobile) return sizes.mobile
    if (isTablet.value && sizes.tablet) return sizes.tablet
    if (sizes.desktop) return sizes.desktop
    
    return imageSrc
  })
  
  const responsiveImageSizes = computed(() => {
    if (isMobile.value) return '100vw'
    if (isTablet.value) return '50vw'
    
    return '33vw'
  })
  
  return {
    responsiveImageSrc,
    responsiveImageSizes,
  }
}
