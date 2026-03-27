// DEPLOYMENT NOTE:
// This project runs as a STATIC SPA (no Node.js process on server).
// Build: `npx nuxi generate`
// Output: `.output/public/`
// Upload the contents of `.output/public/` to the domain's document root.
// The .htaccess file handles SPA routing (all paths serve index.html).
//
// PM2 config below is kept for local dev/testing only.
module.exports = {
  apps: [
    {
      name: 'bsa-frontend',
      port: 3001,
      script: '.output/server/index.mjs',
      instances: 1,
      exec_mode: 'fork',
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      env_production: {
        NODE_ENV: 'production',
        PORT: 3001,
        // Server-side API base (resolved at runtime by Nuxt SSR)
        NUXT_API_BASE: 'https://api.bsa.abinmaharjan.com.np/api',
        // Public runtime config overrides (baked at build, but PM2 can override)
        NUXT_PUBLIC_API_BASE: 'https://api.bsa.abinmaharjan.com.np/api',
        NUXT_PUBLIC_SITE_URL: 'https://bsa.abinmaharjan.com.np',
        NUXT_PUBLIC_CLOUDINARY_CLOUD_NAME: 'dhknx0eac',
        NUXT_PUBLIC_UMAMI_HOST: 'https://cloud.umami.is',
        NUXT_PUBLIC_UMAMI_WEBSITE_ID: 'cffc5868-c5ec-4824-81fb-34b170c74051',
        // Google Maps key: baked at build time via GitHub Actions secret
        // NUXT_PUBLIC_GOOGLE_MAPS_KEY: set via CI build env
      },
      kill_timeout: 5000,
      listen_timeout: 10000,
      shutdown_with_message: true,
    },
  ],
};

