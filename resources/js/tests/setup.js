import { config } from '@vue/test-utils'
import { vi } from 'vitest'

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

// Mock axios
vi.mock('axios', () => ({
  default: {
    defaults: {
      withCredentials: false,
      baseURL: '',
      headers: {
        common: {},
      },
    },
    interceptors: {
      response: {
        use: vi.fn(),
      },
    },
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
}))

// Mock Vue Router
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: vi.fn(),
    replace: vi.fn(),
    go: vi.fn(),
    back: vi.fn(),
    forward: vi.fn(),
  }),
  useRoute: () => ({
    path: '/',
    params: {},
    query: {},
    meta: {},
  }),
}))

// Global test utilities
global.ResizeObserver = vi.fn().mockImplementation(() => ({
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
