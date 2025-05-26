import { describe, expect, it } from 'vitest'

describe('Basic Test', () => {
  it('should work', () => {
    expect(1 + 1).toBe(2)
  })

  it('should handle async operations', async () => {
    const result = await Promise.resolve('hello')

    expect(result).toBe('hello')
  })
})
