// Icon System Composable
// Provides standardized icon usage across the application

import { computed } from 'vue'

// Standardized icon mapping for common actions and entities
const ICON_MAP = {
  // Navigation & UI
  menu: 'tabler-menu-2',
  close: 'tabler-x',
  back: 'tabler-arrow-left',
  forward: 'tabler-arrow-right',
  up: 'tabler-chevron-up',
  down: 'tabler-chevron-down',
  left: 'tabler-chevron-left',
  right: 'tabler-chevron-right',
  expand: 'tabler-chevron-down',
  collapse: 'tabler-chevron-up',
  
  // Actions
  add: 'tabler-plus',
  create: 'tabler-plus',
  edit: 'tabler-edit',
  delete: 'tabler-trash',
  remove: 'tabler-x',
  save: 'tabler-device-floppy',
  cancel: 'tabler-x',
  submit: 'tabler-check',
  confirm: 'tabler-check',
  search: 'tabler-search',
  filter: 'tabler-filter',
  sort: 'tabler-arrows-sort',
  refresh: 'tabler-refresh',
  reload: 'tabler-reload',
  download: 'tabler-download',
  upload: 'tabler-upload',
  copy: 'tabler-copy',
  share: 'tabler-share',
  print: 'tabler-printer',
  move: 'tabler-grip-vertical',
  
  // Authentication & User
  login: 'tabler-login',
  logout: 'tabler-logout',
  user: 'tabler-user',
  users: 'tabler-users',
  profile: 'tabler-user-circle',
  account: 'tabler-user-cog',
  password: 'tabler-lock',
  security: 'tabler-shield',
  permissions: 'tabler-key',
  
  // Data & Content
  dashboard: 'tabler-dashboard',
  analytics: 'tabler-chart-line',
  reports: 'tabler-report',
  chart: 'tabler-chart-bar',
  graph: 'tabler-chart-dots',
  table: 'tabler-table',
  list: 'tabler-list',
  grid: 'tabler-grid-dots',
  data: 'tabler-database',
  file: 'tabler-file',
  folder: 'tabler-folder',
  document: 'tabler-file-text',
  image: 'tabler-photo',
  video: 'tabler-video',
  pdf: 'tabler-file-type-pdf',
  spreadsheet: 'tabler-file-type-xls',
  content: 'tabler-file-text',
  
  // System & Admin
  settings: 'tabler-settings',
  configuration: 'tabler-adjustments',
  admin: 'tabler-crown',
  system: 'tabler-device-desktop',
  tools: 'tabler-tools',
  maintenance: 'tabler-tool',
  backup: 'tabler-database-export',
  logs: 'tabler-file-text',
  
  // Status & Feedback
  success: 'tabler-check-circle',
  error: 'tabler-alert-circle',
  warning: 'tabler-alert-triangle',
  info: 'tabler-info-circle',
  loading: 'tabler-loader',
  pending: 'tabler-clock',
  active: 'tabler-circle-check',
  inactive: 'tabler-circle-x',
  default: 'tabler-circle',
  
  // Communication
  email: 'tabler-mail',
  message: 'tabler-message',
  notification: 'tabler-bell',
  chat: 'tabler-message-circle',
  phone: 'tabler-phone',
  
  // Navigation & Links
  home: 'tabler-home',
  link: 'tabler-link',
  external: 'tabler-external-link',
  internal: 'tabler-arrow-right',
  
  // Visibility
  show: 'tabler-eye',
  hide: 'tabler-eye-off',
  visible: 'tabler-eye',
  invisible: 'tabler-eye-off',
  
  // Time & Calendar
  calendar: 'tabler-calendar',
  date: 'tabler-calendar-event',
  time: 'tabler-clock',
  history: 'tabler-history',
  
  // Media & Design
  color: 'tabler-palette',
  theme: 'tabler-paint',
  layout: 'tabler-layout',
  template: 'tabler-template',
  
  // Commerce & Business
  product: 'tabler-package',
  order: 'tabler-shopping-cart',
  payment: 'tabler-credit-card',
  invoice: 'tabler-receipt',
  
  // Social & Sharing
  social: 'tabler-share-2',
  facebook: 'tabler-brand-facebook',
  twitter: 'tabler-brand-twitter',
  linkedin: 'tabler-brand-linkedin',
  instagram: 'tabler-brand-instagram',
  
  // Development & Technical
  code: 'tabler-code',
  api: 'tabler-api',
  database: 'tabler-database',
  server: 'tabler-server',
  cloud: 'tabler-cloud',
  
  // Rating & Review
  star: 'tabler-star',
  heart: 'tabler-heart',
  thumb_up: 'tabler-thumb-up',
  thumb_down: 'tabler-thumb-down',
  trophy: 'tabler-trophy',
  medal: 'tabler-medal',
  award: 'tabler-award',
}

// Icon categories for documentation and organization
const ICON_CATEGORIES = {
  navigation: ['menu', 'close', 'back', 'forward', 'up', 'down', 'left', 'right', 'expand', 'collapse'],
  actions: ['add', 'create', 'edit', 'delete', 'remove', 'save', 'cancel', 'submit', 'confirm', 'search', 'filter', 'sort', 'refresh', 'reload', 'download', 'upload', 'copy', 'share', 'print'],
  auth: ['login', 'logout', 'user', 'users', 'profile', 'account', 'password', 'security', 'permissions'],
  data: ['dashboard', 'analytics', 'reports', 'chart', 'graph', 'table', 'list', 'grid', 'data', 'file', 'folder', 'document', 'image', 'video', 'pdf', 'spreadsheet', 'content', 'info'],
  system: ['settings', 'configuration', 'admin', 'system', 'tools', 'maintenance', 'backup', 'logs'],
  status: ['success', 'error', 'warning', 'info', 'loading', 'pending', 'active', 'inactive'],
  communication: ['email', 'message', 'notification', 'chat', 'phone'],
  links: ['home', 'link', 'external', 'internal'],
  visibility: ['show', 'hide', 'visible', 'invisible'],
  time: ['calendar', 'date', 'time', 'history'],
  media: ['color', 'theme', 'layout', 'template'],
  commerce: ['product', 'order', 'payment', 'invoice'],
  social: ['social', 'facebook', 'twitter', 'linkedin', 'instagram'],
  technical: ['code', 'api', 'database', 'server', 'cloud'],
  rating: ['star', 'heart', 'thumb_up', 'thumb_down'],
}

// Size mapping for consistent icon sizing
const ICON_SIZES = {
  xs: '12',
  sm: '16',
  md: '20',
  lg: '24',
  xl: '28',
  '2xl': '32',
  '3xl': '40',
  '4xl': '48',
}

// Color mapping for semantic colors
const ICON_COLORS = {
  primary: 'primary',
  secondary: 'secondary',
  success: 'success',
  error: 'error',
  warning: 'warning',
  info: 'info',
  disabled: 'disabled',
  muted: 'medium-emphasis',
}

export function useIconSystem() {
  
  /**
   * Get standardized icon for a semantic name
   * @param {string} name - Semantic icon name
   * @param {string} fallback - Fallback icon if name not found
   * @returns {string} Icon class name
   */
  const getIcon = (name, fallback = 'tabler-circle') => {
    if (!name) return fallback
    
    // If it's already a full icon class, return as-is
    if (name.includes('tabler-') || name.includes('fas ') || name.includes('fab ')) {
      return name
    }
    
    // Look up in our standardized map
    return ICON_MAP[name] || fallback
  }

  /**
   * Get icon with semantic color
   * @param {string} name - Icon name
   * @param {string} type - Semantic color type
   * @returns {object} Icon props object
   */
  const getSemanticIcon = (name, type = 'primary') => {
    return {
      icon: getIcon(name),
      color: ICON_COLORS[type] || type,
    }
  }

  /**
   * Get icon with size
   * @param {string} name - Icon name
   * @param {string} size - Size key or pixel value
   * @returns {object} Icon props object
   */
  const getSizedIcon = (name, size = 'md') => {
    return {
      icon: getIcon(name),
      size: ICON_SIZES[size] || size,
    }
  }

  /**
   * Get complete icon props
   * @param {string} name - Icon name
   * @param {object} options - Icon options
   * @returns {object} Complete icon props
   */
  const getIconProps = (name, options = {}) => {
    const {
      size = 'md',
      color = 'primary',
      fallback = 'tabler-circle',
    } = options

    return {
      icon: getIcon(name, fallback),
      size: ICON_SIZES[size] || size,
      color: ICON_COLORS[color] || color,
      ...(options.class && { class: options.class }),
      ...(options.style && { style: options.style }),
    }
  }

  /**
   * Get action icon with appropriate styling
   * @param {string} action - Action type
   * @param {object} options - Styling options
   * @returns {string|object} Icon class string or icon props object
   */
  const getActionIcon = (action, options = {}) => {
    const actionColors = {
      add: 'success',
      create: 'success',
      edit: 'warning',
      delete: 'error',
      remove: 'error',
      save: 'success',
      cancel: 'secondary',
      submit: 'primary',
      confirm: 'success',
    }

    // If no options provided, return just the icon class for simple usage
    if (Object.keys(options).length === 0) {
      return getIcon(action)
    }

    return getIconProps(action, {
      color: actionColors[action] || 'primary',
      ...options,
    })
  }

  /**
   * Get status icon with appropriate color
   * @param {string} status - Status type
   * @param {object} options - Styling options
   * @returns {string|object} Icon class string or icon props object
   */
  const getStatusIcon = (status, options = {}) => {
    const statusMap = {
      success: { icon: 'success', color: 'success' },
      error: { icon: 'error', color: 'error' },
      warning: { icon: 'warning', color: 'warning' },
      info: { icon: 'info', color: 'info' },
      loading: { icon: 'loading', color: 'primary' },
      pending: { icon: 'pending', color: 'warning' },
      active: { icon: 'active', color: 'success' },
      inactive: { icon: 'inactive', color: 'disabled' },
    }

    const statusConfig = statusMap[status] || { icon: status, color: 'primary' }
    
    // If no options provided, return just the icon class for simple usage
    if (Object.keys(options).length === 0) {
      return getIcon(statusConfig.icon)
    }
    
    return getIconProps(statusConfig.icon, {
      color: statusConfig.color,
      ...options,
    })
  }

  /**
   * Get navigation icon with consistent styling
   * @param {string} navigationItem - Navigation item type
   * @param {object} options - Styling options
   * @returns {string|object} Icon class string or icon props object
   */
  const getNavigationIcon = (navigationItem, options = {}) => {
    const navigationMap = {
      home: { icon: 'home', color: 'primary' },
      dashboard: { icon: 'dashboard', color: 'primary' },
      analytics: { icon: 'analytics', color: 'primary' },
      reports: { icon: 'reports', color: 'primary' },
      settings: { icon: 'settings', color: 'primary' },
      profile: { icon: 'profile', color: 'primary' },
      logout: { icon: 'logout', color: 'secondary' },
      menu: { icon: 'menu', color: 'primary' },
      close: { icon: 'close', color: 'primary' },
      back: { icon: 'back', color: 'primary' },
      forward: { icon: 'forward', color: 'primary' },
      up: { icon: 'up', color: 'primary' },
      down: { icon: 'down', color: 'primary' },
    }

    const navConfig = navigationMap[navigationItem] || { icon: navigationItem, color: 'primary' }
    
    // If no options provided, return just the icon class for simple usage
    if (Object.keys(options).length === 0) {
      return getIcon(navConfig.icon)
    }
    
    return getIconProps(navConfig.icon, {
      color: navConfig.color,
      ...options,
    })
  }

  /**
   * Get entity-specific icons (files, content types, etc.)
   * @param {string} entityType - Entity type (file, document, image, video, etc.)
   * @param {object} options - Icon styling options
   * @returns {string|object} Icon class or props object
   */
  const getEntityIcon = (entityType, options = {}) => {
    // Map entity types to their appropriate icons from ICON_MAP
    const entityMap = {
      // File types
      file: ICON_MAP.file,
      document: ICON_MAP.document,
      image: ICON_MAP.image,
      video: ICON_MAP.video,
      pdf: ICON_MAP.pdf,
      spreadsheet: ICON_MAP.spreadsheet,
      
      // Content types
      content: ICON_MAP.content,
      folder: ICON_MAP.folder,
      
      // Business entities
      product: ICON_MAP.product,
      order: ICON_MAP.order,
      invoice: ICON_MAP.invoice,
      
      // System entities
      user: ICON_MAP.user,
      users: ICON_MAP.users,
      admin: ICON_MAP.admin,
      
      // Data entities
      data: ICON_MAP.data,
      database: ICON_MAP.database,
      report: ICON_MAP.reports,
      analytics: ICON_MAP.analytics,
    }
    
    const entityConfig = {
      icon: entityMap[entityType] || ICON_MAP.file, // Default to file icon
      color: 'secondary',
    }
    
    // If no options provided, return just the icon class for simple usage
    if (Object.keys(options).length === 0) {
      return getIcon(entityConfig.icon)
    }
    
    return getIconProps(entityConfig.icon, {
      color: entityConfig.color,
      ...options,
    })
  }

  /**
   * Get all available icons for documentation
   */
  const getAvailableIcons = () => {
    return {
      icons: ICON_MAP,
      categories: ICON_CATEGORIES,
      sizes: ICON_SIZES,
      colors: ICON_COLORS,
    }
  }

  /**
   * Validate icon usage and suggest improvements
   * @param {string} icon - Current icon being used
   * @param {string} context - Context where icon is used
   * @returns {object} Validation result with suggestions
   */
  const validateIcon = (icon, context = '') => {
    const suggestions = []
    const warnings = []

    // Check if using Font Awesome when Tabler equivalent exists
    if (icon && icon.includes('fas ')) {
      const fontAwesomeName = icon.replace('fas fa-', '')

      const semanticEquivalent = Object.keys(ICON_MAP).find(key => 
        ICON_MAP[key].includes(fontAwesomeName) || key === fontAwesomeName,
      )
      
      if (semanticEquivalent) {
        suggestions.push(`Consider using semantic name "${semanticEquivalent}" instead of "${icon}"`)
      }
    }

    // Check for common semantic names that could be used
    if (context) {
      const contextLower = context.toLowerCase()

      const potentialSemantics = Object.keys(ICON_MAP).filter(key => 
        contextLower.includes(key) || key.includes(contextLower),
      )
      
      if (potentialSemantics.length > 0) {
        suggestions.push(`Based on context "${context}", consider: ${potentialSemantics.join(', ')}`)
      }
    }

    // Check for outdated Font Awesome classes
    if (icon && (icon.includes('fa-') && !icon.includes('tabler-'))) {
      warnings.push('Using Font Awesome icons. Consider migrating to Tabler Icons for consistency.')
    }

    return {
      isValid: warnings.length === 0,
      suggestions,
      warnings,
      recommendedIcon: suggestions.length > 0 ? getIcon(suggestions[0].split('"')[1]) : icon,
    }
  }

  return {
    // Core functions
    getIcon,
    getSemanticIcon,
    getSizedIcon,
    getIconProps,
    
    // Specialized functions
    getActionIcon,
    getStatusIcon,
    getNavigationIcon,
    getEntityIcon,
    
    // Utility functions
    getAvailableIcons,
    validateIcon,
    
    // Computed properties for templates
    iconMap: computed(() => ICON_MAP),
    iconSizes: computed(() => ICON_SIZES),
    iconColors: computed(() => ICON_COLORS),
    iconCategories: computed(() => ICON_CATEGORIES),
  }
}

// Export constants for direct use
export { ICON_CATEGORIES, ICON_COLORS, ICON_MAP, ICON_SIZES }

