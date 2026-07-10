// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  // Prerendered static HTML (SSG): real content for crawlers and link
  // previews, still deployed as plain files — no Node process on the server.
  ssr: true,

  nitro: {
    prerender: {
      crawlLinks: true,
      routes: [
        '/',
        '/programs',
        '/facilities',
        '/kitchen',
        '/book',
        '/about',
        '/story',
        '/faq',
        '/privacy',
        '/terms',
        '/refund-policy',
      ],
    },
  },

  routeRules: {
    // Transactional/dynamic routes stay client-only (browser APIs, tokens,
    // per-user state); served via the 200.html SPA fallback.
    '/checkout': { ssr: false },
    '/track': { ssr: false },
    '/order/**': { ssr: false },
    '/p/**': { ssr: false },
    '/shop': { ssr: false },
    '/shop/**': { ssr: false },
  },

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
    'pinia-plugin-persistedstate/nuxt',
    '@nuxt/image',
    '@nuxt/icon',
  ],

  icon: {
    serverBundle: {
      collections: ['simple-icons'],
    },
  },

  app: {
    pageTransition: { name: 'page', mode: 'out-in' },
    head: {
      title: 'Bhaisepati Sports Academy | Train Harder, Move Faster, Grow Stronger',
      htmlAttrs: { lang: 'en' },
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Bhaisepati Sports Academy | Badminton training, gym & fitness, and recovery facilities in Bhaisepati, Kathmandu. Enroll in programs today.' },
        { name: 'theme-color', content: '#0A0A0F' },
      ],
      script: [
        // Marks JS availability before first paint; scroll-reveal hidden states
        // are scoped to html.js so content is never invisible without JS.
        { innerHTML: 'document.documentElement.classList.add("js")', tagPosition: 'head' },
      ],
      link: [
        { rel: 'icon', type: 'image/png', href: '/favicon.png' },
        { rel: 'apple-touch-icon', href: '/favicon.png' },
        // Fonts are self-hosted (see assets/css/main.css @font-face);
        // preload avoids a flash of fallback text.
        { rel: 'preload', href: '/fonts/inter-latin-var.woff2', as: 'font', type: 'font/woff2', crossorigin: '' },
        { rel: 'preload', href: '/fonts/bebas-neue-latin.woff2', as: 'font', type: 'font/woff2', crossorigin: '' },
      ],
    },
  },

  runtimeConfig: {
    public: {
      siteUrl: process.env.NUXT_PUBLIC_SITE_URL || 'https://bhaisepatisportsacademy.com.np',
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8001/api',
      cloudinaryCloudName: process.env.NUXT_PUBLIC_CLOUDINARY_CLOUD_NAME || '',
      googleMapsKey: process.env.NUXT_PUBLIC_GOOGLE_MAPS_KEY || '',
      // Bhaisepati Sports Academy — from the academy's own Google Maps place entry
      storeLat: 27.6455713,
      storeLng: 85.2968519,
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
