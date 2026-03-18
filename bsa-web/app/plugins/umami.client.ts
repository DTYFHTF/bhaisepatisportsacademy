/**
 * Injects the Umami analytics tracking script on the client.
 * Reads host and websiteId from runtime config.
 */
export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const host = config.public.umamiHost as string
  const websiteId = config.public.umamiWebsiteId as string

  if (!host || !websiteId) return

  useHead({
    script: [
      {
        src: `${host}/script.js`,
        async: true,
        defer: true,
        'data-website-id': websiteId,
      },
    ],
  })
})
