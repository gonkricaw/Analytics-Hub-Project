import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import laravel from 'laravel-vite-plugin'
import { fileURLToPath } from 'node:url'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import { VueRouterAutoImports, getPascalCaseRouteName } from 'unplugin-vue-router'
import VueRouter from 'unplugin-vue-router/vite'
import { defineConfig } from 'vite'
import Layouts from 'vite-plugin-vue-layouts'
import vuetify from 'vite-plugin-vuetify'
import svgLoader from 'vite-svg-loader'

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => ({
  base: '/',
  build: {
    // Optimize chunk size to prevent too many HTTP requests
    chunkSizeWarningLimit: 2000,
    // Apply compression for better performance
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: mode === 'production',
        drop_debugger: mode === 'production'
      }
    },
    rollupOptions: {
      output: {
        // Ensure proper MIME types by using standard extensions
        entryFileNames: 'assets/[name]-[hash].js',
        chunkFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash].[ext]',
        // Optimize module loading
        manualChunks: (id) => {
          // Group Vue ecosystem together
          if (id.includes('node_modules/vue/') || 
              id.includes('node_modules/vue-router/') ||
              id.includes('node_modules/pinia/')) {
            return 'vue-ecosystem';
          }
          
          // Group Vuetify separately
          if (id.includes('node_modules/vuetify/')) {
            return 'vuetify';
          }
          
          // Group utilities
          if (id.includes('node_modules/lodash/') || 
              id.includes('node_modules/date-fns/') ||
              id.includes('node_modules/@vueuse/')) {
            return 'utils';
          }
          
          // Group charting libraries
          if (id.includes('node_modules/apexcharts/') || 
              id.includes('node_modules/vue3-apexcharts/')) {
            return 'charts';
          }
        }
      }
    }
  },
  plugins: [// Docs: https://github.com/posva/unplugin-vue-router
  // ℹ️ This plugin should be placed before vue plugin
    VueRouter({
      getRouteName: routeNode => {
      // Convert pascal case to kebab case
        return getPascalCaseRouteName(routeNode)
          .replace(/([a-z\d])([A-Z])/g, '$1-$2')
          .toLowerCase()
      },

      routesFolder: 'resources/js/pages',
    }),
    vue({
      template: {
        compilerOptions: {
          isCustomElement: tag => tag === 'swiper-container' || tag === 'swiper-slide',
        },

        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    laravel({
      input: ['resources/js/main.js'],
      refresh: true,
      buildDirectory: 'build',
      // Set proper MIME types in development
      cors: true,
      // Force correct MIME types for assets
      headers: {
        'Access-Control-Allow-Origin': '*',
        'Content-Type': 'application/javascript',
      },
      hotFile: 'public/hot',
      // Force proper MIME types for Laravel Vite assets
      transformOnServe: (code, devServerUrl) => {
        return code
          .replace(/\/resources\/js\//g, `${devServerUrl}/resources/js/`)
          .replace(/\/resources\/styles\//g, `${devServerUrl}/resources/styles/`);
      }
    }),
    vueJsx(), // Docs: https://github.com/vuetifyjs/vuetify-loader/tree/master/packages/vite-plugin
    vuetify({
      styles: {
        configFile: 'resources/styles/variables/_vuetify.scss',
      },
    }), // Docs: https://github.com/johncampionjr/vite-plugin-vue-layouts#vite-plugin-vue-layouts
    Layouts({
      layoutsDirs: './resources/js/layouts/',
    }), // Docs: https://github.com/antfu/unplugin-vue-components#unplugin-vue-components
    Components({
      dirs: ['resources/js/@core/components', 'resources/js/views/demos', 'resources/js/components'],
      dts: true,
      resolvers: [
        componentName => {
        // Auto import `VueApexCharts`
          if (componentName === 'VueApexCharts')
            return { name: 'default', from: 'vue3-apexcharts', as: 'VueApexCharts' }
        },
      ],
    }), // Docs: https://github.com/antfu/unplugin-auto-import#unplugin-auto-import
    AutoImport({
      imports: ['vue', VueRouterAutoImports, '@vueuse/core', '@vueuse/math', 'vue-i18n', 'pinia'],
      dirs: [
        './resources/js/@core/utils',
        './resources/js/@core/composable/',
        './resources/js/composables/',
        './resources/js/utils/',
        './resources/js/plugins/*/composables/*',
      ],
      vueTemplate: true,

      // ℹ️ Disabled to avoid confusion & accidental usage
      ignore: ['useCookies', 'useStorage'],
      eslintrc: {
        enabled: true,
        filepath: './.eslintrc-auto-import.json',
      },
    }),
    svgLoader(),
  ],
  define: { 'process.env': {} },
  resolve: {
    alias: {
      '@core-scss': fileURLToPath(new URL('./resources/styles/@core', import.meta.url)),
      '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
      '@themeConfig': fileURLToPath(new URL('./themeConfig.js', import.meta.url)),
      '@core': fileURLToPath(new URL('./resources/js/@core', import.meta.url)),
      '@layouts': fileURLToPath(new URL('./resources/js/@layouts', import.meta.url)),
      '@images': fileURLToPath(new URL('./resources/images/', import.meta.url)),
      '@styles': fileURLToPath(new URL('./resources/styles/', import.meta.url)),
      '@configured-variables': fileURLToPath(new URL('./resources/styles/variables/_template.scss', import.meta.url)),
      '@db': fileURLToPath(new URL('./resources/js/plugins/fake-api/handlers/', import.meta.url)),
      '@api-utils': fileURLToPath(new URL('./resources/js/plugins/fake-api/utils/', import.meta.url)),
      // Add vuetify settings alias to fix SASS import resolution
      'vuetify/settings': fileURLToPath(new URL('./node_modules/vuetify/lib/styles/settings', import.meta.url)),
    },
  },
  optimizeDeps: {
    exclude: ['vuetify'],
    entries: [
      './resources/js/**/*.vue',
    ],
  },
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./resources/js/tests/setup.js'],
  },
}))
