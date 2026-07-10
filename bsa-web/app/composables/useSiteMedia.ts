/**
 * Fixed-position images across the site (hero backgrounds, page banners,
 * gallery tiles) that owners manage from admin → Site Media. Keys match
 * the SiteMedia.key column, camelCased by the API's response middleware
 * (e.g. home_hero_poster -> homeHeroPoster).
 */
export interface SiteMediaMap {
  [key: string]: string | null
}

export function useSiteMedia() {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase

  const { data } = useFetch<SiteMediaMap>(`${apiBase}/site-media`, {
    default: () => ({}),
    server: false,
  })

  const media = computed<SiteMediaMap>(() => data.value ?? {})

  /** Uploaded URL for `key`, or `fallback` if not yet uploaded. */
  function get(key: string, fallback: string): string {
    return media.value[key] || fallback
  }

  return { media, get }
}
