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
  googleMapsUrl: string | null
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
  contactEmail: 'info@bsa.abinmaharjan.com.np',
  contactPhone: '9821357118',
  contactAddress: 'Bhaisepati, Lalitpur, Nepal',
  storeLat: 27.6588,
  storeLng: 85.3134,
  googleMapsUrl: 'https://maps.app.goo.gl/ZzmXJ5rDDKihfaeu7',
  instagramUrl: 'https://www.instagram.com/bhaisepatisportsacademy/',
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
    server: false,
    transform: (raw: Record<string, unknown>): SiteSettings => ({
      storeName:       (raw.store_name as string)        ?? defaults.storeName,
      storeTagline:    (raw.store_tagline as string)      ?? defaults.storeTagline,
      logoUrl:         (raw.logo_url as string | null)    ?? defaults.logoUrl,
      iconUrl:         (raw.icon_url as string | null)    ?? defaults.iconUrl,
      contactEmail:    (raw.contact_email as string)      ?? defaults.contactEmail,
      contactPhone:    (raw.contact_phone as string)      ?? defaults.contactPhone,
      contactAddress:  (raw.contact_address as string)    ?? defaults.contactAddress,
      storeLat:        (raw.store_lat as number)          ?? defaults.storeLat,
      storeLng:        (raw.store_lng as number)          ?? defaults.storeLng,
      googleMapsUrl:   (raw.google_maps_url as string|null) ?? defaults.googleMapsUrl,
      instagramUrl:    (raw.instagram_url as string|null) ?? defaults.instagramUrl,
      facebookUrl:     (raw.facebook_url as string|null)  ?? defaults.facebookUrl,
      whatsappNumber:  (raw.whatsapp_number as string|null) ?? defaults.whatsappNumber,
      deliveryTagline: (raw.delivery_tagline as string)   ?? defaults.deliveryTagline,
      returnTagline:   (raw.return_tagline as string)     ?? defaults.returnTagline,
    }),
  })

  const settings = computed<SiteSettings>(() => data.value ?? defaults)

  const whatsappUrl = computed(() => {
    if (!settings.value.whatsappNumber) return null
    const num = settings.value.whatsappNumber.replace(/\D/g, '')
    return `https://wa.me/977${num}?text=Hi!%20I'd%20like%20to%20enquire%20about%20programs%20at%20Bhaisepati%20Sports%20Academy.`
  })

  return { settings, whatsappUrl }
}
