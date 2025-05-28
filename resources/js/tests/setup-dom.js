/**
 * Test configuration for accessibility testing with DOM mocking
 */

import { beforeAll, vi } from 'vitest'

beforeAll(() => {
  // Mock DOM environment for testing
  global.window = {
    IntersectionObserver: class IntersectionObserver {
      constructor() {}
      observe() {}
      unobserve() {}
      disconnect() {}
    },
    ResizeObserver: class ResizeObserver {
      constructor() {}
      observe() {}
      unobserve() {}
      disconnect() {}
    },
    Worker: class Worker {
      constructor() {}
      postMessage() {}
      terminate() {}
    },
    matchMedia: vi.fn(query => ({
      matches: false,
      media: query,
      onchange: null,
      addListener: vi.fn(),
      removeListener: vi.fn(),
      addEventListener: vi.fn(),
      removeEventListener: vi.fn(),
      dispatchEvent: vi.fn(),
    })),
  }
  
  global.navigator = {
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    maxTouchPoints: 0,
    serviceWorker: {
      register: vi.fn(),
    },
  }
  
  global.document = {
    createElement: vi.fn(() => ({
      style: {},
      classList: {
        add: vi.fn(),
        remove: vi.fn(),
        contains: vi.fn(),
      },
    })),
    body: {
      style: {},
      classList: {
        add: vi.fn(),
        remove: vi.fn(),
        contains: vi.fn(),
      },
    },
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    querySelectorAll: vi.fn(() => []),
  }
})
