<script setup>
import { useIconSystem } from '@/composables/useIconSystem'

const props = defineProps({
  notifications: {
    type: Array,
    default: () => [],
  },
})

const router = useRouter()
const { getStatusIcon, getNavigationIcon } = useIconSystem()

const navigateToNotification = notification => {
  // Navigate to notification detail page
  router.push(`/notifications/${notification.id}`)
}
</script>

<template>
  <VCard class="notifications-widget">
    <VCardTitle class="d-flex align-center justify-space-between">
      <div class="d-flex align-center">
        <VIcon
          :icon="getStatusIcon('info')"
          class="me-3"
          color="primary"
        />
        Recent Notifications
      </div>
      <VBtn
        variant="text"
        color="primary"
        size="small"
        to="/notifications"
      >
        View All
        <VIcon
          :icon="getNavigationIcon('external')"
          size="14"
          class="ms-1"
        />
      </VBtn>
    </VCardTitle>
    
    <VCardText class="pa-0">
      <div
        v-if="notifications.length === 0"
        class="empty-state pa-6 text-center"
      >
        <VIcon
          :icon="getStatusIcon('info')"
          size="48"
          color="surface-variant"
          class="mb-3"
        />
        <p class="text-body-1 text-medium-emphasis">
          No recent notifications
        </p>
      </div>
      
      <VList
        v-else
        class="notification-list"
      >
        <VListItem
          v-for="(notification, index) in notifications"
          :key="notification.id"
          class="notification-item"
          @click="navigateToNotification(notification)"
        >
          <template #prepend>
            <VAvatar
              color="primary"
              variant="tonal"
              size="40"
            >
              <VIcon
                :icon="getStatusIcon('info')"
                size="20"
              />
            </VAvatar>
          </template>
          
          <VListItemTitle class="notification-title">
            {{ notification.title }}
          </VListItemTitle>
          
          <VListItemSubtitle class="notification-time">
            {{ notification.created_at }}
          </VListItemSubtitle>
          
          <template #append>
            <VIcon
              :icon="getNavigationIcon('expand')"
              size="14"
              color="surface-variant"
            />
          </template>
          
          <VDivider
            v-if="index < notifications.length - 1"
            class="mt-3"
          />
        </VListItem>
      </VList>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.notifications-widget {
  height: 100%;
  transition: all 0.3s ease;
  
  &:hover {
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }
}

.notification-list {
  max-height: 320px;
  overflow-y: auto;
  
  &::-webkit-scrollbar {
    width: 4px;
  }
  
  &::-webkit-scrollbar-track {
    background: rgba(var(--v-theme-surface-variant), 0.3);
  }
  
  &::-webkit-scrollbar-thumb {
    background: rgba(var(--v-theme-primary), 0.3);
    border-radius: 2px;
    
    &:hover {
      background: rgba(var(--v-theme-primary), 0.5);
    }
  }
}

.notification-item {
  cursor: pointer;
  transition: all 0.2s ease;
  border-radius: 8px;
  margin: 0 8px;
  
  &:hover {
    background: rgba(var(--v-theme-primary), 0.05);
    transform: translateX(4px);
  }
}

.notification-title {
  font-weight: 500;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.notification-time {
  font-size: 0.75rem;
  color: rgb(var(--v-theme-on-surface-variant));
  margin-top: 0.25rem;
}

.empty-state {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

// Responsive adjustments
@media (max-width: 768px) {
  .notification-list {
    max-height: 250px;
  }
  
  .notification-title {
    font-size: 0.875rem;
  }
  
  .notification-time {
    font-size: 0.7rem;
  }
}
</style>
