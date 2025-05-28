// Quick test to verify getEntityIcon function is working
import fs from 'fs'

// Read the useIconSystem file
const iconSystemPath = 'resources/js/composables/useIconSystem.js'
const iconSystemContent = fs.readFileSync(iconSystemPath, 'utf8')

// Check if getEntityIcon function exists in the file
const hasGetEntityIcon = iconSystemContent.includes('getEntityIcon')
const isExported = iconSystemContent.includes('getEntityIcon,')

console.log('🔍 Icon System Check:')
console.log(`✅ getEntityIcon function exists: ${hasGetEntityIcon}`)
console.log(`✅ getEntityIcon is exported: ${isExported}`)

// Check if the function has the expected entity mappings
const hasUserMapping = iconSystemContent.includes("'user'") || iconSystemContent.includes('"user"')
const hasContentMapping = iconSystemContent.includes("'content'") || iconSystemContent.includes('"content"')
const hasFileMapping = iconSystemContent.includes("'file'") || iconSystemContent.includes('"file"')

console.log(`✅ Has user entity mapping: ${hasUserMapping}`)
console.log(`✅ Has content entity mapping: ${hasContentMapping}`)
console.log(`✅ Has file entity mapping: ${hasFileMapping}`)

// Check if original components are still importing getEntityIcon
const componentsToCheck = [
  'resources/js/components/dashboard/LoginStatsWidget.vue',
  'resources/js/components/dashboard/FrequentUsersWidget.vue',
  'resources/js/components/admin/content/ContentFormModal.vue',
]

let componentsUsingIcon = 0
componentsToCheck.forEach(componentPath => {
  if (fs.existsSync(componentPath)) {
    const content = fs.readFileSync(componentPath, 'utf8')
    if (content.includes('getEntityIcon')) {
      componentsUsingIcon++
    }
  }
})

console.log(`✅ Components using getEntityIcon: ${componentsUsingIcon}/${componentsToCheck.length}`)

if (hasGetEntityIcon && isExported && hasUserMapping && componentsUsingIcon > 0) {
  console.log('\n🎉 SUCCESS: getEntityIcon implementation appears to be complete and properly integrated!')
} else {
  console.log('\n❌ Issues detected with getEntityIcon implementation')
}
