export interface SiteSettings {
  storeName: string
  storeTagline: string
  logoUrl: string | null
  iconUrl: string | null
  contactEmail: string
  contactPhone: string
  contactAddress: string
  storeLat: number
  storeLng: number
  instagramUrl: string | null
  facebookUrl: string | null
  whatsappNumber: string | null
  deliveryTagline: string
  returnTagline: string
}

const defaults: SiteSettings = {
  storeName: 'Bhaisepati Sports Academy',
  storeTagline: 'Train harder. Move faster. Grow stronger.',
  logoUrl: null,
  iconUrl: null,
  contactEmail: 'hello@bsa.example.com',
  contactPhone: '9821357118',
  contactAddress: 'Bhaisepati, Lalitpur, Nepal',
  storeLat: 27.6588,
  storeLng: 85.3134,
  instagramUrl: 'https://instagram.com/bsa.example.com',
  facebookUrl: null,
  whatsappNumber: '9821357118',
  deliveryTagline: 'Enrollment open for all programs.',
  returnTagline: 'Satisfaction guaranteed on every session.',
}

export function useSettings() {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase

  const { data } = useFetch<SiteSettings>(`${apiBase}/settings`, {
    default: () => defaults,
    getCachedData(key, nuxtApp) {
      const cached = nuxtApp.payload.data[key] ?? nuxtApp.static.data[key]
      return cached ?? undefined
    },
  })

  const settings = computed<SiteSettings>(() => data.value ?? defaults)

  const whatsappUrl = computed(() => {
    if (!settings.value.whatsappNumber) return null
    const num = settings.value.whatsappNumber.replace(/\D/g, '')
    return `https://wa.me/977${num}?text=Hi!%20I'd%20like%20to%20enquire%20about%20programs%20at%20Bhaisepati%20Sports%20Academy.`
  })

  return { settings, whatsappUrl }
}
