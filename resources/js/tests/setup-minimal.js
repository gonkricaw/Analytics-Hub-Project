import * as matchers from '@testing-library/jest-dom/matchers'
import { config } from '@vue/test-utils'
import { expect, vi } from 'vitest'

// Extend Vitest's expect with testing-library matchers
expect.extend(matchers)

// Make Vue composition API globally available
import { computed, onMounted, onUnmounted, reactive, readonly, ref, watch } from 'vue'

global.ref = ref
global.reactive = reactive
global.computed = computed
global.watch = watch
global.onMounted = onMounted
global.onUnmounted = onUnmounted
global.readonly = readonly

// Mock window object
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
})

// Mock localStorage
const localStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
}

Object.defineProperty(window, 'localStorage', {
  value: localStorageMock,
})

// Mock ResizeObserver
global.ResizeObserver = vi.fn().mockImplementation(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
}))

// Mock IntersectionObserver
global.IntersectionObserver = vi.fn().mockImplementation(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn(),
}))

// Configure Vue Test Utils
config.global.mocks = {
  $t: key => key,
  $route: {
    path: '/',
    params: {},
    query: {},
    meta: {},
  },
  $router: {
    push: vi.fn(),
    replace: vi.fn(),
  },
}
