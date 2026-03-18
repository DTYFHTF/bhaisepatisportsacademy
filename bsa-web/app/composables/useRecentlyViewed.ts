import { ref, onMounted } from 'vue'
import type { Product } from '~/types/product'
import { RECENTLY_VIEWED_MAX, RECENTLY_VIEWED_DAYS } from '~/utils/constants'

interface RecentItem {
  slug: string
  name: string
  colorName: string
  price: number
  image: string
  viewedAt: number
}

const STORAGE_KEY = 'pp_recently_viewed'

export function useRecentlyViewed() {
  const items = ref<RecentItem[]>([])

  function load() {
    if (typeof window === 'undefined') return
    try {
      const raw = localStorage.getItem(STORAGE_KEY)
      if (!raw) return
      const parsed: RecentItem[] = JSON.parse(raw)
      const cutoff = Date.now() - RECENTLY_VIEWED_DAYS * 24 * 60 * 60 * 1000
      items.value = parsed.filter((i) => i.viewedAt > cutoff)
    } catch {
      items.value = []
    }
  }

  function add(product: Product) {
    if (typeof window === 'undefined') return
    const existing = items.value.filter((i) => i.slug !== product.slug)
    const newItem: RecentItem = {
      slug: product.slug,
      name: product.name,
      colorName: product.colorName,
      price: product.price,
      image: product.images[0]?.url || '',
      viewedAt: Date.now(),
    }
    items.value = [newItem, ...existing].slice(0, RECENTLY_VIEWED_MAX)
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value))
  }

  onMounted(load)

  return { items, add }
}
