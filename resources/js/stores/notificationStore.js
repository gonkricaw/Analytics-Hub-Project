import axios from 'axios'
import { defineStore } from 'pinia'

/**
 * Notification Store
 * Manages user notifications, unread counts, and notification operations
 */
export const useNotificationStore = defineStore('notification', {
  state: () => ({
    // Notification data
    notifications: [],
    unreadCount: 0,
    
    // Loading states
    loading: false,
    markingRead: false,
    
    // Pagination
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
    },
    
    // Filters
    filters: {
      only_unread: false,
      per_page: 15,
    },
    
    // Real-time updates
    lastFetchTime: null,
    pollingInterval: null,
  }),

  getters: {
    /**
     * Get unread notifications
     */
    unreadNotifications: state => {
      return state.notifications.filter(notification => !notification.isSeen)
    },

    /**
     * Get read notifications
     */
    readNotifications: state => {
      return state.notifications.filter(notification => notification.isSeen)
    },

    /**
     * Check if there are any unread notifications
     */
    hasUnreadNotifications: state => {
      return state.unreadCount > 0
    },

    /**
     * Get formatted notifications for UI components
     */
    formattedNotifications: state => {
      return state.notifications.map(notification => ({
        id: notification.id,
        title: notification.title,
        subtitle: notification.content,
        time: notification.time,
        isSeen: notification.isSeen,
        icon: 'tabler-bell',
        color: notification.isSeen ? 'default' : 'primary',
      }))
    },
  },

  actions: {
    /**
     * Fetch user notifications
     */
    async fetchNotifications(params = {}) {
      try {
        this.loading = true
        
        const queryParams = {
          ...this.filters,
          ...params,
        }

        const response = await axios.get('/api/user/notifications', {
          params: queryParams,
        })

        if (response.data.success) {
          this.notifications = response.data.data.notifications
          this.pagination = response.data.data.pagination
          this.unreadCount = response.data.data.unread_count
          this.lastFetchTime = new Date()
        }

        return response.data
      } catch (error) {
        console.error('Error fetching notifications:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Fetch unread count only
     */
    async fetchUnreadCount() {
      try {
        const response = await axios.get('/api/user/notifications/unread-count')
        
        if (response.data.success) {
          this.unreadCount = response.data.data.unread_count
        }

        return response.data
      } catch (error) {
        console.error('Error fetching unread count:', error)
        throw error
      }
    },

    /**
     * Mark a notification as read
     */
    async markAsRead(notificationId) {
      try {
        this.markingRead = true
        
        const response = await axios.post(`/api/user/notifications/${notificationId}/mark-read`)
        
        if (response.data.success) {
          // Update local state
          const notification = this.notifications.find(n => n.id === notificationId)
          if (notification && !notification.isSeen) {
            notification.isSeen = true
            this.unreadCount = Math.max(0, this.unreadCount - 1)
          }
        }

        return response.data
      } catch (error) {
        console.error('Error marking notification as read:', error)
        throw error
      } finally {
        this.markingRead = false
      }
    },

    /**
     * Mark a notification as unread
     */
    async markAsUnread(notificationId) {
      try {
        this.markingRead = true
        
        const response = await axios.post(`/api/user/notifications/${notificationId}/mark-unread`)
        
        if (response.data.success) {
          // Update local state
          const notification = this.notifications.find(n => n.id === notificationId)
          if (notification && notification.isSeen) {
            notification.isSeen = false
            this.unreadCount += 1
          }
        }

        return response.data
      } catch (error) {
        console.error('Error marking notification as unread:', error)
        throw error
      } finally {
        this.markingRead = false
      }
    },

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
      try {
        this.markingRead = true
        
        const response = await axios.post('/api/user/notifications/mark-all-read')
        
        if (response.data.success) {
          // Update local state
          this.notifications.forEach(notification => {
            notification.isSeen = true
          })
          this.unreadCount = 0
        }

        return response.data
      } catch (error) {
        console.error('Error marking all notifications as read:', error)
        throw error
      } finally {
        this.markingRead = false
      }
    },

    /**
     * Remove a notification
     */
    async removeNotification(notificationId) {
      try {
        const response = await axios.delete(`/api/user/notifications/${notificationId}`)
        
        if (response.data.success) {
          // Update local state
          const index = this.notifications.findIndex(n => n.id === notificationId)
          if (index !== -1) {
            const notification = this.notifications[index]
            if (!notification.isSeen) {
              this.unreadCount = Math.max(0, this.unreadCount - 1)
            }
            this.notifications.splice(index, 1)
          }
        }

        return response.data
      } catch (error) {
        console.error('Error removing notification:', error)
        throw error
      }
    },

    /**
     * Load more notifications (pagination)
     */
    async loadMore() {
      if (this.pagination.current_page >= this.pagination.last_page) {
        return
      }

      try {
        const response = await this.fetchNotifications({
          page: this.pagination.current_page + 1,
        })

        if (response.success) {
          // Append new notifications to existing ones
          this.notifications.push(...response.data.notifications)
        }
      } catch (error) {
        console.error('Error loading more notifications:', error)
        throw error
      }
    },

    /**
     * Set filter for notifications
     */
    setFilter(key, value) {
      this.filters[key] = value
    },

    /**
     * Reset filters to default
     */
    resetFilters() {
      this.filters = {
        only_unread: false,
        per_page: 15,
      }
    },

    /**
     * Start polling for new notifications
     */
    startPolling(intervalMs = 30000) { // 30 seconds default
      this.stopPolling() // Clear any existing interval
      
      this.pollingInterval = setInterval(async () => {
        try {
          await this.fetchUnreadCount()
        } catch (error) {
          console.error('Error polling notifications:', error)
        }
      }, intervalMs)
    },

    /**
     * Stop polling for notifications
     */
    stopPolling() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval)
        this.pollingInterval = null
      }
    },

    /**
     * Handle notification click
     */
    async handleNotificationClick(notification) {
      if (!notification.isSeen) {
        await this.markAsRead(notification.id)
      }
    },

    /**
     * Refresh notifications
     */
    async refresh() {
      await this.fetchNotifications()
    },

    /**
     * Clear all local notification data
     */
    clear() {
      this.notifications = []
      this.unreadCount = 0
      this.pagination = {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
      }
      this.lastFetchTime = null
      this.stopPolling()
    },
  },
})
