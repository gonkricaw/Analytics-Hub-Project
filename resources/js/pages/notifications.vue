<template>
  <div class="notifications-page">
    <!-- Page Header -->
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h1 class="text-h4 mb-1">
          <VIcon
            icon="tabler-bell"
            class="me-3"
          />
          Notifications
        </h1>
        <p class="text-body-1 text-medium-emphasis mb-0">
          Manage your notifications and stay updated
        </p>
      </div>
      
      <div class="d-flex align-center gap-3">
        <VChip
          v-if="notificationStore.unreadCount > 0"
          color="primary"
          variant="tonal"
        >
          {{ notificationStore.unreadCount }} Unread
        </VChip>
        
        <VBtn
          color="primary"
          variant="outlined"
          :loading="notificationStore.markingRead"
          :disabled="notificationStore.unreadCount === 0"
          @click="markAllAsRead"
        >
          <VIcon
            start
            icon="tabler-check"
          />
          Mark All Read
        </VBtn>
        
        <VBtn
          color="default"
          variant="outlined"
          :loading="notificationStore.loading"
          @click="refreshNotifications"
        >
          <VIcon
            start
            icon="tabler-refresh"
          />
          Refresh
        </VBtn>
      </div>
    </div>

    <!-- Filters -->
    <VCard class="mb-6">
      <VCardText class="pb-4">
        <VRow align="center">
          <VCol
            cols="12"
            md="6"
            lg="4"
          >
            <VSelect
              v-model="filterType"
              :items="filterOptions"
              label="Filter by Status"
              variant="outlined"
              density="compact"
              @update:model-value="applyFilters"
            />
          </VCol>
          
          <VCol
            cols="12"
            md="6"
            lg="4"
          >
            <VSelect
              v-model="perPage"
              :items="perPageOptions"
              label="Items per page"
              variant="outlined"
              density="compact"
              @update:model-value="applyFilters"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Notifications List -->
    <VCard>
      <VCardText class="pa-0">
        <!-- Loading State -->
        <div
          v-if="notificationStore.loading && !notificationStore.notifications.length"
          class="text-center py-12"
        >
          <VProgressCircular
            indeterminate
            size="48"
            color="primary"
            class="mb-4"
          />
          <p class="text-body-1 text-medium-emphasis">
            Loading notifications...
          </p>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="!notificationStore.notifications.length"
          class="text-center py-12"
        >
          <VIcon
            icon="tabler-bell-off"
            size="64"
            color="disabled"
            class="mb-4"
          />
          <h3 class="text-h6 mb-2">
            No notifications found
          </h3>
          <p class="text-body-1 text-medium-emphasis">
            You're all caught up! New notifications will appear here.
          </p>
        </div>

        <!-- Notifications List -->
        <div v-else>
          <VList class="pa-0">
            <template
              v-for="(notification, index) in notificationStore.notifications"
              :key="notification.id"
            >
              <VDivider v-if="index > 0" />
              
              <VListItem
                class="notification-item"
                :class="{ 'notification-unread': !notification.isSeen }"
                @click="openNotificationDetail(notification)"
              >
                <template #prepend>
                  <VAvatar
                    :color="notification.isSeen ? 'default' : 'primary'"
                    variant="tonal"
                    size="40"
                  >
                    <VIcon icon="tabler-bell" />
                  </VAvatar>
                </template>

                <VListItemTitle class="text-subtitle-1 mb-1">
                  {{ notification.title }}
                </VListItemTitle>
                
                <VListItemSubtitle class="text-body-2 mb-2">
                  {{ notification.content || notification.subtitle }}
                </VListItemSubtitle>
                
                <VListItemSubtitle class="text-caption text-medium-emphasis">
                  {{ notification.time }}
                </VListItemSubtitle>

                <template #append>
                  <div class="d-flex flex-column align-end gap-2">
                    <VChip
                      :color="notification.isSeen ? 'default' : 'primary'"
                      size="x-small"
                      variant="dot"
                    >
                      {{ notification.isSeen ? 'Read' : 'Unread' }}
                    </VChip>
                    
                    <div class="d-flex gap-1">
                      <VBtn
                        v-if="!notification.isSeen"
                        icon
                        size="small"
                        variant="text"
                        color="primary"
                        @click.stop="markAsRead(notification.id)"
                      >
                        <VIcon
                          icon="tabler-check"
                          size="16"
                        />
                        <VTooltip
                          activator="parent"
                          location="top"
                        >
                          Mark as Read
                        </VTooltip>
                      </VBtn>
                      
                      <VBtn
                        v-else
                        icon
                        size="small"
                        variant="text"
                        color="default"
                        @click.stop="markAsUnread(notification.id)"
                      >
                        <VIcon
                          icon="tabler-mail"
                          size="16"
                        />
                        <VTooltip
                          activator="parent"
                          location="top"
                        >
                          Mark as Unread
                        </VTooltip>
                      </VBtn>
                      
                      <VBtn
                        icon
                        size="small"
                        variant="text"
                        color="error"
                        @click.stop="removeNotification(notification.id)"
                      >
                        <VIcon
                          icon="tabler-trash"
                          size="16"
                        />
                        <VTooltip
                          activator="parent"
                          location="top"
                        >
                          Remove
                        </VTooltip>
                      </VBtn>
                    </div>
                  </div>
                </template>
              </VListItem>
            </template>
          </VList>

          <!-- Pagination -->
          <div
            v-if="notificationStore.pagination.last_page > 1"
            class="d-flex justify-center pa-4"
          >
            <VPagination
              v-model="currentPage"
              :length="notificationStore.pagination.last_page"
              :total-visible="7"
              @update:model-value="changePage"
            />
          </div>

          <!-- Load More Button (Alternative to pagination) -->
          <div
            v-else-if="notificationStore.pagination.current_page < notificationStore.pagination.last_page"
            class="text-center pa-4"
          >
            <VBtn
              variant="outlined"
              :loading="notificationStore.loading"
              @click="loadMore"
            >
              <VIcon
                start
                icon="tabler-plus"
              />
              Load More
            </VBtn>
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- Notification Detail Modal -->
    <NotificationDetailModal
      v-model:visible="showDetailModal"
      :notification="selectedNotification"
      @mark-read="handleMarkAsRead"
      @mark-unread="handleMarkAsUnread"
      @remove="handleRemoveNotification"
    />
  </div>
</template>

<script setup>
import NotificationDetailModal from '@/components/NotificationDetailModal.vue'
import { useFlashNotifications } from '@/composables/useFlashNotifications'
import { useNotificationStore } from '@/stores/notificationStore'
import { onMounted, ref } from 'vue'

// Meta
definePageMeta({
  title: 'Notifications',
  requiresAuth: true,
})

// Composables
const notificationStore = useNotificationStore()
const { showSuccess, showError } = useFlashNotifications()

// State
const showDetailModal = ref(false)
const selectedNotification = ref(null)
const filterType = ref('all')
const perPage = ref(15)
const currentPage = ref(1)

// Options
const filterOptions = [
  { title: 'All Notifications', value: 'all' },
  { title: 'Unread Only', value: 'unread' },
  { title: 'Read Only', value: 'read' },
]

const perPageOptions = [
  { title: '10 per page', value: 10 },
  { title: '15 per page', value: 15 },
  { title: '25 per page', value: 25 },
  { title: '50 per page', value: 50 },
]

// Methods
const refreshNotifications = async () => {
  try {
    await notificationStore.fetchNotifications()
    showSuccess('Notifications refreshed')
  } catch (error) {
    showError('Failed to refresh notifications')
  }
}

const markAllAsRead = async () => {
  try {
    await notificationStore.markAllAsRead()
    showSuccess('All notifications marked as read')
  } catch (error) {
    showError('Failed to mark notifications as read')
  }
}

const markAsRead = async notificationId => {
  try {
    await notificationStore.markAsRead(notificationId)
  } catch (error) {
    showError('Failed to mark notification as read')
  }
}

const markAsUnread = async notificationId => {
  try {
    await notificationStore.markAsUnread(notificationId)
  } catch (error) {
    showError('Failed to mark notification as unread')
  }
}

const removeNotification = async notificationId => {
  try {
    await notificationStore.removeNotification(notificationId)
    showSuccess('Notification removed')
  } catch (error) {
    showError('Failed to remove notification')
  }
}

const openNotificationDetail = notification => {
  selectedNotification.value = notification
  showDetailModal.value = true
}

const handleMarkAsRead = async notificationId => {
  await markAsRead(notificationId)

  // Update selected notification
  if (selectedNotification.value?.id === notificationId) {
    selectedNotification.value.isSeen = true
  }
}

const handleMarkAsUnread = async notificationId => {
  await markAsUnread(notificationId)

  // Update selected notification
  if (selectedNotification.value?.id === notificationId) {
    selectedNotification.value.isSeen = false
  }
}

const handleRemoveNotification = async notificationId => {
  await removeNotification(notificationId)
}

const applyFilters = async () => {
  const filters = {
    per_page: perPage.value,
  }

  if (filterType.value === 'unread') {
    filters.only_unread = true
  } else if (filterType.value === 'read') {
    filters.only_unread = false
  }

  notificationStore.setFilter('per_page', filters.per_page)
  if (filters.only_unread !== undefined) {
    notificationStore.setFilter('only_unread', filters.only_unread)
  }

  currentPage.value = 1
  await notificationStore.fetchNotifications()
}

const changePage = async page => {
  currentPage.value = page
  await notificationStore.fetchNotifications({ page })
}

const loadMore = async () => {
  try {
    await notificationStore.loadMore()
  } catch (error) {
    showError('Failed to load more notifications')
  }
}

// Initialize
onMounted(async () => {
  try {
    await notificationStore.fetchNotifications()
  } catch (error) {
    showError('Failed to load notifications')
  }
})
</script>

<style scoped>
.notifications-page {
  padding: 24px;
  margin-block: 0;
  margin-inline: auto;
  max-inline-size: 1200px;
}

.notification-item {
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.notification-item:hover {
  background-color: rgba(var(--v-theme-on-surface), 0.04);
}

.notification-unread {
  background-color: rgba(var(--v-theme-primary), 0.02);
  border-inline-start: 4px solid rgb(var(--v-theme-primary));
}

.notification-unread:hover {
  background-color: rgba(var(--v-theme-primary), 0.06);
}

@media (max-width: 960px) {
  .notifications-page {
    padding: 16px;
  }
}
</style>
