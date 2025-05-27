<script setup>
const props = defineProps({
  format: {
    type: String,
    default: '24h', // '12h' or '24h'
  },
})

const currentTime = ref(new Date())
const currentDate = ref(new Date())

// Update time every second
onMounted(() => {
  const updateTime = () => {
    const now = new Date()
    currentTime.value = now
    currentDate.value = now
  }
  
  updateTime()
  setInterval(updateTime, 1000)
})

const formattedTime = computed(() => {
  const options = {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: props.format === '12h',
  }
  return currentTime.value.toLocaleTimeString('en-US', options)
})

const formattedDate = computed(() => {
  const options = {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }
  return currentDate.value.toLocaleDateString('en-US', options)
})

const timeZone = computed(() => {
  const formatter = new Intl.DateTimeFormat('en-US', {
    timeZoneName: 'short',
  })
  const parts = formatter.formatToParts(currentTime.value)
  const timeZonePart = parts.find(part => part.type === 'timeZoneName')
  return timeZonePart ? timeZonePart.value : ''
})
</script>

<template>
  <VCard class="digital-clock-widget">
    <VCardText class="text-center pa-6">
      <div class="clock-display">
        <VIcon
          icon="fa-clock"
          size="24"
          class="clock-icon mb-3"
          color="primary"
        />
        
        <div class="time-display mb-3">
          <span class="time-text">{{ formattedTime }}</span>
          <span
            v-if="timeZone"
            class="timezone-text ms-2"
          >{{ timeZone }}</span>
        </div>
        
        <div class="date-display">
          <span class="date-text">{{ formattedDate }}</span>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.digital-clock-widget {
  background: linear-gradient(135deg, 
    rgba(var(--v-theme-primary), 0.1) 0%, 
    rgba(var(--v-theme-secondary), 0.1) 100%);
  border: 1px solid rgba(var(--v-theme-primary), 0.2);
  transition: all 0.3s ease;
  
  &:hover {
    box-shadow: 0 8px 32px rgba(var(--v-theme-primary), 0.2);
    transform: translateY(-2px);
  }
}

.clock-display {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.clock-icon {
  animation: pulse 2s infinite;
}

.time-display {
  display: flex;
  align-items: baseline;
  justify-content: center;
  flex-wrap: wrap;
}

.time-text {
  font-size: 2.5rem;
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
  font-family: 'Roboto Mono', monospace;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  animation: glow 3s ease-in-out infinite alternate;
}

.timezone-text {
  font-size: 0.875rem;
  font-weight: 500;
  color: rgb(var(--v-theme-on-surface-variant));
  margin-top: 0.5rem;
}

.date-text {
  font-size: 1.1rem;
  font-weight: 500;
  color: rgb(var(--v-theme-on-surface));
  opacity: 0.8;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.1);
    opacity: 0.8;
  }
}

@keyframes glow {
  from {
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  to {
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), 
                 0 0 20px rgba(var(--v-theme-primary), 0.3);
  }
}

// Responsive adjustments
@media (max-width: 768px) {
  .time-text {
    font-size: 2rem;
  }
  
  .date-text {
    font-size: 1rem;
  }
  
  .timezone-text {
    font-size: 0.75rem;
  }
}

@media (max-width: 480px) {
  .time-text {
    font-size: 1.75rem;
  }
  
  .time-display {
    flex-direction: column;
    align-items: center;
  }
  
  .timezone-text {
    margin-top: 0.25rem;
    margin-left: 0 !important;
  }
}
</style>
