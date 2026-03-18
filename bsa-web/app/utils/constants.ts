// Bhaisepati Sports Academy constants

export const BRAND = {
  name: 'Bhaisepati Sports Academy',
  tagline: 'Train Harder. Move Faster. Grow Stronger.',
  origin: 'Bhaisepati, Kathmandu, Nepal',
  phone: '9821357118',
  whatsapp: '9779821357118',
  email: 'hello@bsa.example.com',
  address: 'Bhaisepati, Lalitpur, Nepal',
  instagram: 'bsa.example.com',
  openingHours: 'Sunday–Friday, 6am–9pm',
} as const

// ─── Services ─────────────────────────────────────────

export const SERVICE_CATEGORIES = ['WAXING', 'FACIAL', 'BODY_CARE', 'BROW'] as const
export type ServiceCategory = (typeof SERVICE_CATEGORIES)[number]

export const SERVICE_CATEGORY_LABELS: Record<ServiceCategory, string> = {
  WAXING: 'Waxing',
  FACIAL: 'Facial Treatments',
  BODY_CARE: 'Body Care',
  BROW: 'Brow Services',
}

export const SERVICE_CATEGORY_ICONS: Record<ServiceCategory, string> = {
  WAXING: '💆',
  FACIAL: '✨',
  BODY_CARE: '🧴',
  BROW: '👁️',
}

export interface WaxType {
  id: string
  name: string
  description: string
  icon: string
  recommendedServices: string[]
}

export const WAX_TYPES: WaxType[] = [
  {
    id: 'hard',
    name: 'Hard Wax',
    description: 'Gentle on sensitive areas, grips hair not skin. Our most popular choice for Brazilian and bikini waxing.',
    icon: '💎',
    recommendedServices: ['brazilian-wax', 'bikini-wax', 'underarm-wax'],
  },
  {
    id: 'rica',
    name: 'Rica Wax',
    description: 'Plant-based formula with natural oils. Ideal for sensitive and reactive skin types.',
    icon: '🌿',
    recommendedServices: ['full-body-wax', 'arm-wax', 'leg-wax', 'bikini-wax'],
  },
  {
    id: 'honey',
    name: 'Honey Wax',
    description: 'Traditional warm wax, perfect for larger surface areas. Effective and affordable.',
    icon: '🍯',
    recommendedServices: ['full-body-wax', 'leg-wax', 'half-body-wax'],
  },
  {
    id: 'luxury',
    name: 'Luxury Wax',
    description: 'Premium lavender & chamomile blend. The most comfortable waxing experience available.',
    icon: '✨',
    recommendedServices: ['brazilian-wax', 'full-body-wax'],
  },
]

export interface ServiceDef {
  id: string
  slug: string
  name: string
  description: string
  category: ServiceCategory
  duration: number  // minutes
  price: number     // paisa
  waxTypes: string[]
  isPopular: boolean
}

// Static service definitions used for quiz/combo logic (deterministic features).
// The live /api/services endpoint is the source of truth for pages.
export const SERVICES: ServiceDef[] = [
  {
    id: 'srv-01',
    slug: 'full-arms-wax',
    name: 'Full Arms Wax',
    description: 'Complete waxing for both arms from shoulder to wrist. Smooth, clean results every time.',
    category: 'WAXING',
    duration: 30,
    price: 80000,
    waxTypes: ['Rica', 'Honey', 'Chocolate'],
    isPopular: true,
  },
  {
    id: 'srv-02',
    slug: 'full-legs-wax',
    name: 'Full Legs Wax',
    description: 'Both legs from hip to ankle. Our most popular service for consistently smooth skin.',
    category: 'WAXING',
    duration: 45,
    price: 120000,
    waxTypes: ['Rica', 'Honey', 'Chocolate'],
    isPopular: true,
  },
  {
    id: 'srv-03',
    slug: 'underarm-wax',
    name: 'Underarm Wax',
    description: 'Clean, precise underarm waxing. Fast and virtually pain-free with our premium wax.',
    category: 'WAXING',
    duration: 15,
    price: 30000,
    waxTypes: ['Rica', 'Honey', 'Chocolate', 'Sugar'],
    isPopular: true,
  },
  {
    id: 'srv-04',
    slug: 'full-body-wax',
    name: 'Full Body Wax',
    description: 'Complete body waxing — arms, legs, underarms, and stomach. Our signature treatment.',
    category: 'WAXING',
    duration: 90,
    price: 250000,
    waxTypes: ['Rica', 'Chocolate'],
    isPopular: true,
  },
  {
    id: 'srv-05',
    slug: 'upper-lip-wax',
    name: 'Upper Lip Wax',
    description: 'Precise upper lip hair removal. Quick, clean, and gentle on sensitive facial skin.',
    category: 'FACIAL',
    duration: 10,
    price: 15000,
    waxTypes: ['Rica', 'Sugar'],
    isPopular: true,
  },
  {
    id: 'srv-06',
    slug: 'full-face-wax',
    name: 'Full Face Wax',
    description: 'Complete facial waxing — forehead, cheeks, upper lip, chin, and sideburns.',
    category: 'FACIAL',
    duration: 25,
    price: 50000,
    waxTypes: ['Rica', 'Sugar'],
    isPopular: true,
  },
  {
    id: 'srv-07',
    slug: 'eyebrow-threading',
    name: 'Eyebrow Threading',
    description: 'Precise eyebrow shaping using the threading technique. Clean lines, natural shape.',
    category: 'BROW',
    duration: 15,
    price: 20000,
    waxTypes: [],
    isPopular: true,
  },
  {
    id: 'srv-08',
    slug: 'body-scrub-treatment',
    name: 'Body Scrub Treatment',
    description: 'Full body exfoliation with natural scrub. Removes dead skin, prep for waxing or standalone glow treatment.',
    category: 'BODY_CARE',
    duration: 40,
    price: 100000,
    waxTypes: [],
    isPopular: false,
  },
]

// ─── Products ─────────────────────────────────────────

export const PRODUCT_CATEGORIES = ['SKINCARE', 'AFTERCARE', 'WAX_KIT'] as const
export type ProductCategory = (typeof PRODUCT_CATEGORIES)[number]

export const PRODUCT_CATEGORY_LABELS: Record<ProductCategory, string> = {
  SKINCARE: 'Skincare',
  AFTERCARE: 'Aftercare',
  WAX_KIT: 'Wax Kits',
}

// Alias for shop page backwards compat
export const CATEGORY_LABELS = PRODUCT_CATEGORY_LABELS

export const CATEGORY_SLUGS: Record<string, string> = {
  skincare: 'SKINCARE',
  aftercare: 'AFTERCARE',
  'wax-kits': 'WAX_KIT',
}

export const CATEGORY_SLUG_BY_KEY: Record<string, string> = {
  SKINCARE: 'skincare',
  AFTERCARE: 'aftercare',
  WAX_KIT: 'wax-kits',
}

// ─── Delivery ─────────────────────────────────────────

export const DELIVERY = {
  valleyFee: 10000,      // NPR 100 in paisa
  otherFee: 15000,       // NPR 150
  freeThreshold: 500000, // NPR 5,000
  valleyDays: '1–2 days',
  otherDays: '3–5 days',
} as const

export const COD_LIMIT = 1500000 // NPR 15,000 in paisa

// ─── OTP ──────────────────────────────────────────────

export const OTP = {
  length: 6,
  expiryMinutes: 10,
  resendCooldownSeconds: 60,
  maxResends: 3,
} as const

// ─── Misc ─────────────────────────────────────────────

export const RECENTLY_VIEWED_MAX = 4
export const RECENTLY_VIEWED_DAYS = 7
