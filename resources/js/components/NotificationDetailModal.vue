<template>
  <VDialog
    :model-value="visible"
    max-width="600px"
    persistent
    class="notification-detail-modal"
    @update:model-value="handleClose"
  >
    <VCard>
      <VCardTitle class="d-flex align-center justify-space-between pa-6">
        <div class="d-flex align-center gap-3">
          <VIcon
            icon="tabler-bell"
            :color="notification?.isSeen ? 'default' : 'primary'"
            size="24"
          />
          <span class="text-h6">Notification Details</span>
        </div>
        
        <VBtn
          variant="text"
          icon="tabler-x"
          size="small"
          @click="handleClose"
        />
      </VCardTitle>

      <VDivider />

      <VCardText class="pa-6">
        <div v-if="notification">
          <!-- Notification Title -->
          <div class="mb-4">
            <div class="d-flex align-center justify-space-between mb-2">
              <h3 class="text-h6 mb-0">
                {{ notification.title }}
              </h3>
              <VChip
                :color="notification.isSeen ? 'default' : 'primary'"
                size="small"
                variant="tonal"
              >
                {{ notification.isSeen ? 'Read' : 'Unread' }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0">
              {{ notification.time }}
            </p>
          </div>

          <!-- Notification Content -->
          <div class="mb-6">
            <VAlert
              variant="tonal"
              color="info"
              class="mb-0"
            >
              <div class="text-body-1">
                {{ notification.content || notification.subtitle }}
              </div>
            </VAlert>
          </div>

          <!-- Notification Actions -->
          <div class="d-flex gap-3 flex-wrap">
            <VBtn
              v-if="!notification.isSeen"
              color="primary"
              variant="flat"
              :loading="markingAsRead"
              @click="markAsRead"
            >
              <VIcon
                start
                icon="tabler-check"
              />
              Mark as Read
            </VBtn>
            
            <VBtn
              v-else
              color="default"
              variant="outlined"
              :loading="markingAsRead"
              @click="markAsUnread"
            >
              <VIcon
                start
                icon="tabler-mail"
              />
              Mark as Unread
            </VBtn>

            <VBtn
              color="error"
              variant="outlined"
              :loading="removing"
              @click="removeNotification"
            >
              <VIcon
                start
                icon="tabler-trash"
              />
              Remove
            </VBtn>
          </div>
        </div>

        <div
          v-else
          class="text-center py-8"
        >
          <VIcon
            icon="tabler-bell-off"
            size="48"
            color="disabled"
            class="mb-4"
          />
          <p class="text-body-1 text-disabled">
            No notification selected
          </p>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref } from 'vue'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  notification: {
    type: Object,
    default: null,
  },
})

// Emits
const emit = defineEmits([
  'update:visible',
  'close',
  'mark-read',
  'mark-unread',
  'remove',
])

// Loading states
const markingAsRead = ref(false)
const removing = ref(false)

// Methods
const handleClose = () => {
  emit('update:visible', false)
  emit('close')
}

const markAsRead = async () => {
  if (!props.notification) return
  
  try {
    markingAsRead.value = true
    emit('mark-read', props.notification.id)
  } finally {
    markingAsRead.value = false
  }
}

const markAsUnread = async () => {
  if (!props.notification) return
  
  try {
    markingAsRead.value = true
    emit('mark-unread', props.notification.id)
  } finally {
    markingAsRead.value = false
  }
}

const removeNotification = async () => {
  if (!props.notification) return
  
  try {
    removing.value = true
    emit('remove', props.notification.id)
    handleClose()
  } finally {
    removing.value = false
  }
}
</script>

<style scoped>
.notification-detail-modal {
  z-index: 9999;
}

.notification-detail-modal :deep(.v-card) {
  overflow: visible;
}

.notification-detail-modal :deep(.v-card-title) {
  border-block-end: 1px solid rgb(var(--v-theme-on-surface), 0.12);
}

@media (max-width: 600px) {
  .notification-detail-modal :deep(.v-dialog) {
    margin: 16px;
  }

  .notification-detail-modal :deep(.v-card) {
    inline-size: 100%;
    max-inline-size: none !important;
  }
}
</style>
