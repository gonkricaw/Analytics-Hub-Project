import { useAuth } from '@/composables/useAuth'
import { useSystemConfigStore } from '@/stores/systemConfig.js'
import { computed } from 'vue'

export function useMaintenanceMode() {
  const { user } = useAuth()
  const systemConfigStore = useSystemConfigStore()
  
  // Computed properties for maintenance mode
  const isMaintenanceMode = computed(() => 
    systemConfigStore.generalSettings.maintenanceMode
  )
  
  const maintenanceMessage = computed(() => 
    systemConfigStore.generalSettings.maintenanceMessage || 'System is under maintenance. Please try again later.'
  )
  
  // Check if current user can bypass maintenance mode (admin users)
  const canBypassMaintenance = computed(() => {
    if (!user.value) return false
    
    // Check if user has admin roles that can bypass maintenance
    const hasAdminRole = user.value.roles?.some(role => 
      role.name === 'super_admin' || role.name === 'admin'
    )
    
    // Check if user has specific maintenance bypass permission
    const hasMaintenancePermission = user.value.permissions?.some(permission =>
      permission.name === 'maintenance.bypass' || permission.name === 'system.manage'
    )
    
    return hasAdminRole || hasMaintenancePermission
  })
  
  return {
    isMaintenanceMode,
    maintenanceMessage,
    canBypassMaintenance,
  }
}
