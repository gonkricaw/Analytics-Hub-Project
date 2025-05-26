<template>
  <div
    v-if="show"
    class="modal-overlay"
    @click.self="$emit('close')"
  >
    <div class="modal-container">
      <div class="modal-header">
        <div class="header-info">
          <h2 class="modal-title">
            <i :class="getContentTypeIcon(content?.type)" />
            {{ content?.title }}
          </h2>
          <div class="content-meta">
            <span
              class="content-type-badge"
              :class="`type-${content?.type}`"
            >
              {{ content?.type }}
            </span>
            <span class="content-slug">{{ content?.slug }}</span>
          </div>
        </div>
        <button
          class="modal-close"
          @click="$emit('close')"
        >
          <i class="fas fa-times" />
        </button>
      </div>

      <div class="modal-body">
        <!-- Custom HTML Content -->
        <div
          v-if="content?.type === 'custom'"
          class="content-preview custom-content"
        >
          <div
            class="html-content"
            v-html="content.custom_content"
          />
        </div>

        <!-- Embedded URL Content -->
        <div
          v-else-if="content?.type === 'embed_url'"
          class="content-preview embed-content"
        >
          <div class="embed-info">
            <div class="url-display">
              <label>Original URL:</label>
              <a
                :href="content.embed_url_original"
                target="_blank"
                class="url-link"
              >
                <i class="fas fa-external-link-alt" />
                {{ content.embed_url_original }}
              </a>
            </div>
            <div
              v-if="content.embed_url_iframe"
              class="url-display"
            >
              <label>Embed URL:</label>
              <a
                :href="content.embed_url_iframe"
                target="_blank"
                class="url-link"
              >
                <i class="fas fa-external-link-alt" />
                {{ content.embed_url_iframe }}
              </a>
            </div>
          </div>
          
          <div
            v-if="content.embed_url_iframe || content.embed_url_original"
            class="iframe-container"
          >
            <iframe 
              :src="content.embed_url_iframe || content.embed_url_original"
              class="embed-iframe"
              frameborder="0"
              allowfullscreen
              sandbox="allow-scripts allow-same-origin allow-forms"
            />
          </div>
        </div>

        <!-- File Content -->
        <div
          v-else-if="['file', 'video', 'image'].includes(content?.type)"
          class="content-preview file-content"
        >
          <div class="file-info">
            <div class="file-header">
              <i :class="getFileIcon(content)" />
              <div class="file-details">
                <span class="file-name">{{ content.file_original_name || 'No file' }}</span>
                <span
                  v-if="content.file_size"
                  class="file-size"
                >{{ formatFileSize(content.file_size) }}</span>
              </div>
            </div>
            
            <div
              v-if="content.file_path"
              class="file-actions"
            >
              <a
                :href="getFileUrl(content)"
                target="_blank"
                class="btn btn-primary"
              >
                <i class="fas fa-download" />
                Download
              </a>
              <a
                :href="getFileUrl(content)"
                target="_blank"
                class="btn btn-secondary"
              >
                <i class="fas fa-external-link-alt" />
                Open
              </a>
            </div>
          </div>

          <!-- Image Preview -->
          <div
            v-if="content.type === 'image' && content.file_path"
            class="image-preview"
          >
            <img
              :src="getFileUrl(content)"
              :alt="content.title"
              class="preview-image"
            >
          </div>

          <!-- Video Preview -->
          <div
            v-else-if="content.type === 'video' && content.file_path"
            class="video-preview"
          >
            <video
              controls
              class="preview-video"
            >
              <source
                :src="getFileUrl(content)"
                :type="content.file_mime_type"
              >
              Your browser does not support the video tag.
            </video>
          </div>
        </div>

        <!-- Metadata Section -->
        <div
          v-if="hasMetadata"
          class="metadata-section"
        >
          <h3 class="section-title">
            <i class="fas fa-tags" />
            Metadata
          </h3>
          
          <div class="metadata-grid">
            <div
              v-if="content?.meta_title"
              class="meta-item"
            >
              <label>Meta Title:</label>
              <span>{{ content.meta_title }}</span>
            </div>
            
            <div
              v-if="content?.meta_description"
              class="meta-item"
            >
              <label>Meta Description:</label>
              <span>{{ content.meta_description }}</span>
            </div>
            
            <div
              v-if="content?.meta_keywords"
              class="meta-item"
            >
              <label>Meta Keywords:</label>
              <span>{{ content.meta_keywords }}</span>
            </div>
          </div>
        </div>

        <!-- Content Information -->
        <div class="info-section">
          <h3 class="section-title">
            <i class="fas fa-info-circle" />
            Information
          </h3>
          
          <div class="info-grid">
            <div class="info-item">
              <label>Created By:</label>
              <span>{{ content?.created_by?.name || 'System' }}</span>
            </div>
            
            <div class="info-item">
              <label>Created At:</label>
              <span>{{ formatDate(content?.created_at) }}</span>
            </div>
            
            <div
              v-if="content?.updated_at !== content?.created_at"
              class="info-item"
            >
              <label>Last Updated:</label>
              <span>{{ formatDate(content?.updated_at) }}</span>
            </div>
            
            <div
              v-if="content?.updated_by && content?.updated_by?.id !== content?.created_by?.id"
              class="info-item"
            >
              <label>Updated By:</label>
              <span>{{ content.updated_by.name }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button
          type="button"
          class="btn btn-secondary"
          @click="$emit('close')"
        >
          <i class="fas fa-times" />
          Close
        </button>
        <a
          v-if="getViewUrl()"
          :href="getViewUrl()"
          target="_blank"
          class="btn btn-primary"
        >
          <i class="fas fa-external-link-alt" />
          View Live
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

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
const emit = defineEmits(['close'])

// Computed
const hasMetadata = computed(() => {
  return props.content?.meta_title || 
         props.content?.meta_description || 
         props.content?.meta_keywords
})

// Methods
const getContentTypeIcon = type => {
  const icons = {
    'custom': 'fas fa-file-alt',
    'embed_url': 'fas fa-external-link-alt',
    'file': 'fas fa-file',
    'video': 'fas fa-video',
    'image': 'fas fa-image',
  }
  
  return icons[type] || 'fas fa-file'
}

const getFileIcon = content => {
  if (!content) return 'fas fa-file'
  
  const type = content.file_mime_type || ''
  if (type.startsWith('video/')) return 'fas fa-video'
  if (type.startsWith('image/')) return 'fas fa-image'
  if (type.includes('pdf')) return 'fas fa-file-pdf'
  if (type.includes('word')) return 'fas fa-file-word'
  if (type.includes('excel') || type.includes('spreadsheet')) return 'fas fa-file-excel'
  
  return 'fas fa-file'
}

const formatFileSize = bytes => {
  if (!bytes || bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatDate = date => {
  if (!date) return 'N/A'
  
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getFileUrl = content => {
  if (!content?.file_path) return null
  
  return `/storage/${content.file_path}`
}

const getViewUrl = () => {
  if (!props.content?.slug) return null
  
  return `/content/${props.content.slug}`
}
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
  max-inline-size: 1000px;
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  background: var(--v-theme-background);
  border-block-end: 1px solid var(--v-theme-surface-variant);
  padding-block: 1.5rem;
  padding-inline: 2rem;
}

.header-info {
  flex: 1;
}

.modal-title {
  display: flex;
  align-items: center;
  color: var(--v-theme-on-background);
  font-size: 1.5rem;
  font-weight: 700;
  gap: 0.75rem;
  margin-block: 0 0.5rem;
  margin-inline: 0;
}

.modal-title i {
  color: var(--v-theme-primary);
}

.content-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.content-type-badge {
  display: inline-flex;
  align-items: center;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 500;
  padding-block: 0.25rem;
  padding-inline: 0.75rem;
  text-transform: uppercase;
}

.content-type-badge.type-custom {
  background: rgba(var(--v-theme-primary-rgb), 0.1);
  color: var(--v-theme-primary);
}

.content-type-badge.type-embed_url {
  background: rgba(var(--v-theme-secondary-rgb), 0.1);
  color: var(--v-theme-secondary);
}

.content-type-badge.type-file,
.content-type-badge.type-video,
.content-type-badge.type-image {
  background: rgba(var(--v-theme-info-rgb), 0.1);
  color: var(--v-theme-info);
}

.content-slug {
  border-radius: 4px;
  background: var(--v-theme-surface-variant);
  color: var(--v-theme-on-background-variant);
  font-family: monospace;
  font-size: 0.85rem;
  padding-block: 0.25rem;
  padding-inline: 0.5rem;
}

.modal-close {
  flex-shrink: 0;
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
  max-block-size: calc(90vh - 180px);
  overflow-y: auto;
}

.content-preview {
  margin-block-end: 2rem;
}

.html-content {
  padding: 2rem;
  border: 1px solid var(--v-theme-surface-variant);
  border-radius: 8px;
  background: var(--v-theme-background);
  line-height: 1.6;
}

.html-content h1,
.html-content h2,
.html-content h3,
.html-content h4,
.html-content h5,
.html-content h6 {
  color: var(--v-theme-on-background);
  margin-block: 1.5rem 1rem;
}

.html-content h1:first-child,
.html-content h2:first-child,
.html-content h3:first-child {
  margin-block-start: 0;
}

.html-content p {
  color: var(--v-theme-on-background);
  margin-block-end: 1rem;
}

.html-content ul,
.html-content ol {
  margin-block-end: 1rem;
  padding-inline-start: 2rem;
}

.html-content a {
  color: var(--v-theme-primary);
  text-decoration: none;
}

.html-content a:hover {
  text-decoration: underline;
}

.embed-info {
  margin-block-end: 2rem;
}

.url-display {
  margin-block-end: 1rem;
}

.url-display label {
  display: block;
  color: var(--v-theme-on-surface);
  font-weight: 500;
  margin-block-end: 0.25rem;
}

.url-link {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem;
  border-radius: 6px;
  background: var(--v-theme-surface-variant);
  color: var(--v-theme-primary);
  font-family: monospace;
  gap: 0.5rem;
  text-decoration: none;
  word-break: break-all;
}

.url-link:hover {
  text-decoration: underline;
}

.iframe-container {
  position: relative;
  overflow: hidden;
  border: 1px solid var(--v-theme-surface-variant);
  border-radius: 8px;
  block-size: 500px;
  inline-size: 100%;
}

.embed-iframe {
  border: none;
  block-size: 100%;
  inline-size: 100%;
}

.file-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem;
  border: 1px solid var(--v-theme-surface-variant);
  border-radius: 8px;
  background: var(--v-theme-background);
  margin-block-end: 2rem;
}

.file-header {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.file-header i {
  color: var(--v-theme-primary);
  font-size: 2rem;
}

.file-details {
  display: flex;
  flex-direction: column;
}

.file-name {
  color: var(--v-theme-on-background);
  font-size: 1.1rem;
  font-weight: 500;
}

.file-size {
  color: var(--v-theme-on-background-variant);
  font-size: 0.9rem;
}

.file-actions {
  display: flex;
  gap: 0.75rem;
}

.image-preview {
  text-align: center;
}

.preview-image {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 10%);
  max-block-size: 500px;
  max-inline-size: 100%;
}

.video-preview {
  text-align: center;
}

.preview-video {
  border-radius: 8px;
  max-block-size: 500px;
  max-inline-size: 100%;
}

.metadata-section,
.info-section {
  margin-block-end: 2rem;
}

.section-title {
  display: flex;
  align-items: center;
  border-block-end: 1px solid var(--v-theme-surface-variant);
  color: var(--v-theme-on-surface);
  font-size: 1.2rem;
  font-weight: 600;
  gap: 0.5rem;
  margin-block: 0 1rem;
  margin-inline: 0;
  padding-block-end: 0.5rem;
}

.section-title i {
  color: var(--v-theme-primary);
}

.metadata-grid,
.info-grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

.meta-item,
.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.meta-item label,
.info-item label {
  color: var(--v-theme-on-surface-variant);
  font-size: 0.9rem;
  font-weight: 500;
}

.meta-item span,
.info-item span {
  color: var(--v-theme-on-surface);
  word-break: break-word;
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
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-primary {
  background: var(--v-theme-primary);
  color: var(--v-theme-on-primary);
}

.btn-primary:hover {
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

/* Responsive */
@media (max-width: 768px) {
  .modal-container {
    border-radius: 0;
    margin: 0;
    max-block-size: 100vh;
  }

  .content-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .file-info {
    flex-direction: column;
    gap: 1rem;
  }

  .file-actions {
    justify-content: center;
    inline-size: 100%;
  }

  .metadata-grid,
  .info-grid {
    grid-template-columns: 1fr;
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding-inline: 1rem;
  }
}
</style>
