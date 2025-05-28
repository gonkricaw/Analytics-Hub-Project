/**
 * Form Accessibility Composable
 * Provides utilities for enhancing form accessibility
 */

import { computed, nextTick, ref } from 'vue'

export function useFormAccessibility(formRef) {
  const errors = ref({})
  const touched = ref({})
  const announcements = ref([])
  const formId = ref(`form-${Math.random().toString(36).substr(2, 9)}`)

  /**
   * Generate ARIA attributes for form fields
   */
  const generateAriaAttributes = (fieldName, options = {}) => {
    const {
      required = false,
      invalid = false,
      describedBy = null,
      label = null,
    } = options

    const attrs = {
      'aria-required': required,
      'aria-invalid': invalid,
      'aria-describedby': [
        describedBy,
        errors.value[fieldName] ? `${fieldName}-error` : null,
        `${fieldName}-help`,
      ].filter(Boolean).join(' ') || null,
    }

    if (label) {
      attrs['aria-label'] = label
    }

    return attrs
  }

  /**
   * Get accessible field attributes for form fields
   * @param {string} fieldName - Name of the form field
   * @param {Object} options - Field configuration options
   * @returns {Object} ARIA attributes and accessibility properties
   */
  const getFieldAttributes = (fieldName, options = {}) => {
    const {
      required = false,
      type = 'text',
      label = '',
      description = '',
      error = null,
      disabled = false,
      readonly = false,
    } = options

    // Use form-based ID pattern to match the test expectations and labels
    const fieldId = `${formId.value}-${fieldName}`
    const errorId = `${fieldId}-error`
    const descriptionId = `${fieldId}-description`
    const labelId = `${fieldId}-label`

    // Base attributes
    const attributes = {
      id: fieldId,
      name: fieldName,
      'aria-required': required,
      'aria-invalid': !!error || !!errors.value[fieldName],
      'aria-disabled': disabled,
      'aria-readonly': readonly,
    }

    // Always add describedby, even if empty initially
    const describedBy = []
    if (description) {
      describedBy.push(descriptionId)
    }
    if (error || errors.value[fieldName]) {
      describedBy.push(errorId)
    }

    // Always include the basic describedby attribute for consistency
    attributes['aria-describedby'] = describedBy.length > 0 ? describedBy.join(' ') : `${fieldId}-help`

    // Add labelledby
    if (label) {
      attributes['aria-labelledby'] = labelId
    }

    return attributes
  }

  /**
   * Get label attributes for proper label-field association
   * @param {string} fieldName - Name of the form field
   * @returns {Object} Label attributes
   */
  const getLabelAttributes = fieldName => {
    const fieldId = `${formId.value}-${fieldName}`
    
    return {
      for: fieldId,
      id: `${fieldId}-label`,
    }
  }

  /**
   * Set field error with accessibility announcements
   */
  const setFieldError = (fieldName, errorMessage) => {
    errors.value[fieldName] = errorMessage
    touched.value[fieldName] = true

    // Announce error to screen readers
    announceToScreenReader(`${fieldName} error: ${errorMessage}`, 'assertive')
  }

  /**
   * Clear field error
   */
  const clearFieldError = fieldName => {
    delete errors.value[fieldName]
    
    // Announce success if field was previously invalid
    if (touched.value[fieldName]) {
      announceToScreenReader(`${fieldName} is now valid`, 'polite')
    }
  }

  /**
   * Validate field and set error state
   */
  const validateField = (fieldName, value, rules = []) => {
    for (const rule of rules) {
      const result = rule(value)
      if (result !== true) {
        setFieldError(fieldName, result)
        
        return false
      }
    }
    
    clearFieldError(fieldName)
    
    return true
  }

  /**
   * Announce message to screen readers
   */
  const announceToScreenReader = (message, priority = 'polite') => {
    const announcement = {
      id: Date.now(),
      message,
      priority,
    }
    
    announcements.value.push(announcement)
    
    // Remove announcement after it's been announced
    setTimeout(() => {
      announcements.value = announcements.value.filter(a => a.id !== announcement.id)
    }, 1000)
  }

  /**
   * Focus management for form navigation
   */
  const focusFirstError = () => {
    const firstErrorField = Object.keys(errors.value)[0]
    if (firstErrorField && formRef?.value) {
      // Try both name and the new ID pattern
      const fieldId = `${formId.value}-${firstErrorField}`
      const errorElement = formRef.value.querySelector(`[name="${firstErrorField}"], #${fieldId}`)
      if (errorElement) {
        errorElement.focus()

        // Announce the error
        announceToScreenReader(`Please correct the error in ${firstErrorField}`, 'assertive')
      }
    }
  }

  /**
   * Focus next form field
   */
  const focusNextField = currentFieldName => {
    if (!formRef?.value) return

    const formElements = Array.from(formRef.value.querySelectorAll('input, select, textarea, button'))
    const fieldId = `${formId.value}-${currentFieldName}`

    const currentIndex = formElements.findIndex(el => 
      el.name === currentFieldName || el.id === fieldId,
    )
    
    if (currentIndex !== -1 && currentIndex < formElements.length - 1) {
      formElements[currentIndex + 1].focus()
    }
  }

  /**
   * Focus previous form field
   */
  const focusPreviousField = currentFieldName => {
    if (!formRef?.value) return

    const formElements = Array.from(formRef.value.querySelectorAll('input, select, textarea, button'))
    const fieldId = `${formId.value}-${currentFieldName}`

    const currentIndex = formElements.findIndex(el => 
      el.name === currentFieldName || el.id === fieldId,
    )
    
    if (currentIndex > 0) {
      formElements[currentIndex - 1].focus()
    }
  }

  /**
   * Handle keyboard navigation
   */
  const handleKeyboardNavigation = (event, fieldName) => {
    // Ctrl/Cmd + Arrow keys for field navigation
    if ((event.ctrlKey || event.metaKey)) {
      if (event.key === 'ArrowDown') {
        event.preventDefault()
        focusNextField(fieldName)
      } else if (event.key === 'ArrowUp') {
        event.preventDefault()
        focusPreviousField(fieldName)
      }
    }
  }

  /**
   * Generate error message ID
   */
  const getErrorId = fieldName => `${fieldName}-error`

  /**
   * Generate help text ID
   */
  const getHelpId = fieldName => `${fieldName}-help`

  /**
   * Check if field has error
   */
  const hasError = fieldName => Boolean(errors.value[fieldName])

  /**
   * Get error message for field
   */
  const getError = fieldName => errors.value[fieldName] || null

  /**
   * Check if field is required
   */
  const isRequired = (fieldName, rules = []) => {
    return rules.some(rule => rule.toString().includes('required'))
  }

  /**
   * Form submission with accessibility
   */
  const handleFormSubmit = (submitHandler, validationRules = {}) => {
    return async event => {
      event?.preventDefault()

      // Clear previous errors
      errors.value = {}

      // Validate all fields
      let isValid = true
      const fieldNames = Object.keys(validationRules)

      for (const fieldName of fieldNames) {
        const field = formRef?.value?.querySelector(`[name="${fieldName}"], #${fieldName}`)
        const value = field?.value || ''
        const rules = validationRules[fieldName] || []

        if (!validateField(fieldName, value, rules)) {
          isValid = false
        }
      }

      if (!isValid) {
        announceToScreenReader('Form contains errors. Please review and correct them.', 'assertive')
        await nextTick()
        focusFirstError()
        
        return false
      }

      // Announce successful validation
      announceToScreenReader('Form is valid. Submitting...', 'polite')

      try {
        await submitHandler()
        announceToScreenReader('Form submitted successfully', 'polite')
        
        return true
      } catch (error) {
        announceToScreenReader('Form submission failed. Please try again.', 'assertive')
        
        return false
      }
    }
  }

  /**
   * Generate form field wrapper attributes
   */
  const getFieldWrapperAttributes = fieldName => {
    return {
      'data-field': fieldName,
      'data-has-error': hasError(fieldName),
      'data-touched': touched.value[fieldName],
    }
  }

  /**
   * Live region for announcements
   */
  const liveRegionAttributes = computed(() => ({
    'aria-live': 'polite',
    'aria-atomic': 'true',
    'role': 'status',
  }))

  /**
   * Alert region for errors
   */
  const alertRegionAttributes = computed(() => ({
    'aria-live': 'assertive',
    'aria-atomic': 'true',
    'role': 'alert',
  }))

  /**
   * Announce error messages to screen readers
   * @param {string} fieldName - Name of the field with error
   * @param {string} errorMessage - Error message to announce
   */
  const announceError = (fieldName, errorMessage) => {
    const announcement = `Error in ${fieldName}: ${errorMessage}`

    announcements.value.push({
      message: announcement,
      type: 'error',
      timestamp: Date.now(),
    })
    
    // Create or update live region for immediate announcement
    if (typeof document !== 'undefined') {
      let liveRegion = document.getElementById('form-error-announcements')
      if (!liveRegion) {
        liveRegion = document.createElement('div')
        liveRegion.id = 'form-error-announcements'
        liveRegion.setAttribute('aria-live', 'assertive')
        liveRegion.setAttribute('aria-atomic', 'true')
        liveRegion.className = 'sr-only'
        liveRegion.style.cssText = `
          position: absolute !important;
          width: 1px !important;
          height: 1px !important;
          padding: 0 !important;
          margin: -1px !important;
          overflow: hidden !important;
          clip: rect(0, 0, 0, 0) !important;
          white-space: nowrap !important;
          border: 0 !important;
        `
        document.body.appendChild(liveRegion)
      }
      
      liveRegion.textContent = announcement
      
      // Clear after announcement
      setTimeout(() => {
        if (liveRegion) {
          liveRegion.textContent = ''
        }
      }, 1000)
    }
  }

  return {
    // State
    errors,
    touched,
    announcements,
    formId,

    // Methods
    generateAriaAttributes,
    getFieldAttributes,
    getLabelAttributes,
    setFieldError,
    clearFieldError,
    validateField,
    announceToScreenReader,
    announceError,
    focusFirstError,
    focusNextField,
    focusPreviousField,
    handleKeyboardNavigation,
    handleFormSubmit,
    handleFormSubmission: handleFormSubmit, // Alias for test compatibility

    // Utilities
    getErrorId,
    getHelpId,
    hasError,
    getError,
    isRequired,
    getFieldWrapperAttributes,

    // Computed
    liveRegionAttributes,
    alertRegionAttributes,
  }
}

/**
 * Common validation rules with accessibility-friendly messages
 */
export const validationRules = {
  required: (fieldName = 'This field') => value => {
    if (!value || (typeof value === 'string' && !value.trim())) {
      return `${fieldName} is required`
    }
    
    return true
  },

  email: value => {
    if (!value) return true
    const emailRegex = /^[^\s@]+@[^\s@][^\s.@]*\.[^\s@]+$/
    
    return emailRegex.test(value) || 'Please enter a valid email address'
  },

  minLength: (min, fieldName = 'This field') => value => {
    if (!value) return true
    
    return value.length >= min || `${fieldName} must be at least ${min} characters long`
  },

  maxLength: (max, fieldName = 'This field') => value => {
    if (!value) return true
    
    return value.length <= max || `${fieldName} must be no more than ${max} characters long`
  },

  password: value => {
    if (!value) return true
    
    const checks = [
      { test: value.length >= 8, message: 'at least 8 characters' },
      { test: /[A-Z]/.test(value), message: 'one uppercase letter' },
      { test: /[a-z]/.test(value), message: 'one lowercase letter' },
      { test: /\d/.test(value), message: 'one number' },
      { test: /[!@#$%^&*(),.?":{}|<>]/.test(value), message: 'one special character' },
    ]

    const failed = checks.filter(check => !check.test)
    if (failed.length > 0) {
      return `Password must contain ${failed.map(f => f.message).join(', ')}`
    }
    
    return true
  },

  confirmPassword: originalPassword => value => {
    if (!value) return true
    
    return value === originalPassword || 'Passwords do not match'
  },

  url: value => {
    if (!value) return true
    try {
      new URL(value)
      
      return true
    } catch {
      return 'Please enter a valid URL'
    }
  },

  number: value => {
    if (!value) return true
    
    return !isNaN(value) || 'Please enter a valid number'
  },

  positiveNumber: value => {
    if (!value) return true
    const num = Number(value)
    
    return (!isNaN(num) && num > 0) || 'Please enter a positive number'
  },
}
