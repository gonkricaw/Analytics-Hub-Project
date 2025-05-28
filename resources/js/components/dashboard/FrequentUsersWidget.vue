<script setup>
import { useIconSystem } from '@/composables/useIconSystem.js'

const props = defineProps({
  users: {
    type: Array,
    default: () => [],
  },
})

// Icon system
const { getStatusIcon, getEntityIcon, getIcon } = useIconSystem()
</script>

<template>
  <VCard class="frequent-users-widget">
    <VCardTitle class="d-flex align-center">
      <VIcon
        :icon="getStatusIcon('success')"
        class="me-3"
        color="warning"
      />
      Top Active Users
      <VTooltip
        text="Most frequent logins this month"
        location="top"
      >
        <template #activator="{ props: tooltipProps }">
          <VIcon
            v-bind="tooltipProps"
            :icon="getStatusIcon('info')"
            size="16"
            color="surface-variant"
            class="ms-2"
          />
        </template>
      </VTooltip>
    </VCardTitle>
    
    <VCardText class="pa-0">
      <div
        v-if="users.length === 0"
        class="empty-state pa-6 text-center"
      >
        <VIcon
          :icon="getEntityIcon('chart')"
          size="48"
          color="surface-variant"
          class="mb-3"
        />
        <p class="text-body-1 text-medium-emphasis">
          No activity data available
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
            <div class="rank-badge">
              <VIcon
                v-if="index === 0"
                :icon="getIcon('trophy')"
                color="warning"
                size="20"
              />
              <VIcon
                v-else-if="index === 1"
                :icon="getIcon('medal')"
                color="info"
                size="18"
              />
              <VIcon
                v-else-if="index === 2"
                :icon="getIcon('award')"
                color="success"
                size="16"
              />
              <span
                v-else
                class="rank-number"
              >
                {{ index + 1 }}
              </span>
            </div>
            
            <VAvatar
              :color="index === 0 ? 'warning' : index === 1 ? 'info' : index === 2 ? 'success' : 'surface-variant'"
              size="40"
              class="ms-3"
            >
              <span class="text-white font-weight-bold">
                {{ user.name.charAt(0).toUpperCase() }}
              </span>
            </VAvatar>
          </template>
          
          <VListItemTitle class="user-name">
            {{ user.name }}
          </VListItemTitle>
          
          <VListItemSubtitle class="user-email">
            {{ user.email }}
          </VListItemSubtitle>
          
          <template #append>
            <VChip
              :color="index === 0 ? 'warning' : index === 1 ? 'info' : index === 2 ? 'success' : 'surface-variant'"
              variant="tonal"
              size="small"
              class="login-count"
            >
              {{ user.login_count }} logins
            </VChip>
          </template>
          
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
.frequent-users-widget {
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
    background: rgba(var(--v-theme-warning), 0.3);
    border-radius: 2px;
    
    &:hover {
      background: rgba(var(--v-theme-warning), 0.5);
    }
  }
}

.user-item {
  transition: all 0.2s ease;
  border-radius: 8px;
  margin: 0 8px;
  
  &:hover {
    background: rgba(var(--v-theme-warning), 0.05);
  }
}

.rank-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(var(--v-theme-surface-variant), 0.3);
  margin-right: 8px;
}

.rank-number {
  font-size: 0.875rem;
  font-weight: 700;
  color: rgb(var(--v-theme-on-surface-variant));
}

.user-name {
  font-weight: 500;
  font-size: 0.875rem;
  line-height: 1.3;
}

.user-email {
  font-size: 0.75rem;
  color: rgb(var(--v-theme-on-surface-variant));
  margin-top: 0.25rem;
}

.login-count {
  font-size: 0.7rem;
  font-weight: 600;
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
  .user-list {
    max-height: 250px;
  }
  
  .user-name {
    font-size: 0.8rem;
  }
  
  .user-email {
    font-size: 0.7rem;
  }
  
  .login-count {
    font-size: 0.65rem;
  }
  
  .rank-badge {
    width: 28px;
    height: 28px;
  }
}
</style>
