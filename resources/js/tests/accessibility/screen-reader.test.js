/**
 * Accessibility Testing with Real Screen Reader Support
 * 
 * This test suite validates screen reader compatibility and ARIA implementation
 * using jsdom-testing-library and screen reader simulation
 */

import SkipLinks from '@/components/accessibility/SkipLinks.vue'
import { useFormAccessibility } from '@/composables/useFormAccessibility'
import { fireEvent, render, screen, waitFor } from '@testing-library/vue'
import { axe, toHaveNoViolations } from 'jest-axe'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createVuetify } from 'vuetify'

// Extend Jest matchers
expect.extend(toHaveNoViolations)

// Mock components for comprehensive testing
const AccessibleFormComponent = {
  template: `
    <div>
      <form 
        :id="formId" 
        @submit.prevent="handleSubmit"
        role="form"
        :aria-labelledby="formId + '-title'"
        :aria-describedby="formId + '-description'"
      >
        <h2 :id="formId + '-title'">Registration Form</h2>
        <p :id="formId + '-description'">Please fill out all required fields to register.</p>
        
        <!-- Live region for announcements -->
        <div 
          id="form-announcements"
          aria-live="polite" 
          aria-atomic="true" 
          class="sr-only"
        >
          {{ announcement }}
        </div>
        
        <!-- Name field with validation -->
        <div class="form-group">
          <label :for="formId + '-name'" class="form-label">
            Full Name *
          </label>
          <input 
            :id="formId + '-name'"
            type="text" 
            v-model="formData.name"
            v-bind="getFieldAttributes('name', 'Enter your full legal name')"
            class="form-input"
            :class="{ 'error': errors.name }"
            :aria-invalid="!!errors.name"
            :aria-describedby="errors.name ? formId + '-name-error' : formId + '-name-help'"
            placeholder="Enter your full name"
            required
            autocomplete="name"
            @blur="validateName"
          />
          <div 
            :id="formId + '-name-help'" 
            class="form-help"
            :aria-hidden="!!errors.name"
          >
            Enter your first and last name
          </div>
          <div 
            v-if="errors.name"
            :id="formId + '-name-error'"
            class="form-error"
            role="alert"
            aria-live="assertive"
          >
            {{ errors.name }}
          </div>
        </div>
        
        <!-- Email field with validation -->
        <div class="form-group">
          <label :for="formId + '-email'" class="form-label">
            Email Address *
          </label>
          <input 
            :id="formId + '-email'"
            type="email" 
            v-model="formData.email"
            v-bind="getFieldAttributes('email', 'Enter a valid email address')"
            class="form-input"
            :class="{ 'error': errors.email }"
            :aria-invalid="!!errors.email"
            :aria-describedby="errors.email ? formId + '-email-error' : formId + '-email-help'"
            placeholder="Enter your email"
            required
            autocomplete="email"
            @blur="validateEmail"
          />
          <div 
            :id="formId + '-email-help'" 
            class="form-help"
            :aria-hidden="!!errors.email"
          >
            We'll use this to send you important updates
          </div>
          <div 
            v-if="errors.email"
            :id="formId + '-email-error'"
            class="form-error"
            role="alert"
            aria-live="assertive"
          >
            {{ errors.email }}
          </div>
        </div>
        
        <!-- Password field with requirements -->
        <div class="form-group">
          <label :for="formId + '-password'" class="form-label">
            Password *
          </label>
          <input 
            :id="formId + '-password'"
            :type="showPassword ? 'text' : 'password'"
            v-model="formData.password"
            v-bind="getFieldAttributes('password', 'Create a strong password')"
            class="form-input"
            :class="{ 'error': errors.password }"
            :aria-invalid="!!errors.password"
            :aria-describedby="formId + '-password-requirements ' + (errors.password ? formId + '-password-error' : '')"
            placeholder="Create a password"
            required
            autocomplete="new-password"
            @blur="validatePassword"
          />
          <button 
            type="button" 
            class="password-toggle"
            :aria-label="showPassword ? 'Hide password' : 'Show password'"
            :aria-pressed="showPassword"
            @click="togglePasswordVisibility"
          >
            {{ showPassword ? 'Hide' : 'Show' }}
          </button>
          
          <!-- Password requirements -->
          <div 
            :id="formId + '-password-requirements'"
            class="password-requirements"
            role="group"
            aria-labelledby="password-req-title"
          >
            <h3 id="password-req-title" class="sr-only">Password Requirements</h3>
            <ul role="list" aria-label="Password requirements">
              <li 
                v-for="(requirement, index) in passwordRequirements"
                :key="index"
                :class="{ 'met': requirement.test(formData.password) }"
                role="listitem"
                :aria-label="requirement.test(formData.password) ? 'Requirement met: ' + requirement.text : 'Requirement not met: ' + requirement.text"
              >
                <span 
                  class="requirement-icon"
                  :aria-hidden="true"
                >
                  {{ requirement.test(formData.password) ? '✓' : '○' }}
                </span>
                {{ requirement.text }}
              </li>
            </ul>
          </div>
          
          <div 
            v-if="errors.password"
            :id="formId + '-password-error'"
            class="form-error"
            role="alert"
            aria-live="assertive"
          >
            {{ errors.password }}
          </div>
        </div>
        
        <!-- Terms and conditions -->
        <div class="form-group">
          <label class="checkbox-label">
            <input 
              :id="formId + '-terms'"
              type="checkbox" 
              v-model="formData.agreeToTerms"
              :aria-describedby="formId + '-terms-help'"
              required
              @change="validateTerms"
            />
            <span class="checkbox-text">
              I agree to the 
              <a href="/terms" target="_blank" rel="noopener noreferrer">
                Terms and Conditions
                <span class="sr-only">(opens in new tab)</span>
              </a>
            </span>
          </label>
          <div 
            :id="formId + '-terms-help'"
            class="form-help"
          >
            You must agree to our terms to create an account
          </div>
        </div>
        
        <!-- Submit button -->
        <div class="form-actions">
          <button 
            type="submit" 
            class="submit-button"
            :disabled="!isFormValid || isSubmitting"
            :aria-describedby="!isFormValid ? formId + '-submit-help' : undefined"
          >
            <span v-if="isSubmitting" class="loading-spinner" aria-hidden="true"></span>
            {{ isSubmitting ? 'Creating Account...' : 'Create Account' }}
          </button>
          
          <div 
            v-if="!isFormValid"
            :id="formId + '-submit-help'"
            class="sr-only"
            aria-live="polite"
          >
            Please fill in all required fields correctly to create your account
          </div>
        </div>
        
        <!-- Form success/error messages -->
        <div 
          v-if="submitMessage"
          class="form-message"
          :class="{ 'success': submitSuccess, 'error': !submitSuccess }"
          role="alert"
          aria-live="assertive"
        >
          {{ submitMessage }}
        </div>
      </form>
    </div>
  `,
  setup() {
    const {
      formId,
      getFieldAttributes,
      handleFormSubmission,
      announceToScreenReader,
    } = useFormAccessibility({
      formName: 'registration',
      validationRules: {
        name: { required: true, minLength: 2 },
        email: { required: true, email: true },
        password: { required: true, minLength: 8 },
      },
    })
    
    const formData = reactive({
      name: '',
      email: '',
      password: '',
      agreeToTerms: false,
    })
    
    const errors = reactive({
      name: '',
      email: '',
      password: '',
    })
    
    const announcement = ref('')
    const showPassword = ref(false)
    const isSubmitting = ref(false)
    const submitMessage = ref('')
    const submitSuccess = ref(false)
    
    const passwordRequirements = [
      { test: pwd => pwd.length >= 8, text: 'At least 8 characters' },
      { test: pwd => /[A-Z]/.test(pwd), text: 'One uppercase letter' },
      { test: pwd => /[a-z]/.test(pwd), text: 'One lowercase letter' },
      { test: pwd => /\d/.test(pwd), text: 'One number' },
      { test: pwd => /[!@#$%^&*]/.test(pwd), text: 'One special character' },
    ]
    
    const isFormValid = computed(() => {
      return formData.name.length >= 2 &&
             /^[^\s@]+@[^\s@][^\s.@]*\.[^\s@]+$/.test(formData.email) &&
             passwordRequirements.every(req => req.test(formData.password)) &&
             formData.agreeToTerms &&
             !Object.values(errors).some(error => error)
    })
    
    const validateName = () => {
      if (!formData.name.trim()) {
        errors.name = 'Name is required'
      } else if (formData.name.length < 2) {
        errors.name = 'Name must be at least 2 characters'
      } else {
        errors.name = ''
      }
    }
    
    const validateEmail = () => {
      if (!formData.email.trim()) {
        errors.email = 'Email is required'
      } else if (!/^[^\s@]+@[^\s@][^\s.@]*\.[^\s@]+$/.test(formData.email)) {
        errors.email = 'Please enter a valid email address'
      } else {
        errors.email = ''
      }
    }
    
    const validatePassword = () => {
      if (!formData.password) {
        errors.password = 'Password is required'
      } else if (!passwordRequirements.every(req => req.test(formData.password))) {
        errors.password = 'Password must meet all requirements'
      } else {
        errors.password = ''
      }
    }
    
    const validateTerms = () => {
      if (!formData.agreeToTerms) {
        announceToScreenReader('You must agree to the terms and conditions', 'assertive')
      }
    }
    
    const togglePasswordVisibility = () => {
      showPassword.value = !showPassword.value
      announceToScreenReader(`Password is now ${showPassword.value ? 'visible' : 'hidden'}`, 'polite')
    }
    
    const handleSubmit = async () => {
      if (!isFormValid.value) {
        announceToScreenReader('Please fix the form errors before submitting', 'assertive')
        
        return
      }
      
      await handleFormSubmission(async () => {
        isSubmitting.value = true
        announcement.value = 'Creating your account...'
        
        try {
          // Simulate API call
          await new Promise(resolve => setTimeout(resolve, 2000))
          
          submitSuccess.value = true
          submitMessage.value = 'Account created successfully! Welcome aboard!'
          announcement.value = 'Account created successfully!'
          announceToScreenReader('Account created successfully! Welcome aboard!', 'polite')
          
          return { success: true }
        } catch (error) {
          submitSuccess.value = false
          submitMessage.value = 'Failed to create account. Please try again.'
          announcement.value = 'Account creation failed'
          announceToScreenReader('Account creation failed. Please try again.', 'assertive')
          
          return { success: false, error: error.message }
        } finally {
          isSubmitting.value = false
        }
      })
    }
    
    return {
      formId,
      getFieldAttributes,
      formData,
      errors,
      announcement,
      showPassword,
      isSubmitting,
      submitMessage,
      submitSuccess,
      passwordRequirements,
      isFormValid,
      validateName,
      validateEmail,
      validatePassword,
      validateTerms,
      togglePasswordVisibility,
      handleSubmit,
    }
  },
}

describe('Screen Reader Accessibility Tests', () => {
  let vuetify

  beforeEach(() => {
    vuetify = createVuetify()
    
    // Mock screen reader APIs
    global.speechSynthesis = {
      speak: vi.fn(),
      cancel: vi.fn(),
      getVoices: vi.fn(() => []),
    }
    
    // Clear DOM
    document.body.innerHTML = ''
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  describe('Form Accessibility', () => {
    it('should have no accessibility violations', async () => {
      const { container } = render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      const results = await axe(container)

      expect(results).toHaveNoViolations()
    })

    it('should provide proper form structure for screen readers', () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      // Check form has proper labeling
      const form = screen.getByRole('form', { name: /registration form/i })

      expect(form).toBeInTheDocument()
      
      // Check form has description
      expect(screen.getByText(/please fill out all required fields/i)).toBeInTheDocument()
      
      // Check all form fields have proper labels
      expect(screen.getByLabelText(/full name/i)).toBeInTheDocument()
      expect(screen.getByLabelText(/email address/i)).toBeInTheDocument()
      expect(screen.getByLabelText(/password/i)).toBeInTheDocument()
    })

    it('should provide comprehensive field descriptions and help text', () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      // Check help text is properly associated
      const nameField = screen.getByLabelText(/full name/i)

      expect(nameField).toHaveAttribute('aria-describedby')
      
      const emailField = screen.getByLabelText(/email address/i)

      expect(emailField).toHaveAttribute('aria-describedby')
      
      // Check help text content
      expect(screen.getByText(/enter your first and last name/i)).toBeInTheDocument()
      expect(screen.getByText(/we'll use this to send you important updates/i)).toBeInTheDocument()
    })

    it('should handle field validation with proper ARIA attributes', async () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      const nameField = screen.getByLabelText(/full name/i)
      
      // Trigger validation by focusing and blurring with empty value
      fireEvent.focus(nameField)
      fireEvent.blur(nameField)
      
      await waitFor(() => {
        // Field should be marked as invalid
        expect(nameField).toHaveAttribute('aria-invalid', 'true')
        
        // Error message should be present and announced
        const errorMessage = screen.getByRole('alert', { name: /name is required/i })

        expect(errorMessage).toBeInTheDocument()
        expect(errorMessage).toHaveAttribute('aria-live', 'assertive')
      })
    })

    it('should provide accessible password requirements', () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      // Check password requirements are properly structured
      const requirementsList = screen.getByRole('list', { name: /password requirements/i })

      expect(requirementsList).toBeInTheDocument()
      
      // Check individual requirements
      const requirements = screen.getAllByRole('listitem')

      expect(requirements).toHaveLength(5)
      
      // Each requirement should have proper aria-label
      requirements.forEach(requirement => {
        expect(requirement).toHaveAttribute('aria-label')
      })
    })

    it('should handle password visibility toggle accessibly', async () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      const toggleButton = screen.getByRole('button', { name: /show password/i })

      expect(toggleButton).toHaveAttribute('aria-pressed', 'false')
      
      // Toggle password visibility
      fireEvent.click(toggleButton)
      
      await waitFor(() => {
        expect(screen.getByRole('button', { name: /hide password/i })).toBeInTheDocument()
        expect(toggleButton).toHaveAttribute('aria-pressed', 'true')
      })
    })

    it('should announce form submission status to screen readers', async () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      // Fill out form with valid data
      fireEvent.change(screen.getByLabelText(/full name/i), { target: { value: 'John Doe' } })
      fireEvent.change(screen.getByLabelText(/email address/i), { target: { value: 'john@example.com' } })
      fireEvent.change(screen.getByLabelText(/password/i), { target: { value: 'SecurePass123!' } })
      fireEvent.click(screen.getByRole('checkbox'))
      
      // Submit form
      const submitButton = screen.getByRole('button', { name: /create account/i })

      fireEvent.click(submitButton)
      
      // Check loading state
      await waitFor(() => {
        expect(screen.getByText(/creating account/i)).toBeInTheDocument()
      })
      
      // Check success message
      await waitFor(() => {
        const successMessage = screen.getByRole('alert', { name: /account created successfully/i })

        expect(successMessage).toBeInTheDocument()
        expect(successMessage).toHaveAttribute('aria-live', 'assertive')
      }, { timeout: 3000 })
    })

    it('should provide proper keyboard navigation', async () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      const nameField = screen.getByLabelText(/full name/i)
      const emailField = screen.getByLabelText(/email address/i)
      const passwordField = screen.getByLabelText(/password/i)
      
      // Test tab navigation
      nameField.focus()
      expect(document.activeElement).toBe(nameField)
      
      // Tab to next field
      fireEvent.keyDown(nameField, { key: 'Tab' })
      expect(document.activeElement).toBe(emailField)
      
      // Tab to password field
      fireEvent.keyDown(emailField, { key: 'Tab' })
      expect(document.activeElement).toBe(passwordField)
    })

    it('should handle form submission via keyboard', async () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      const form = screen.getByRole('form')
      
      // Submit form with Enter key
      fireEvent.keyDown(form, { key: 'Enter' })
      
      // Should trigger validation and show errors for empty form
      await waitFor(() => {
        expect(screen.getByText(/please fix the form errors/i)).toBeInTheDocument()
      })
    })
  })

  describe('Skip Links Accessibility', () => {
    it('should provide proper skip links for keyboard navigation', () => {
      const { container } = render(SkipLinks, {
        global: {
          plugins: [vuetify],
          stubs: {
            RouterLink: {
              template: '<a :href="to"><slot /></a>',
              props: ['to'],
            },
          },
        },
      })
      
      const skipLinks = container.querySelectorAll('.skip-link')

      expect(skipLinks.length).toBeGreaterThan(0)
      
      // Each skip link should have proper content
      skipLinks.forEach(link => {
        expect(link.textContent.trim()).toBeTruthy()
        expect(link.getAttribute('href')).toBeTruthy()
      })
    })

    it('should have no accessibility violations in skip links', async () => {
      const { container } = render(SkipLinks, {
        global: {
          plugins: [vuetify],
          stubs: {
            RouterLink: {
              template: '<a :href="to"><slot /></a>',
              props: ['to'],
            },
          },
        },
      })
      
      const results = await axe(container)

      expect(results).toHaveNoViolations()
    })

    it('should be keyboard accessible', async () => {
      render(SkipLinks, {
        global: {
          plugins: [vuetify],
          stubs: {
            RouterLink: {
              template: '<a :href="to"><slot /></a>',
              props: ['to'],
            },
          },
        },
      })
      
      // Skip links should be focusable
      const firstSkipLink = screen.getAllByRole('link')[0]
      
      firstSkipLink.focus()
      expect(document.activeElement).toBe(firstSkipLink)
    })
  })

  describe('Live Regions and Announcements', () => {
    it('should create proper live regions for announcements', () => {
      const { container } = render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      // Check for live regions
      const liveRegions = container.querySelectorAll('[aria-live]')

      expect(liveRegions.length).toBeGreaterThan(0)
      
      // Check different politeness levels
      const politeLiveRegion = container.querySelector('[aria-live="polite"]')
      const assertiveLiveRegion = container.querySelector('[aria-live="assertive"]')
      
      expect(politeLiveRegion).toBeInTheDocument()
      expect(assertiveLiveRegion).toBeInTheDocument()
    })

    it('should properly structure screen reader content', () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      // Check for screen reader only content
      const srOnlyElements = document.querySelectorAll('.sr-only')

      expect(srOnlyElements.length).toBeGreaterThan(0)
      
      // Verify important content is not hidden from screen readers
      const formTitle = screen.getByRole('heading', { name: /registration form/i })

      expect(formTitle).toBeInTheDocument()
      expect(formTitle).not.toHaveClass('sr-only')
    })
  })

  describe('ARIA Landmarks and Structure', () => {
    it('should use proper semantic HTML and ARIA roles', () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      // Check for proper form structure
      expect(screen.getByRole('form')).toBeInTheDocument()
      expect(screen.getByRole('heading', { level: 2 })).toBeInTheDocument()
      
      // Check for proper grouping
      const passwordGroup = screen.getByRole('group', { name: /password requirements/i })

      expect(passwordGroup).toBeInTheDocument()
      
      // Check for list structure
      expect(screen.getByRole('list', { name: /password requirements/i })).toBeInTheDocument()
    })

    it('should have proper heading hierarchy', () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      // Main form heading should be h2
      const mainHeading = screen.getByRole('heading', { level: 2, name: /registration form/i })

      expect(mainHeading).toBeInTheDocument()
      
      // Sub-heading for password requirements should be h3
      const subHeading = document.querySelector('#password-req-title')

      expect(subHeading).toBeInTheDocument()
      expect(subHeading.tagName.toLowerCase()).toBe('h3')
    })
  })

  describe('Error Handling and Feedback', () => {
    it('should provide immediate error feedback with proper ARIA attributes', async () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      const emailField = screen.getByLabelText(/email address/i)
      
      // Enter invalid email
      fireEvent.change(emailField, { target: { value: 'invalid-email' } })
      fireEvent.blur(emailField)
      
      await waitFor(() => {
        // Error should be announced
        const errorAlert = screen.getByRole('alert', { name: /please enter a valid email/i })

        expect(errorAlert).toBeInTheDocument()
        
        // Field should be marked invalid
        expect(emailField).toHaveAttribute('aria-invalid', 'true')
        
        // Error should be associated with field
        expect(emailField.getAttribute('aria-describedby')).toContain('email-error')
      })
    })

    it('should clear errors when field becomes valid', async () => {
      render(AccessibleFormComponent, {
        global: {
          plugins: [vuetify],
        },
      })
      
      const emailField = screen.getByLabelText(/email address/i)
      
      // Enter invalid email first
      fireEvent.change(emailField, { target: { value: 'invalid' } })
      fireEvent.blur(emailField)
      
      await waitFor(() => {
        expect(screen.getByRole('alert')).toBeInTheDocument()
      })
      
      // Enter valid email
      fireEvent.change(emailField, { target: { value: 'valid@example.com' } })
      fireEvent.blur(emailField)
      
      await waitFor(() => {
        // Error should be cleared
        expect(screen.queryByRole('alert', { name: /please enter a valid email/i })).not.toBeInTheDocument()
        expect(emailField).toHaveAttribute('aria-invalid', 'false')
      })
    })
  })
})
