import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vite'
import vuetify from 'vite-plugin-vuetify'

export default defineConfig({
  base: '/',
  build: {
    outDir: 'public/build-test',
    rollupOptions: {
      input: ['resources/js/css-import-test.js'],
      output: {
        assetFileNames: 'assets/[name].[ext]'
      }
    }
  },
  plugins: [
    // Add Vue plugins for proper component handling
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
    vueJsx(),
    // Add vuetify plugin to properly handle SASS imports
    vuetify({
      styles: {
        configFile: 'resources/styles/variables/_vuetify.scss',
      },
    }),
  ],
  resolve: {
    alias: {
      '@core-scss': fileURLToPath(new URL('./resources/styles/@core', import.meta.url)),
      '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
      '@styles': fileURLToPath(new URL('./resources/styles/', import.meta.url)),
      '@configured-variables': fileURLToPath(new URL('./resources/styles/variables/_template.scss', import.meta.url)),
      // Add core utils alias
      '@core': fileURLToPath(new URL('./resources/js/@core', import.meta.url)),
      // Add layouts alias for SCSS imports
      '@layouts': fileURLToPath(new URL('./resources/js/@layouts', import.meta.url)),
      // Add vuetify settings alias to resolve the import
      'vuetify/settings': fileURLToPath(new URL('./node_modules/vuetify/lib/styles/settings', import.meta.url)),
    }
  }
})
