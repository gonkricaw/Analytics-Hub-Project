<template>
  <div
    v-if="show"
    class="modal-overlay"
    @click.self="$emit('close')"
  >
    <div class="modal-container">
      <div class="modal-header">
        <h2 class="modal-title">
          <i :class="isEditing ? 'fas fa-edit' : 'fas fa-plus'" />
          {{ isEditing ? 'Edit Content' : 'Create Content' }}
        </h2>
        <button
          class="modal-close"
          @click="$emit('close')"
        >
          <i class="fas fa-times" />
        </button>
      </div>

      <form
        class="modal-body"
        @submit.prevent="handleSubmit"
      >
        <!-- Basic Information -->
        <div class="form-section">
          <h3 class="section-title">
            Basic Information
          </h3>
          
          <div class="form-row">
            <div class="form-group">
              <label
                for="title"
                class="form-label"
              >Title *</label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                class="form-input"
                :class="{ 'is-invalid': errors.title }"
                placeholder="Enter content title"
                required
              >
              <div
                v-if="errors.title"
                class="form-error"
              >
                {{ errors.title[0] }}
              </div>
            </div>

            <div class="form-group">
              <label
                for="slug"
                class="form-label"
              >Slug *</label>
              <input
                id="slug"
                v-model="form.slug"
                type="text"
                class="form-input"
                :class="{ 'is-invalid': errors.slug }"
                placeholder="auto-generated-from-title"
                required
              >
              <div
                v-if="errors.slug"
                class="form-error"
              >
                {{ errors.slug[0] }}
              </div>
              <small class="form-help">Used in URLs. Leave empty to auto-generate from title.</small>
            </div>
          </div>

          <div class="form-group">
            <label
              for="type"
              class="form-label"
            >Content Type *</label>
            <select
              id="type"
              v-model="form.type"
              class="form-select"
              :class="{ 'is-invalid': errors.type }"
              required
            >
              <option value="">
                Select content type
              </option>
              <option value="custom">
                Custom HTML Content
              </option>
              <option value="embed_url">
                Embedded URL
              </option>
              <option value="file">
                File Upload
              </option>
              <option value="video">
                Video
              </option>
              <option value="image">
                Image
              </option>
            </select>
            <div
              v-if="errors.type"
              class="form-error"
            >
              {{ errors.type[0] }}
            </div>
          </div>
        </div>

        <!-- Content Type Specific Fields -->
        <div
          v-if="form.type === 'custom'"
          class="form-section"
        >
          <h3 class="section-title">
            Custom Content
          </h3>
          
          <div class="form-group">
            <label
              for="custom_content"
              class="form-label"
            >HTML Content</label>
            <div class="editor-container">
              <div class="editor-toolbar">
                <button
                  type="button"
                  class="editor-btn"
                  title="Bold"
                  @click="insertHtml('<strong>', '</strong>')"
                >
                  <i class="fas fa-bold" />
                </button>
                <button
                  type="button"
                  class="editor-btn"
                  title="Italic"
                  @click="insertHtml('<em>', '</em>')"
                >
                  <i class="fas fa-italic" />
                </button>
                <button
                  type="button"
                  class="editor-btn"
                  title="Heading"
                  @click="insertHtml('<h3>', '</h3>')"
                >
                  <i class="fas fa-heading" />
                </button>
                <button
                  type="button"
                  class="editor-btn"
                  title="List"
                  @click="insertHtml('<ul><li>', '</li></ul>')"
                >
                  <i class="fas fa-list-ul" />
                </button>
                <button
                  type="button"
                  class="editor-btn"
                  title="Link"
                  @click="insertHtml('<a href=\'\'>', '</a>')"
                >
                  <i class="fas fa-link" />
                </button>
              </div>
              <textarea
                id="custom_content"
                v-model="form.custom_content"
                class="form-textarea"
                :class="{ 'is-invalid': errors.custom_content }"
                rows="12"
                placeholder="Enter HTML content..."
              />
            </div>
            <div
              v-if="errors.custom_content"
              class="form-error"
            >
              {{ errors.custom_content[0] }}
            </div>
          </div>
        </div>

        <div
          v-if="form.type === 'embed_url'"
          class="form-section"
        >
          <h3 class="section-title">
            Embedded URL
          </h3>
          
          <div class="form-group">
            <label
              for="embed_url_original"
              class="form-label"
            >Original URL *</label>
            <input
              id="embed_url_original"
              v-model="form.embed_url_original"
              type="url"
              class="form-input"
              :class="{ 'is-invalid': errors.embed_url_original }"
              placeholder="https://example.com"
              required
            >
            <div
              v-if="errors.embed_url_original"
              class="form-error"
            >
              {{ errors.embed_url_original[0] }}
            </div>
          </div>

          <div class="form-group">
            <label
              for="embed_url_iframe"
              class="form-label"
            >iFrame URL</label>
            <input
              id="embed_url_iframe"
              v-model="form.embed_url_iframe"
              type="url"
              class="form-input"
              :class="{ 'is-invalid': errors.embed_url_iframe }"
              placeholder="https://example.com/embed"
            >
            <div
              v-if="errors.embed_url_iframe"
              class="form-error"
            >
              {{ errors.embed_url_iframe[0] }}
            </div>
            <small class="form-help">Optional: Use if the embed URL is different from the original URL</small>
          </div>
        </div>

        <div
          v-if="['file', 'video', 'image'].includes(form.type)"
          class="form-section"
        >
          <h3 class="section-title">
            File Upload
          </h3>
          
          <div class="form-group">
            <label
              for="file"
              class="form-label"
            >{{ getFileLabel() }} *</label>
            <div
              class="file-upload-area"
              :class="{ 'drag-over': isDragOver }" 
              @dragover.prevent="isDragOver = true"
              @dragleave.prevent="isDragOver = false"
              @drop.prevent="handleFileDrop"
            >
              <input
                id="file"
                ref="fileInput"
                type="file"
                class="file-input"
                :accept="getFileAccept()"
                hidden
                @change="handleFileSelect"
              >
              
              <div
                v-if="!selectedFile"
                class="file-upload-prompt"
                @click="$refs.fileInput.click()"
              >
                <i class="fas fa-cloud-upload-alt" />
                <p>Drop your {{ form.type }} here or click to browse</p>
                <small>{{ getFileHint() }}</small>
              </div>
              
              <div
                v-else
                class="file-upload-preview"
              >
                <div class="file-info">
                  <i :class="getFileIcon()" />
                  <span class="file-name">{{ selectedFile.name }}</span>
                  <span class="file-size">({{ formatFileSize(selectedFile.size) }})</span>
                </div>
                <button
                  type="button"
                  class="btn-remove"
                  title="Remove file"
                  @click="removeFile"
                >
                  <i class="fas fa-times" />
                </button>
              </div>
            </div>
            <div
              v-if="errors.file"
              class="form-error"
            >
              {{ errors.file[0] }}
            </div>
          </div>
        </div>

        <!-- Metadata -->
        <div class="form-section">
          <h3 class="section-title">
            Metadata
          </h3>
          
          <div class="form-row">
            <div class="form-group">
              <label
                for="meta_title"
                class="form-label"
              >Meta Title</label>
              <input
                id="meta_title"
                v-model="form.meta_title"
                type="text"
                class="form-input"
                :class="{ 'is-invalid': errors.meta_title }"
                placeholder="SEO title"
              >
              <div
                v-if="errors.meta_title"
                class="form-error"
              >
                {{ errors.meta_title[0] }}
              </div>
            </div>

            <div class="form-group">
              <label
                for="meta_keywords"
                class="form-label"
              >Meta Keywords</label>
              <input
                id="meta_keywords"
                v-model="form.meta_keywords"
                type="text"
                class="form-input"
                :class="{ 'is-invalid': errors.meta_keywords }"
                placeholder="keyword1, keyword2, keyword3"
              >
              <div
                v-if="errors.meta_keywords"
                class="form-error"
              >
                {{ errors.meta_keywords[0] }}
              </div>
            </div>
          </div>

          <div class="form-group">
            <label
              for="meta_description"
              class="form-label"
            >Meta Description</label>
            <textarea
              id="meta_description"
              v-model="form.meta_description"
              class="form-textarea"
              :class="{ 'is-invalid': errors.meta_description }"
              rows="3"
              placeholder="Brief description for search engines"
            />
            <div
              v-if="errors.meta_description"
              class="form-error"
            >
              {{ errors.meta_description[0] }}
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            @click="$emit('close')"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="saving"
          >
            <i
              v-if="saving"
              class="fas fa-spinner fa-spin"
            />
            <i
              v-else
              :class="isEditing ? 'fas fa-save' : 'fas fa-plus'"
            />
            {{ saving ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useContentStore } from '@/stores/content'
import { computed, nextTick, reactive, ref, watch } from 'vue'

// Props
const props = defineProps({
  content: {
    type: Object,
    default: null,
  },
  show: {
    type: Boolean,
    default: false,
  },
})

// Emits
const emit = defineEmits(['close', 'saved'])

// Store
const contentStore = useContentStore()

// Reactive data
const saving = ref(false)
const errors = ref({})
const selectedFile = ref(null)
const isDragOver = ref(false)
const fileInput = ref(null)

// Form data
const form = reactive({
  title: '',
  slug: '',
  type: '',
  custom_content: '',
  embed_url_original: '',
  embed_url_iframe: '',
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
  file: null,
})

// Computed
const isEditing = computed(() => !!props.content?.id)

// Methods
const handleSubmit = async () => {
  saving.value = true
  errors.value = {}

  try {
    const formData = new FormData()
    
    // Add form fields
    Object.keys(form).forEach(key => {
      if (form[key] !== null && form[key] !== '') {
        if (key === 'file' && selectedFile.value) {
          formData.append('file', selectedFile.value)
        } else if (key !== 'file') {
          formData.append(key, form[key])
        }
      }
    })

    let response
    if (isEditing.value) {
      response = await contentStore.updateContent(props.content.id, formData)
    } else {
      response = await contentStore.createContent(formData)
    }

    emit('saved', response.data)
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      console.error('Content save error:', err)
    }
  } finally {
    saving.value = false
  }
}

const handleFileSelect = event => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    form.file = file
  }
}

const handleFileDrop = event => {
  isDragOver.value = false

  const file = event.dataTransfer.files[0]
  if (file) {
    selectedFile.value = file
    form.file = file
  }
}

const removeFile = () => {
  selectedFile.value = null
  form.file = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const insertHtml = (startTag, endTag) => {
  const textarea = document.getElementById('custom_content')
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const selectedText = textarea.value.substring(start, end)
  const replacement = startTag + selectedText + endTag
  
  form.custom_content = 
    textarea.value.substring(0, start) + 
    replacement + 
    textarea.value.substring(end)
  
  nextTick(() => {
    textarea.focus()
    textarea.setSelectionRange(start + startTag.length, start + startTag.length + selectedText.length)
  })
}

const getFileLabel = () => {
  const labels = {
    file: 'File',
    video: 'Video File',
    image: 'Image File',
  }
  
  return labels[form.type] || 'File'
}

const getFileAccept = () => {
  const accepts = {
    video: 'video/*',
    image: 'image/*',
    file: '*/*',
  }
  
  return accepts[form.type] || '*/*'
}

const getFileHint = () => {
  const hints = {
    video: 'MP4, WebM, AVI files up to 100MB',
    image: 'JPEG, PNG, GIF files up to 10MB',
    file: 'Any file type up to 50MB',
  }
  
  return hints[form.type] || 'Any file type'
}

const getFileIcon = () => {
  if (!selectedFile.value) return 'fas fa-file'
  
  const type = selectedFile.value.type
  if (type.startsWith('video/')) return 'fas fa-video'
  if (type.startsWith('image/')) return 'fas fa-image'
  if (type.includes('pdf')) return 'fas fa-file-pdf'
  if (type.includes('word')) return 'fas fa-file-word'
  if (type.includes('excel') || type.includes('spreadsheet')) return 'fas fa-file-excel'
  
  return 'fas fa-file'
}

const formatFileSize = bytes => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const generateSlug = title => {
  return title
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim('-')
}

// Watchers
watch(() => props.content, newContent => {
  if (newContent) {
    Object.keys(form).forEach(key => {
      form[key] = newContent[key] || ''
    })
  } else {
    Object.keys(form).forEach(key => {
      form[key] = ''
    })
    selectedFile.value = null
  }
  errors.value = {}
}, { immediate: true, deep: true })

watch(() => form.title, newTitle => {
  if (newTitle && !isEditing.value && !form.slug) {
    form.slug = generateSlug(newTitle)
  }
})

watch(() => props.show, show => {
  if (!show) {
    errors.value = {}
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
  padding: 1rem;
  background: rgba(0, 0, 0, 70%);
  inset: 0;
}

.modal-container {
  overflow: hidden;
  border-radius: 12px;
  background: var(--v-theme-surface);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 30%);
  inline-size: 100%;
  max-block-size: 90vh;
  max-inline-size: 800px;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--v-theme-background);
  border-block-end: 1px solid var(--v-theme-surface-variant);
  padding-block: 1.5rem;
  padding-inline: 2rem;
}

.modal-title {
  display: flex;
  align-items: center;
  margin: 0;
  color: var(--v-theme-on-background);
  font-size: 1.5rem;
  font-weight: 700;
  gap: 0.75rem;
}

.modal-title i {
  color: var(--v-theme-primary);
}

.modal-close {
  padding: 0.5rem;
  border: none;
  border-radius: 6px;
  background: none;
  color: var(--v-theme-on-background-variant);
  cursor: pointer;
  font-size: 1.25rem;
  transition: all 0.2s ease;
}

.modal-close:hover {
  background: var(--v-theme-error);
  color: var(--v-theme-on-error);
}

.modal-body {
  padding: 2rem;
  max-block-size: calc(90vh - 140px);
  overflow-y: auto;
}

.form-section {
  margin-block-end: 2rem;
}

.section-title {
  border-block-end: 1px solid var(--v-theme-surface-variant);
  color: var(--v-theme-on-surface);
  font-size: 1.2rem;
  font-weight: 600;
  margin-block: 0 1rem;
  margin-inline: 0;
  padding-block-end: 0.5rem;
}

.form-row {
  display: grid;
  gap: 1rem;
  grid-template-columns: 1fr 1fr;
}

.form-group {
  margin-block-end: 1rem;
}

.form-label {
  display: block;
  color: var(--v-theme-on-surface);
  font-weight: 500;
  margin-block-end: 0.5rem;
}

.form-input,
.form-select,
.form-textarea {
  padding: 0.75rem;
  border: 1px solid var(--v-theme-surface-variant);
  border-radius: 8px;
  background: var(--v-theme-background);
  color: var(--v-theme-on-background);
  font-size: 1rem;
  inline-size: 100%;
  transition: border-color 0.2s ease;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  border-color: var(--v-theme-primary);
  box-shadow: 0 0 0 2px rgba(var(--v-theme-primary-rgb), 0.2);
  outline: none;
}

.form-input.is-invalid,
.form-select.is-invalid,
.form-textarea.is-invalid {
  border-color: var(--v-theme-error);
}

.form-error {
  color: var(--v-theme-error);
  font-size: 0.875rem;
  margin-block-start: 0.25rem;
}

.form-help {
  display: block;
  color: var(--v-theme-on-surface-variant);
  font-size: 0.875rem;
  margin-block-start: 0.25rem;
}

.editor-container {
  overflow: hidden;
  border: 1px solid var(--v-theme-surface-variant);
  border-radius: 8px;
}

.editor-toolbar {
  display: flex;
  padding: 0.5rem;
  background: var(--v-theme-surface-variant);
  border-block-end: 1px solid var(--v-theme-surface-variant);
  gap: 0.25rem;
}

.editor-btn {
  padding: 0.5rem;
  border: 1px solid transparent;
  border-radius: 4px;
  background: transparent;
  color: var(--v-theme-on-surface-variant);
  cursor: pointer;
  transition: all 0.2s ease;
}

.editor-btn:hover {
  border-color: var(--v-theme-primary);
  background: var(--v-theme-primary);
  color: var(--v-theme-on-primary);
}

.file-upload-area {
  padding: 2rem;
  border: 2px dashed var(--v-theme-surface-variant);
  border-radius: 8px;
  cursor: pointer;
  text-align: center;
  transition: all 0.2s ease;
}

.file-upload-area:hover,
.file-upload-area.drag-over {
  border-color: var(--v-theme-primary);
  background: rgba(var(--v-theme-primary-rgb), 0.05);
}

.file-upload-prompt i {
  color: var(--v-theme-primary);
  font-size: 3rem;
  margin-block-end: 1rem;
}

.file-upload-prompt p {
  color: var(--v-theme-on-surface);
  font-size: 1.1rem;
  margin-block: 0 0.5rem;
  margin-inline: 0;
}

.file-upload-prompt small {
  color: var(--v-theme-on-surface-variant);
}

.file-upload-preview {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-radius: 8px;
  background: var(--v-theme-background);
}

.file-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.file-info i {
  color: var(--v-theme-primary);
  font-size: 1.5rem;
}

.file-name {
  color: var(--v-theme-on-background);
  font-weight: 500;
}

.file-size {
  color: var(--v-theme-on-background-variant);
  font-size: 0.9rem;
}

.btn-remove {
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 50%;
  background: var(--v-theme-error);
  block-size: 32px;
  color: var(--v-theme-on-error);
  cursor: pointer;
  inline-size: 32px;
  transition: all 0.2s ease;
}

.btn-remove:hover {
  background: var(--v-theme-error-darken-1);
  transform: scale(1.1);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  background: var(--v-theme-background);
  border-block-start: 1px solid var(--v-theme-surface-variant);
  gap: 1rem;
  padding-block: 1.5rem;
  padding-inline: 2rem;
}

.btn {
  display: flex;
  align-items: center;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  gap: 0.5rem;
  padding-block: 0.75rem;
  padding-inline: 1.5rem;
  transition: all 0.2s ease;
}

.btn-primary {
  background: var(--v-theme-primary);
  color: var(--v-theme-on-primary);
}

.btn-primary:hover:not(:disabled) {
  background: var(--v-theme-primary-darken-1);
  transform: translateY(-1px);
}

.btn-secondary {
  background: var(--v-theme-surface-variant);
  color: var(--v-theme-on-surface-variant);
}

.btn-secondary:hover {
  background: var(--v-theme-surface-variant-darken-1);
}

.btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-container {
    border-radius: 0;
    margin: 0;
    max-block-size: 100vh;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding-inline: 1rem;
  }
}
</style>
