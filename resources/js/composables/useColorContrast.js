/**
 * Color Contrast Utilities
 * 
 * Provides tools for checking and ensuring WCAG-compliant color contrast ratios
 * Automatically validates text/background combinations and suggests improvements
 * Supports both light and dark themes with dynamic contrast validation
 * 
 * Features:
 * - WCAG AA/AAA compliance checking
 * - Color contrast ratio calculation
 * - Automatic color adjustment suggestions
 * - Theme-aware contrast validation
 * - Real-time contrast monitoring
 * - Accessibility reporting
 * 
 * @author Indonet Analytics Hub Team
 * @version 1.0.0
 */

import { computed, reactive, ref } from 'vue'

// WCAG contrast ratio thresholds
const WCAG_THRESHOLDS = {
  AA_NORMAL: 4.5,
  AA_LARGE: 3.0,
  AAA_NORMAL: 7.0,
  AAA_LARGE: 4.5,
}

// Color contrast state
const contrastIssues = reactive(new Map())

const contrastReport = ref({
  total: 0,
  passed: 0,
  failed: 0,
  issues: [],
})

export function useColorContrast() {
  /**
   * Convert hex color to RGB values
   */
  const hexToRgb = hex => {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
    
    return result ? {
      r: parseInt(result[1], 16),
      g: parseInt(result[2], 16),
      b: parseInt(result[3], 16),
    } : null
  }

  /**
   * Convert RGB to luminance value
   */
  const getLuminance = (r, g, b) => {
    const [rs, gs, bs] = [r, g, b].map(c => {
      c = c / 255
      
      return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4)
    })

    
    return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs
  }

  /**
   * Calculate contrast ratio between two colors
   */
  const getContrastRatio = (color1, color2) => {
    const rgb1 = typeof color1 === 'string' ? hexToRgb(color1) : color1
    const rgb2 = typeof color2 === 'string' ? hexToRgb(color2) : color2
    
    if (!rgb1 || !rgb2) return 0
    
    const lum1 = getLuminance(rgb1.r, rgb1.g, rgb1.b)
    const lum2 = getLuminance(rgb2.r, rgb2.g, rgb2.b)
    
    const brightest = Math.max(lum1, lum2)
    const darkest = Math.min(lum1, lum2)
    
    return (brightest + 0.05) / (darkest + 0.05)
  }

  /**
   * Check if contrast meets WCAG standards
   */
  const checkWCAGCompliance = (ratio, isLargeText = false, level = 'AA') => {
    const threshold = level === 'AAA' 
      ? (isLargeText ? WCAG_THRESHOLDS.AAA_LARGE : WCAG_THRESHOLDS.AAA_NORMAL)
      : (isLargeText ? WCAG_THRESHOLDS.AA_LARGE : WCAG_THRESHOLDS.AA_NORMAL)
    
    return {
      passes: ratio >= threshold,
      ratio,
      threshold,
      level,
      isLargeText,
    }
  }

  /**
   * Suggest better color combinations
   */
  const suggestBetterColors = (foreground, background, level = 'AA', isLargeText = false) => {
    const targetRatio = level === 'AAA' 
      ? (isLargeText ? WCAG_THRESHOLDS.AAA_LARGE : WCAG_THRESHOLDS.AAA_NORMAL)
      : (isLargeText ? WCAG_THRESHOLDS.AA_LARGE : WCAG_THRESHOLDS.AA_NORMAL)

    const suggestions = []
    
    // Generate a variety of high contrast color combinations
    const colors = [
      { name: 'Black on White', fg: '#000000', bg: '#ffffff' },
      { name: 'White on Black', fg: '#ffffff', bg: '#000000' },
      { name: 'Dark Gray on White', fg: '#333333', bg: '#ffffff' },
      { name: 'White on Dark Blue', fg: '#ffffff', bg: '#003366' },
      { name: 'Black on Light Gray', fg: '#000000', bg: '#f5f5f5' },
      { name: 'Dark Blue on Light Blue', fg: '#003366', bg: '#e6f3ff' },
    ]
    
    colors.forEach(combo => {
      const ratio = getContrastRatio(combo.fg, combo.bg)
      if (ratio >= targetRatio) {
        suggestions.push({
          name: combo.name,
          foreground: combo.fg,
          background: combo.bg,
          ratio,
          compliant: true,
        })
      }
    })
    
    return suggestions
  }

  /**
   * Adjust color lightness
   */
  const adjustColorLightness = (color, lightness) => {
    const rgb = typeof color === 'string' ? hexToRgb(color) : color
    if (!rgb) return color
    
    const factor = lightness / 100
    const adjustedR = Math.round(rgb.r * factor)
    const adjustedG = Math.round(rgb.g * factor)
    const adjustedB = Math.round(rgb.b * factor)
    
    return `#${adjustedR.toString(16).padStart(2, '0')}${adjustedG.toString(16).padStart(2, '0')}${adjustedB.toString(16).padStart(2, '0')}`
  }

  /**
   * Extract colors from CSS custom properties
   */
  const extractThemeColors = () => {
    const computedStyle = getComputedStyle(document.documentElement)
    const themeColors = {}
    
    // Extract Vuetify theme colors
    const colorProperties = [
      'primary',
      'secondary',
      'accent',
      'error',
      'warning',
      'info',
      'success',
      'surface',
      'background',
      'on-surface',
      'on-background',
      'on-primary',
    ]
    
    colorProperties.forEach(prop => {
      const value = computedStyle.getPropertyValue(`--v-theme-${prop}`)
      if (value) {
        themeColors[prop] = value.trim()
      }
    })
    
    return themeColors
  }

  /**
   * Validate all text/background combinations in the current theme
   */
  const validateThemeContrast = () => {
    const colors = extractThemeColors()
    const issues = []
    
    // Common text/background combinations to check
    const combinations = [
      { text: 'on-surface', background: 'surface' },
      { text: 'on-background', background: 'background' },
      { text: 'on-primary', background: 'primary' },
      { text: 'surface', background: 'primary' },
      { text: 'background', background: 'error' },
      { text: 'surface', background: 'warning' },
      { text: 'surface', background: 'info' },
      { text: 'surface', background: 'success' },
    ]
    
    combinations.forEach(combo => {
      const textColor = colors[combo.text]
      const bgColor = colors[combo.background]
      
      if (textColor && bgColor) {
        const ratio = getContrastRatio(textColor, bgColor)
        const compliance = checkWCAGCompliance(ratio)
        
        if (!compliance.passes) {
          issues.push({
            textColor: combo.text,
            backgroundColor: combo.background,
            ratio,
            compliance,
            suggestions: suggestBetterColors(textColor, WCAG_THRESHOLDS.AA_NORMAL),
          })
        }
      }
    })
    
    // Update global contrast report
    contrastReport.value = {
      total: combinations.length,
      passed: combinations.length - issues.length,
      failed: issues.length,
      issues,
    }
    
    return issues
  }

  /**
   * Check specific element contrast
   */
  const checkElementContrast = element => {
    if (!element) return null
    
    const computedStyle = getComputedStyle(element)
    const textColor = computedStyle.color
    const backgroundColor = computedStyle.backgroundColor
    
    // Convert to hex if needed
    const textHex = rgbToHex(textColor)
    const bgHex = rgbToHex(backgroundColor)
    
    if (textHex && bgHex) {
      const ratio = getContrastRatio(textHex, bgHex)

      const isLargeText = parseFloat(computedStyle.fontSize) >= 18 || 
                         computedStyle.fontWeight === 'bold'
      
      return {
        ratio,
        textColor: textHex,
        backgroundColor: bgHex,
        compliance: checkWCAGCompliance(ratio, isLargeText),
        element,
      }
    }
    
    return null
  }

  /**
   * Convert RGB string to hex
   */
  const rgbToHex = rgb => {
    if (!rgb || rgb === 'transparent') return null
    
    const match = rgb.match(/\d+/g)
    if (!match || match.length < 3) return null
    
    const r = parseInt(match[0])
    const g = parseInt(match[1])
    const b = parseInt(match[2])
    
    return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`
  }

  /**
   * Monitor contrast issues in real-time
   */
  const startContrastMonitoring = () => {
    const observer = new MutationObserver(() => {
      validateThemeContrast()
    })
    
    observer.observe(document.body, {
      attributes: true,
      attributeFilter: ['class', 'style'],
      subtree: true,
    })
    
    // Also listen for theme changes
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')

    mediaQuery.addEventListener('change', validateThemeContrast)
    
    return () => {
      observer.disconnect()
      mediaQuery.removeEventListener('change', validateThemeContrast)
    }
  }

  /**
   * Generate accessibility report
   */
  const generateAccessibilityReport = () => {
    const issues = validateThemeContrast()
    
    return {
      timestamp: new Date().toISOString(),
      summary: contrastReport.value,
      details: issues.map(issue => ({
        ...issue,
        severity: issue.ratio < WCAG_THRESHOLDS.AA_LARGE ? 'high' : 'medium',
        recommendation: `Increase contrast ratio from ${issue.ratio.toFixed(2)} to at least ${WCAG_THRESHOLDS.AA_NORMAL}`,
      })),
      guidelines: {
        'WCAG AA Normal Text': `Minimum ratio: ${WCAG_THRESHOLDS.AA_NORMAL}`,
        'WCAG AA Large Text': `Minimum ratio: ${WCAG_THRESHOLDS.AA_LARGE}`,
        'WCAG AAA Normal Text': `Minimum ratio: ${WCAG_THRESHOLDS.AAA_NORMAL}`,
        'WCAG AAA Large Text': `Minimum ratio: ${WCAG_THRESHOLDS.AAA_LARGE}`,
      },
    }
  }

  /**
   * Fix contrast issue by adjusting colors
   */
  const fixContrastIssue = (textColor, backgroundColor, targetRatio = WCAG_THRESHOLDS.AA_NORMAL) => {
    let adjustedTextColor = textColor
    let adjustedBgColor = backgroundColor
    
    // Try darkening text color first
    for (let factor = 0.9; factor >= 0.1; factor -= 0.1) {
      const testColor = adjustColorLightness(textColor, factor * 100)
      const ratio = getContrastRatio(testColor, backgroundColor)
      
      if (ratio >= targetRatio) {
        adjustedTextColor = testColor
        break
      }
    }
    
    // If text adjustment didn't work, try lightening background
    if (getContrastRatio(adjustedTextColor, backgroundColor) < targetRatio) {
      for (let factor = 1.1; factor <= 2.0; factor += 0.1) {
        const testColor = adjustColorLightness(backgroundColor, factor * 100)
        const ratio = getContrastRatio(textColor, testColor)
        
        if (ratio >= targetRatio) {
          adjustedBgColor = testColor
          break
        }
      }
    }
    
    return {
      originalText: textColor,
      originalBackground: backgroundColor,
      adjustedText: adjustedTextColor,
      adjustedBackground: adjustedBgColor,
      ratio: getContrastRatio(adjustedTextColor, adjustedBgColor),
    }
  }

  // Computed properties
  const hasContrastIssues = computed(() => contrastReport.value.failed > 0)

  const compliancePercentage = computed(() => {
    const total = contrastReport.value.total
    
    return total > 0 ? Math.round((contrastReport.value.passed / total) * 100) : 100
  })

  /**
   * Check if color combination is WCAG compliant
   */
  const isWCAGCompliant = (foreground, background, level = 'AA', isLargeText = false) => {
    const ratio = getContrastRatio(foreground, background)
    const compliance = checkWCAGCompliance(ratio, isLargeText, level)
    
    // Return boolean for simple compliance check
    return compliance.passes
  }

  /**
   * Get detailed WCAG compliance information
   */
  const getWCAGComplianceDetails = (foreground, background, level = 'AA', isLargeText = false) => {
    const ratio = getContrastRatio(foreground, background)
    const compliance = checkWCAGCompliance(ratio, isLargeText, level)
    
    return {
      compliant: compliance.passes,
      ratio: compliance.ratio,
      level: compliance.passes ? (ratio >= WCAG_THRESHOLDS.AAA_NORMAL ? 'AAA' : 'AA') : 'FAIL',
      threshold: compliance.threshold,
    }
  }

  return {
    // Core functions
    getContrastRatio,
    checkWCAGCompliance,
    isWCAGCompliant,
    getWCAGComplianceDetails,
    hexToRgb,
    rgbToHex,
    
    // Function aliases for test compatibility
    calculateContrast: getContrastRatio,
    calculateContrastRatio: getContrastRatio,
    suggestAccessibleColors: suggestBetterColors,
    initContrastMonitoring: startContrastMonitoring,
    
    // Theme validation
    validateThemeContrast,
    checkElementContrast,
    extractThemeColors,
    
    // Color adjustment
    suggestBetterColors,
    adjustColorLightness,
    fixContrastIssue,
    
    // Monitoring
    startContrastMonitoring,
    generateAccessibilityReport,
    
    // State
    contrastIssues,
    contrastReport,
    hasContrastIssues,
    compliancePercentage,
    
    // Constants
    WCAG_THRESHOLDS,
  }
}
