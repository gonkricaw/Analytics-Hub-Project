/**
 * Missing JavaScript Chunks Fix
 * This script creates the missing JavaScript chunk files that are causing 404 errors
 */

const fs = require('fs');
const path = require('path');

// Create chunks directory if it doesn't exist
const chunksDir = path.join(__dirname, 'public', 'js', 'chunks');
if (!fs.existsSync(chunksDir)) {
    fs.mkdirSync(chunksDir, { recursive: true });
    console.log('✓ Created /public/js/chunks directory');
}

// Common chunk files that are typically needed
const chunkFiles = [
    'vendor.js',
    'app.js',
    'components.js',
    'layouts.js',
    'pages.js',
    'stores.js',
    'composables.js'
];

// Create placeholder chunk files
chunkFiles.forEach(filename => {
    const filePath = path.join(chunksDir, filename);
    if (!fs.existsSync(filePath)) {
        const content = `// ${filename} - Generated placeholder
console.log('Loaded chunk: ${filename}');
// This is a placeholder file. Run 'npm run build' to generate actual chunks.
`;
        fs.writeFileSync(filePath, content);
        console.log(`✓ Created chunk file: ${filename}`);
    }
});

// Create additional missing JS files in main js directory
const jsDir = path.join(__dirname, 'public', 'js');
const mainJsFiles = {
    'admin.js': `// Admin Module
console.log('Admin module loaded');

// Admin dashboard functionality
window.AdminModule = {
    init: function() {
        console.log('Admin module initialized');
    },
    
    // Add your admin-specific functions here
    loadDashboard: function() {
        console.log('Loading admin dashboard...');
    }
};

// Auto-initialize
document.addEventListener('DOMContentLoaded', function() {
    if (window.AdminModule) {
        window.AdminModule.init();
    }
});
`,

    'charts.js': `// Charts Module
console.log('Charts module loaded');

// Chart utilities and configurations
window.ChartsModule = {
    init: function() {
        console.log('Charts module initialized');
    },
    
    // Common chart configurations
    defaultOptions: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true
            }
        }
    },
    
    // Chart creation helper
    createChart: function(ctx, config) {
        console.log('Creating chart with config:', config);
        // Add your chart library integration here
    }
};

// Auto-initialize
document.addEventListener('DOMContentLoaded', function() {
    if (window.ChartsModule) {
        window.ChartsModule.init();
    }
});
`,

    'ui-extended.js': `// UI Extended Module
console.log('UI Extended module loaded');

// Extended UI components and utilities
window.UIExtended = {
    init: function() {
        console.log('UI Extended module initialized');
    },
    
    // Common UI utilities
    showLoader: function(element) {
        if (element) {
            element.classList.add('loading');
        }
    },
    
    hideLoader: function(element) {
        if (element) {
            element.classList.remove('loading');
        }
    },
    
    // Toast notifications
    showToast: function(message, type = 'info') {
        console.log(\`Toast (\${type}): \${message}\`);
        // Add your toast implementation here
    }
};

// Auto-initialize
document.addEventListener('DOMContentLoaded', function() {
    if (window.UIExtended) {
        window.UIExtended.init();
    }
});
`
};

// Create main JS files
Object.entries(mainJsFiles).forEach(([filename, content]) => {
    const filePath = path.join(jsDir, filename);
    if (!fs.existsSync(filePath) || fs.statSync(filePath).size < 100) {
        fs.writeFileSync(filePath, content);
        console.log(`✓ Created/Updated main JS file: ${filename}`);
    }
});

console.log('\n✓ All missing JavaScript files have been created!');
console.log('📝 Note: These are placeholder files. Run "npm run build" to generate the actual Vite chunks.');
