// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  ssr: false,

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
    'pinia-plugin-persistedstate/nuxt',
    '@vueuse/motion/nuxt',
    '@nuxt/image',
    '@nuxt/icon',
  ],

  icon: {
    serverBundle: {
      collections: ['simple-icons'],
    },
  },

  app: {
    head: {
      title: 'Bhaisepati Sports Academy | Train Harder, Move Faster, Grow Stronger',
      htmlAttrs: { lang: 'en' },
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Bhaisepati Sports Academy | Badminton training, gym & fitness, and recovery facilities in Bhaisepati, Kathmandu. Enroll in programs today.' },
        { name: 'theme-color', content: '#0A0A0F' },
      ],
      link: [
        { rel: 'icon', type: 'image/png', href: '/favicon.png' },
        { rel: 'apple-touch-icon', href: '/favicon.png' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700&display=swap',
        },
      ],
    },
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api',
      cloudinaryCloudName: process.env.NUXT_PUBLIC_CLOUDINARY_CLOUD_NAME || '',
      googleMapsKey: process.env.NUXT_PUBLIC_GOOGLE_MAPS_KEY || '',
      storeLat: 27.7172,
      storeLng: 85.3240,
      umamiHost: process.env.NUXT_PUBLIC_UMAMI_HOST || '',
      umamiWebsiteId: process.env.NUXT_PUBLIC_UMAMI_WEBSITE_ID || '',
    },
  },

  // Static SPA: build with `npx nuxi generate`, deploy .output/public/ to server
  // No Node.js process needed on shared hosting

  image: {
    provider: 'cloudinary',
    cloudinary: {
      baseURL: `https://res.cloudinary.com/${process.env.NUXT_PUBLIC_CLOUDINARY_CLOUD_NAME || 'dhknx0eac'}/image/upload/`,
    },
  },

  tailwindcss: {
    cssPath: '~/assets/css/main.css',
  },
})
