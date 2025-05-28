// Debug script to test useFormAccessibility
const { useFormAccessibility } = require('./resources/js/composables/useFormAccessibility.js')

// Mock ref and computed
global.ref = value => ({ value })
global.computed = fn => ({ value: fn() })

// Test the composable
const { getFieldAttributes, formId } = useFormAccessibility()

console.log('Form ID:', formId.value)
console.log('Field attributes for "test":', getFieldAttributes('test'))
console.log('Field attributes with options:', getFieldAttributes('test', {
  required: true,
  label: 'Test Field',
  description: 'Test description',
}))
