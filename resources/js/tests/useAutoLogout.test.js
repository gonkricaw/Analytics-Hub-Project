import { useAutoLogout } from '@/composables/useAutoLogout'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

// Mock useAuth
const mockLogout = vi.fn()
vi.mock('@/composables/useAuth', () => ({
  useAuth: () => ({
    logout: mockLogout,
    isLoggedIn: { value: true },
  }),
}))

describe('useAutoLogout', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.useFakeTimers()
    
    // Reset document event listeners
    document.removeEventListener = vi.fn()
    document.addEventListener = vi.fn()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  it('should set up activity listeners', () => {
    useAutoLogout()

    expect(document.addEventListener).toHaveBeenCalledWith('mousedown', expect.any(Function))
    expect(document.addEventListener).toHaveBeenCalledWith('mousemove', expect.any(Function))
    expect(document.addEventListener).toHaveBeenCalledWith('keypress', expect.any(Function))
    expect(document.addEventListener).toHaveBeenCalledWith('scroll', expect.any(Function))
    expect(document.addEventListener).toHaveBeenCalledWith('touchstart', expect.any(Function))
    expect(document.addEventListener).toHaveBeenCalledWith('click', expect.any(Function))
  })

  it('should logout after timeout period', () => {
    useAutoLogout()

    // Fast-forward time by 15 minutes (900000ms)
    vi.advanceTimersByTime(900000)

    expect(mockLogout).toHaveBeenCalled()
  })

  it('should reset timer on user activity', () => {
    useAutoLogout()

    // Fast-forward to just before timeout
    vi.advanceTimersByTime(800000) // 13.33 minutes

    // Simulate user activity
    const addEventListenerCalls = document.addEventListener.mock.calls
    const mouseMoveHandler = addEventListenerCalls.find(call => call[0] === 'mousemove')[1]
    mouseMoveHandler()

    // Fast-forward another 13.33 minutes (should not logout yet)
    vi.advanceTimersByTime(800000)

    expect(mockLogout).not.toHaveBeenCalled()

    // Fast-forward to complete the full timeout from activity
    vi.advanceTimersByTime(100000)

    expect(mockLogout).toHaveBeenCalled()
  })

  it('should clean up listeners on unmount', () => {
    const { stop } = useAutoLogout()

    stop()

    expect(document.removeEventListener).toHaveBeenCalledWith('mousedown', expect.any(Function))
    expect(document.removeEventListener).toHaveBeenCalledWith('mousemove', expect.any(Function))
    expect(document.removeEventListener).toHaveBeenCalledWith('keypress', expect.any(Function))
    expect(document.removeEventListener).toHaveBeenCalledWith('scroll', expect.any(Function))
    expect(document.removeEventListener).toHaveBeenCalledWith('touchstart', expect.any(Function))
    expect(document.removeEventListener).toHaveBeenCalledWith('click', expect.any(Function))
  })

  it('should handle multiple activity events', () => {
    useAutoLogout()

    // Get all the event handlers
    const addEventListenerCalls = document.addEventListener.mock.calls
    const handlers = {
      mousedown: addEventListenerCalls.find(call => call[0] === 'mousedown')[1],
      mousemove: addEventListenerCalls.find(call => call[0] === 'mousemove')[1],
      keypress: addEventListenerCalls.find(call => call[0] === 'keypress')[1],
      scroll: addEventListenerCalls.find(call => call[0] === 'scroll')[1],
      touchstart: addEventListenerCalls.find(call => call[0] === 'touchstart')[1],
      click: addEventListenerCalls.find(call => call[0] === 'click')[1],
    }

    // Simulate multiple activities
    handlers.mousedown()
    handlers.keypress()
    handlers.scroll()

    // Fast-forward to just before timeout
    vi.advanceTimersByTime(800000)

    // More activity
    handlers.touchstart()
    handlers.click()

    // Should not logout yet
    vi.advanceTimersByTime(800000)
    expect(mockLogout).not.toHaveBeenCalled()

    // Complete timeout from last activity
    vi.advanceTimersByTime(100000)
    expect(mockLogout).toHaveBeenCalled()
  })
})
