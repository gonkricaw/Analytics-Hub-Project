<script setup>
const props = defineProps({
  users: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <VCard class="online-users-widget">
    <VCardTitle class="d-flex align-center">
      <VIcon
        icon="fa-users"
        class="me-3"
        color="success"
      />
      Users Online
      <VChip
        color="success"
        variant="tonal"
        size="small"
        class="ms-3"
      >
        {{ users.length }}
      </VChip>
    </VCardTitle>
    
    <VCardText class="pa-0">
      <div
        v-if="users.length === 0"
        class="empty-state pa-6 text-center"
      >
        <VIcon
          icon="fa-user-times"
          size="48"
          color="surface-variant"
          class="mb-3"
        />
        <p class="text-body-1 text-medium-emphasis">
          No users currently online
        </p>
      </div>
      
      <VList
        v-else
        class="user-list"
      >
        <VListItem
          v-for="(user, index) in users"
          :key="user.id"
          class="user-item"
        >
          <template #prepend>
            <VAvatar
              color="success"
              size="40"
              class="user-avatar"
            >
              <span class="text-white font-weight-bold">
                {{ user.name.charAt(0).toUpperCase() }}
              </span>
              <div class="online-indicator" />
            </VAvatar>
          </template>
          
          <VListItemTitle class="user-name">
            {{ user.name }}
          </VListItemTitle>
          
          <VListItemSubtitle class="user-details">
            <div class="text-caption">
              <VIcon
                icon="fa-clock"
                size="10"
                class="me-1"
              />
              Active {{ user.last_activity }}
            </div>
            <div class="text-caption mt-1">
              <VIcon
                icon="fa-sign-in-alt"
                size="10"
                class="me-1"
              />
              Logged in {{ user.login_time }}
            </div>
          </VListItemSubtitle>
          
          <VDivider
            v-if="index < users.length - 1"
            class="mt-3"
          />
        </VListItem>
      </VList>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.online-users-widget {
  height: 100%;
  transition: all 0.3s ease;
  
  &:hover {
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }
}

.user-list {
  max-height: 320px;
  overflow-y: auto;
  
  &::-webkit-scrollbar {
    width: 4px;
  }
  
  &::-webkit-scrollbar-track {
    background: rgba(var(--v-theme-surface-variant), 0.3);
  }
  
  &::-webkit-scrollbar-thumb {
    background: rgba(var(--v-theme-success), 0.3);
    border-radius: 2px;
    
    &:hover {
      background: rgba(var(--v-theme-success), 0.5);
    }
  }
}

.user-item {
  transition: all 0.2s ease;
  border-radius: 8px;
  margin: 0 8px;
  
  &:hover {
    background: rgba(var(--v-theme-success), 0.05);
  }
}

.user-avatar {
  position: relative;
  
  .online-indicator {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 12px;
    height: 12px;
    background: rgb(var(--v-theme-success));
    border: 2px solid rgb(var(--v-theme-surface));
    border-radius: 50%;
    animation: pulse 2s infinite;
  }
}

.user-name {
  font-weight: 500;
  font-size: 0.875rem;
  line-height: 1.3;
}

.user-details {
  color: rgb(var(--v-theme-on-surface-variant));
  
  .text-caption {
    font-size: 0.7rem;
    line-height: 1.2;
  }
}

.empty-state {
  animation: fadeIn 0.5s ease-out;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.7;
    transform: scale(1.1);
  }
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
  .user-list {
    max-height: 250px;
  }
  
  .user-name {
    font-size: 0.8rem;
  }
  
  .user-details .text-caption {
    font-size: 0.65rem;
  }
}
</style>
