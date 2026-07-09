/**
 * Page-level SEO: title/description plus canonical URL, OpenGraph and
 * Twitter card tags derived from them. Use in every public page instead
 * of calling useSeoMeta directly, so link previews and canonicals stay
 * consistent sitewide.
 */

// Placeholder until the academy has its own branded 1200×630 OG image
// (see docs/CONTENT_STRATEGY.md — photography shot list).
const DEFAULT_OG_IMAGE
  = 'https://images.unsplash.com/photo-1613914153966-fd0cf11e0e8b?auto=format&fit=crop&w=1200&h=630&q=80'

export function usePageSeo(input: { title: string; description: string; image?: string }) {
  const config = useRuntimeConfig()
  const route = useRoute()
  const url = `${config.public.siteUrl}${route.path === '/' ? '' : route.path.replace(/\/$/, '')}`

  useSeoMeta({
    title: input.title,
    description: input.description,
    ogTitle: input.title,
    ogDescription: input.description,
    ogUrl: url,
    ogImage: input.image ?? DEFAULT_OG_IMAGE,
    twitterCard: 'summary_large_image',
  })

  useHead({
    link: [{ rel: 'canonical', href: url }],
  })
}
