<template>
  <div
    class="modal-overlay"
    role="dialog"
    aria-modal="true"
    :aria-labelledby="`${formId}-title`"
    @click="handleOverlayClick"
  >
    <div
      class="modal-container"
      @click.stop
    >
      <div class="modal-header">
        <h2 
          :id="`${formId}-title`"
          class="modal-title"
        >
          <i :class="getNavigationIcon('menu')" />
          {{ isEditing ? 'Edit Menu Item' : 'Create Menu Item' }}
        </h2>
        <button
          class="modal-close"
          aria-label="Close dialog"
          @click="$emit('cancel')"
        >
          <i :class="getActionIcon('close')" />
        </button>
      </div>
      
      <form
        :id="formId"
        class="modal-form"
        @submit.prevent="handleSubmit"
        @keydown="handleKeyboardNavigation"
      >
        <!-- Screen reader announcements -->
        <div
          id="menu-form-announcements"
          aria-live="polite"
          aria-atomic="true"
          class="sr-only"
        />
        
        <div class="modal-body">
          <!-- Basic Information -->
          <div class="form-section">
            <h3 class="section-title">
              Basic Information
            </h3>
            
            <div class="form-group">
              <label
                for="name"
                class="form-label required"
              >Menu Name</label>
              <input
                id="name"
                v-model="form.name"
                v-bind="getFieldAttributes('name', 'The display name for this menu item')"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.name }"
                placeholder="Enter menu name"
                required
                @blur="handleFieldValidation('name')"
              >
              <div
                v-if="errors.name"
                class="invalid-feedback"
                role="alert"
              >
                {{ errors.name }}
              </div>
            </div>
            
            <div class="form-group">
              <label
                for="icon"
                class="form-label"
              >Icon</label>
              <div class="icon-input-group">
                <input
                  id="icon"
                  v-model="form.icon"
                  v-bind="getFieldAttributes('icon', 'Font Awesome icon class for the menu item')"
                  type="text"
                  class="form-control"
                  placeholder="fas fa-home"
                  :aria-describedby="`${formId}-icon-help`"
                >
                <div 
                  class="icon-preview"
                  :aria-label="`Icon preview: ${form.icon || 'No icon selected'}`"
                >
                  <i
                    v-if="form.icon"
                    :class="form.icon"
                  />
                  <span
                    v-else
                    class="no-icon"
                  >No icon</span>
                </div>
              </div>
              <small 
                :id="`${formId}-icon-help`"
                class="form-text"
              >
                Use Font Awesome classes (e.g., "fas fa-home", "fas fa-user")
              </small>
            </div>
            
            <div class="form-group">
              <label
                for="parent_id"
                class="form-label"
              >Parent Menu</label>
              <select
                id="parent_id"
                v-model="form.parent_id"
                class="form-control"
              >
                <option value="">
                  Root Level
                </option>
                <option 
                  v-for="option in parentOptions" 
                  :key="option.value"
                  :value="option.value"
                  :disabled="option.value === menu?.id"
                >
                  {{ option.label }}
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label
                for="order"
                class="form-label"
              >Display Order</label>
              <input
                id="order"
                v-model.number="form.order"
                type="number"
                class="form-control"
                min="0"
                placeholder="0"
              >
              <small class="form-text">
                Lower numbers appear first. Leave empty for automatic ordering.
              </small>
            </div>
          </div>
          
          <!-- Menu Type & Target -->
          <div class="form-section">
            <h3 class="section-title">
              Menu Type & Target
            </h3>
            
            <div class="form-group">
              <fieldset>
                <legend class="form-label required">
                  Menu Type
                </legend>
                <div 
                  class="radio-group"
                  role="radiogroup"
                  aria-labelledby="menu-type-legend"
                  :aria-describedby="`${formId}-type-help`"
                >
                  <label class="radio-option">
                    <input
                      v-model="form.type"
                      type="radio"
                      value="route"
                      name="menu-type"
                      aria-describedby="route-description"
                    >
                    <span class="radio-label">
                      <i :class="getNavigationIcon('internal')" />
                      Internal Route
                    </span>
                    <small id="route-description">Link to a page within the application</small>
                  </label>
                  
                  <label class="radio-option">
                    <input
                      v-model="form.type"
                      type="radio"
                      value="url"
                      name="menu-type"
                      aria-describedby="url-description"
                    >
                    <span class="radio-label">
                      <i :class="getNavigationIcon('external')" />
                      External URL
                    </span>
                    <small id="url-description">Link to an external website</small>
                  </label>
                  
                  <label class="radio-option">
                    <input
                      v-model="form.type"
                      type="radio"
                      value="content"
                      name="menu-type"
                      aria-describedby="content-description"
                    >
                    <span class="radio-label">
                      <i :class="getEntityIcon('content')" />
                      Content Page
                    </span>
                    <small id="content-description">Link to a managed content page</small>
                  </label>
                  
                  <label class="radio-option">
                    <input
                      v-model="form.type"
                      type="radio"
                      value="embed"
                      name="menu-type"
                      aria-describedby="embed-description"
                    >
                    <span class="radio-label">
                      <i :class="getEntityIcon('code')" />
                      Embedded Content
                    </span>
                    <small id="embed-description">Open embedded content in a new window</small>
                  </label>
                </div>
                <div 
                  :id="`${formId}-type-help`"
                  class="sr-only"
                >
                  Choose how this menu item should behave when clicked
                </div>
              </fieldset>
            </div>
            
            <!-- Route/URL Input -->
            <div
              v-if="form.type === 'route' || form.type === 'url'"
              class="form-group"
            >
              <label
                for="route_or_url"
                class="form-label required"
              >
                {{ form.type === 'route' ? 'Route Path' : 'URL' }}
              </label>
              <input
                id="route_or_url"
                v-model="form.route_or_url"
                v-bind="getFieldAttributes('route_or_url', form.type === 'route' ? 'Application route path' : 'External website URL')"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.route_or_url }"
                :placeholder="form.type === 'route' ? '/dashboard' : 'https://example.com'"
                required
                @blur="handleFieldValidation('route_or_url')"
              >
              <div
                v-if="errors.route_or_url"
                class="invalid-feedback"
                role="alert"
              >
                {{ errors.route_or_url }}
              </div>
            </div>
            
            <!-- Content Selection -->
            <div
              v-if="form.type === 'content' || form.type === 'embed'"
              class="form-group"
            >
              <label
                for="content_id"
                class="form-label required"
              >Content</label>
              <select
                id="content_id"
                v-model="form.content_id"
                v-bind="getFieldAttributes('content_id', 'Select content to link to this menu item')"
                class="form-control"
                :class="{ 'is-invalid': errors.content_id }"
                required
                @blur="handleFieldValidation('content_id')"
              >
                <option value="">
                  Select content...
                </option>
                <option 
                  v-for="content in filteredContentOptions" 
                  :key="content.value"
                  :value="content.value"
                >
                  {{ content.label }} ({{ content.type }})
                </option>
              </select>
              <div
                v-if="errors.content_id"
                class="invalid-feedback"
                role="alert"
              >
                {{ errors.content_id }}
              </div>
              
              <div
                v-if="form.type === 'embed'"
                class="form-note embed-note"
                role="note"
                aria-live="polite"
              >
                <i :class="getStatusIcon('info')" />
                <span>
                  Only content with embedded URLs can be used for embed menu items.
                </span>
              </div>
            </div>
          </div>
          
          <!-- Access Control -->
          <div class="form-section">
            <h3 class="section-title">
              Access Control
            </h3>
            
            <div class="form-group">
              <label
                for="role_permissions_required"
                class="form-label"
              >Required Permissions</label>
              <input
                id="role_permissions_required"
                v-model="form.role_permissions_required"
                type="text"
                class="form-control"
                placeholder="admin.access, reports.view"
              >
              <small class="form-text">
                Comma-separated list of permissions required to see this menu item.
                Leave empty for public access.
              </small>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            aria-label="Cancel and close dialog"
            @click="$emit('cancel')"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="!isFormValid || saving"
            :aria-describedby="!isFormValid ? `${formId}-submit-help` : undefined"
          >
            <i
              v-if="saving"
              :class="getStatusIcon('loading')"
              aria-hidden="true"
            />
            <i
              v-else
              :class="getActionIcon('save')"
              aria-hidden="true"
            />
            {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
          </button>
          
          <div 
            v-if="!isFormValid"
            :id="`${formId}-submit-help`"
            class="sr-only"
          >
            Please fill in all required fields to save the menu item
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { validationRules as rules, useFormAccessibility } from '@/composables/useFormAccessibility.js'
import { useIconSystem } from '@/composables/useIconSystem.js'
import { computed, onMounted, ref, watch } from 'vue'

const props = defineProps({
  menu: {
    type: Object,
    default: null,
  },
  parentOptions: {
    type: Array,
    default: () => [],
  },
  contentOptions: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['save', 'cancel'])

// Form accessibility composable
const {
  formId,
  getFieldAttributes,
  getErrorAttributes,
  handleKeyboardNavigation,
  announceToScreenReader,
  handleFormSubmission,
  validateField,
  focusFirstError,
} = useFormAccessibility()

// Icon system
const { getStatusIcon, getActionIcon, getNavigationIcon, getEntityIcon } = useIconSystem()

const saving = ref(false)
const errors = ref({})

const form = ref({
  name: '',
  icon: '',
  parent_id: '',
  order: '',
  type: 'route',
  route_or_url: '',
  content_id: '',
  role_permissions_required: '',
})

// Helper validation functions
const validateRouteOrUrl = (value) => {
  const formData = form.value
  if ((formData.type === 'route' || formData.type === 'url') && !value) {
    return 'This field is required'
  }
  if (formData.type === 'route' && value && !value.startsWith('/')) {
    return 'Route path must start with /'
  }
  if (formData.type === 'url' && value && !isValidUrl(value)) {
    return 'Please enter a valid URL'
  }
  return true
}

const validateContentId = (value) => {
  const formData = form.value
  if ((formData.type === 'content' || formData.type === 'embed') && !value) {
    return 'Content selection is required'
  }
  return true
}

// Form validation rules
const validationRules = {
  name: [
    rules.required('Menu name'),
    rules.minLength(2, 'Menu name')
  ],
  route_or_url: [
    validateRouteOrUrl
  ],
  content_id: [
    validateContentId
  ],
}

const isEditing = computed(() => !!props.menu?.id)

const filteredContentOptions = computed(() => {
  if (form.value.type === 'embed') {
    // Only show content that has embed URLs
    return props.contentOptions.filter(content => 
      content.type === 'embed' || content.hasEmbedUrl,
    )
  }
  
  return props.contentOptions
})

const isFormValid = computed(() => {
  return form.value.name.trim() && 
         !errors.value.name &&
         !errors.value.route_or_url &&
         !errors.value.content_id
})

// Handle field validation
const handleFieldValidation = field => {
  // Clear previous error
  errors.value[field] = ''
  
  const rules = validationRules[field]
  if (!rules) return true
  
  // Validate each rule for this field
  for (const rule of rules) {
    const result = rule(form.value[field])
    if (result !== true) {
      errors.value[field] = result
      announceToScreenReader(`${field} field: ${result}`, 'assertive')
      return false
    }
  }
  
  return true
}

const handleOverlayClick = () => {
  emit('cancel')
}

const handleSubmit = async () => {
  // Validate each field manually
  let formIsValid = true
  const formErrors = {}
  
  for (const [field, rules] of Object.entries(validationRules)) {
    if (!handleFieldValidation(field)) {
      formIsValid = false
      formErrors[field] = errors.value[field]
    }
  }
  
  if (!formIsValid) {
    announceToScreenReader('Please fix the errors in the form', 'assertive')
    focusFirstError(formErrors)
    
    return
  }
  
  // Handle form submission with accessibility feedback
  await handleFormSubmission(async () => {
    saving.value = true
    
    try {
      // Prepare form data
      const formData = {
        name: form.value.name.trim(),
        icon: form.value.icon.trim() || null,
        parent_id: form.value.parent_id || null,
        order: form.value.order || null,
        type: form.value.type,
        route_or_url: (form.value.type === 'route' || form.value.type === 'url') 
          ? form.value.route_or_url.trim() : null,
        content_id: (form.value.type === 'content' || form.value.type === 'embed') 
          ? form.value.content_id : null,
        role_permissions_required: form.value.role_permissions_required.trim() || null,
      }
      
      announceToScreenReader(`${isEditing.value ? 'Updating' : 'Creating'} menu item...`, 'polite')
      emit('save', formData)
      announceToScreenReader(`Menu item ${isEditing.value ? 'updated' : 'created'} successfully`, 'polite')
    } catch (error) {
      announceToScreenReader(`Error ${isEditing.value ? 'updating' : 'creating'} menu item`, 'assertive')
      throw error
    } finally {
      saving.value = false
    }
  })
}

const isValidUrl = string => {
  try {
    new URL(string)
    
    return true
  } catch (_) {
    return false
  }
}

// Watch for type changes to clear related fields
watch(() => form.value.type, (newType, oldType) => {
  if (newType !== oldType) {
    form.value.route_or_url = ''
    form.value.content_id = ''
    errors.value = {}
  }
})

// Initialize form with existing menu data
onMounted(() => {
  if (props.menu) {
    form.value = {
      name: props.menu.name || '',
      icon: props.menu.icon || '',
      parent_id: props.menu.parent_id || '',
      order: props.menu.order || '',
      type: props.menu.type || 'route',
      route_or_url: props.menu.route_or_url || '',
      content_id: props.menu.content_id || '',
      role_permissions_required: props.menu.role_permissions_required || '',
    }
  }
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background-color: rgba(0, 0, 0, 50%);
  inset: 0;
}

.modal-container {
  display: flex;
  overflow: hidden;
  flex-direction: column;
  border-radius: 12px;
  background: white;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 30%);
  inline-size: 100%;
  max-block-size: 90vh;
  max-inline-size: 600px;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-block-end: 1px solid #e9ecef;
  padding-block: 20px;
  padding-inline: 24px;
}

.modal-title {
  display: flex;
  align-items: center;
  margin: 0;
  color: #333;
  font-size: 20px;
  font-weight: 600;
  gap: 12px;
}

.modal-title i {
  color: #007bff;
}

.modal-close {
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 6px;
  background: none;
  block-size: 32px;
  color: #6c757d;
  cursor: pointer;
  inline-size: 32px;
  transition: all 0.2s ease;
}

.modal-close:hover {
  background-color: #f8f9fa;
  color: #333;
}

.modal-form {
  display: flex;
  flex: 1;
  flex-direction: column;
}

.modal-body {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
}

.form-section {
  margin-block-end: 32px;
}

.form-section:last-child {
  margin-block-end: 0;
}

.section-title {
  border-block-end: 1px solid #e9ecef;
  color: #333;
  font-size: 16px;
  font-weight: 600;
  margin-block: 0 16px;
  margin-inline: 0;
  padding-block-end: 8px;
}

.form-group {
  margin-block-end: 20px;
}

.form-label {
  display: block;
  color: #333;
  font-size: 14px;
  font-weight: 500;
  margin-block-end: 6px;
}

.form-label.required::after {
  color: #dc3545;
  content: " *";
}

.form-control {
  border: 1px solid #ced4da;
  border-radius: 6px;
  font-size: 14px;
  inline-size: 100%;
  padding-block: 10px;
  padding-inline: 12px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 25%);
  outline: none;
}

.form-control.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  display: block;
  color: #dc3545;
  font-size: 13px;
  margin-block-start: 4px;
}

.form-text {
  display: block;
  color: #6c757d;
  font-size: 12px;
  margin-block-start: 4px;
}

.icon-input-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.icon-input-group .form-control {
  flex: 1;
}

.icon-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #ced4da;
  border-radius: 6px;
  background-color: #f8f9fa;
  block-size: 40px;
  color: #6c757d;
  font-size: 16px;
  inline-size: 40px;
}

.no-icon {
  font-size: 10px;
  text-align: center;
}

.radio-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.radio-option {
  display: flex;
  align-items: flex-start;
  padding: 12px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  cursor: pointer;
  gap: 12px;
  transition: all 0.2s ease;
}

.radio-option:hover {
  border-color: #007bff;
  background-color: #f8f9fa;
}

.radio-option input[type="radio"] {
  margin-block-start: 2px;
}

.radio-option input[type="radio"]:checked + .radio-label {
  color: #007bff;
  font-weight: 500;
}

.radio-label {
  display: flex;
  flex: 1;
  align-items: center;
  color: #333;
  font-size: 14px;
  font-weight: 500;
  gap: 8px;
}

.radio-option small {
  display: block;
  color: #6c757d;
  font-size: 12px;
  margin-block-start: 4px;
}

.form-note {
  display: flex;
  align-items: flex-start;
  padding: 12px;
  border: 1px solid #bbdefb;
  border-radius: 6px;
  background-color: #e3f2fd;
  gap: 8px;
  margin-block-start: 8px;
}

.form-note i {
  color: #1976d2;
  margin-block-start: 2px;
}

.form-note span {
  color: #1565c0;
  font-size: 13px;
  line-height: 1.4;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  background-color: #f8f9fa;
  border-block-start: 1px solid #e9ecef;
  gap: 12px;
  padding-block: 20px;
  padding-inline: 24px;
}

.btn {
  display: inline-flex;
  align-items: center;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  gap: 8px;
  padding-block: 10px;
  padding-inline: 20px;
  transition: all 0.2s ease;
}

.btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background-color: #545b62;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
}

/* Responsive design */
@media (max-width: 768px) {
  .modal-overlay {
    padding: 10px;
  }

  .modal-container {
    max-block-size: 95vh;
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 16px;
  }

  .icon-input-group {
    flex-direction: column;
    align-items: stretch;
  }

  .icon-preview {
    align-self: center;
  }
}
</style>
