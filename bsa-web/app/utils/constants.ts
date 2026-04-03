// Bhaisepati Sports Academy | Constants & Static Data

// ─── Brand ────────────────────────────────────────────

export const BRAND = {
  name: 'Bhaisepati Sports Academy',
  shortName: 'BSA',
  tagline: 'Train Harder. Move Faster. Grow Stronger.',
  origin: 'Bhaisepati, Kathmandu, Nepal',
  phone: '9821357118',
  whatsapp: '9821357118',
  email: 'info@bsa.abinmaharjan.com.np',
  address: 'Bhaisepati, Lalitpur, Nepal',
  instagram: 'https://www.instagram.com/bhaisepatisportsacademy/',
  instagramHandle: '@bhaisepatisportsacademy',
  googleMaps: 'https://maps.app.goo.gl/MUW8SZTasKzDuWaA7',
  openingHours: 'Sunday–Friday, 6:00 AM – 9:00 PM',
  closedDay: 'Saturday',
} as const

// ─── Images (placeholder Unsplash) ────────────────────

export const IMAGES = {
  hero: 'https://images.unsplash.com/photo-1613914153966-fd0cf11e0e8b?auto=format&fit=crop&w=1920&q=80',
  heroAlt: 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?auto=format&fit=crop&w=1920&q=80',
  badmintonCourt: 'https://images.unsplash.com/photo-1613914153966-fd0cf11e0e8b?auto=format&fit=crop&w=1200&q=80',
  badmintonPlayer: 'https://images.unsplash.com/photo-1547347298-4074fc3086f0?auto=format&fit=crop&w=800&q=80',
  gym: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1200&q=80',
  gymTraining: 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=800&q=80',
  sauna: 'https://images.unsplash.com/photo-1520974048-a3a50c2b0eb0?auto=format&fit=crop&w=1200&q=80',
  recovery: 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=800&q=80',
  food: 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800&q=80',
  smoothie: 'https://images.unsplash.com/photo-1505252585461-04db1eb84625?auto=format&fit=crop&w=800&q=80',
  teamSport: 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&w=1200&q=80',
  aboutFacility: 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?auto=format&fit=crop&w=1200&q=80',
} as const

// Category-to-image mapping for program cards
export const PROGRAM_IMAGES: Record<string, string> = {
  BADMINTON: IMAGES.badmintonPlayer,
  FITNESS: IMAGES.gymTraining,
  RECOVERY: IMAGES.recovery,
}

// Category-to-image mapping for kitchen items
export const KITCHEN_IMAGES: Record<string, string> = {
  'pre-workout': IMAGES.food,
  'post-workout': IMAGES.food,
  'snacks': IMAGES.food,
  'drinks': IMAGES.smoothie,
}

// ─── Facility Categories ──────────────────────────────

export const FACILITY_CATEGORIES = ['BADMINTON', 'GYM', 'SAUNA'] as const
export type FacilityCategory = (typeof FACILITY_CATEGORIES)[number]

export const FACILITY_LABELS: Record<FacilityCategory, string> = {
  BADMINTON: 'Badminton Courts',
  GYM: 'Gym & Strength',
  SAUNA: 'Sauna & Steam',
}

export const FACILITY_ICONS: Record<FacilityCategory, string> = {
  BADMINTON: '🏸',
  GYM: '💪',
  SAUNA: '♨️',
}

// ─── Programs ─────────────────────────────────────────

export const PROGRAM_CATEGORIES = ['BADMINTON', 'FITNESS', 'RECOVERY'] as const
export type ProgramCategory = (typeof PROGRAM_CATEGORIES)[number]

export const PROGRAM_CATEGORY_LABELS: Record<ProgramCategory, string> = {
  BADMINTON: 'Badminton',
  FITNESS: 'Fitness & Conditioning',
  RECOVERY: 'Recovery',
}

export interface ProgramDef {
  id: string
  slug: string
  name: string
  description: string
  category: ProgramCategory
  level: 'beginner' | 'intermediate' | 'advanced' | 'all'
  ageGroup: string
  duration: string // e.g. "1 month", "3 months"
  sessionsPerWeek: number
  price: number // paisa
  isPopular: boolean
  features: string[]
}

export const PROGRAMS: ProgramDef[] = [
  {
    id: 'prog-01',
    slug: 'badminton-foundation',
    name: 'Badminton Foundation',
    description: 'Perfect for beginners. Learn grips, footwork, basic strokes, and court rules. Build a strong foundation for competitive play.',
    category: 'BADMINTON',
    level: 'beginner',
    ageGroup: 'All ages',
    duration: '1 month',
    sessionsPerWeek: 3,
    price: 300000, // NPR 3,000
    isPopular: true,
    features: ['Basic stroke techniques', 'Court movement & footwork', 'Rules & scoring', 'Equipment guidance'],
  },
  {
    id: 'prog-02',
    slug: 'badminton-intermediate',
    name: 'Intermediate Development',
    description: 'Refine your game. Advanced strokes, rally strategies, match play, and competitive mindset training.',
    category: 'BADMINTON',
    level: 'intermediate',
    ageGroup: 'All ages',
    duration: '1 month',
    sessionsPerWeek: 4,
    price: 500000, // NPR 5,000
    isPopular: true,
    features: ['Advanced stroke techniques', 'Rally strategy', 'Match play & doubles', 'Video analysis'],
  },
  {
    id: 'prog-03',
    slug: 'badminton-competitive',
    name: 'Advanced & Competitive',
    description: 'Tournament-ready training. Speed drills, endurance, tactical play, and match simulation under pressure.',
    category: 'BADMINTON',
    level: 'advanced',
    ageGroup: '13+',
    duration: '1 month',
    sessionsPerWeek: 5,
    price: 800000, // NPR 8,000
    isPopular: false,
    features: ['Tournament preparation', 'Speed & agility drills', 'Match simulation', 'Fitness integration'],
  },
  {
    id: 'prog-04',
    slug: 'youth-academy',
    name: 'Youth Academy',
    description: 'Age-appropriate training for junior players. Focus on fun, fundamentals, and building a love for the sport.',
    category: 'BADMINTON',
    level: 'beginner',
    ageGroup: 'Under 16',
    duration: '1 month',
    sessionsPerWeek: 3,
    price: 250000, // NPR 2,500
    isPopular: true,
    features: ['Fun-first approach', 'Age-appropriate drills', 'Mini tournaments', 'Physical literacy'],
  },
  {
    id: 'prog-05',
    slug: 'gym-membership',
    name: 'Gym & Strength Training',
    description: 'Full gym access with strength training equipment. Build power, prevent injuries, and improve athletic performance.',
    category: 'FITNESS',
    level: 'all',
    ageGroup: '16+',
    duration: '1 month',
    sessionsPerWeek: 6,
    price: 400000, // NPR 4,000
    isPopular: true,
    features: ['Full gym access', 'Strength equipment', 'Cardio machines', 'Flexible schedule'],
  },
  {
    id: 'prog-06',
    slug: 'full-membership',
    name: 'Full Membership Package',
    description: 'Complete access to everything BSA offers: badminton courts, gym, sauna & steam. The ultimate training package.',
    category: 'FITNESS',
    level: 'all',
    ageGroup: '16+',
    duration: '1 month',
    sessionsPerWeek: 7,
    price: 600000, // NPR 6,000
    isPopular: true,
    features: ['Unlimited court booking', 'Full gym access', 'Sauna & steam access', 'Priority scheduling'],
  },
]

// ─── Facilities ───────────────────────────────────────

export interface FacilityDef {
  id: string
  name: string
  category: FacilityCategory
  description: string
  features: string[]
  icon: string
}

export const FACILITIES: FacilityDef[] = [
  {
    id: 'fac-01',
    name: 'Badminton Courts',
    category: 'BADMINTON',
    description: 'Professional-grade badminton courts with proper lighting, regulation markings, and quality playing surface. Available for training and court booking.',
    features: ['Professional playing surface', 'Proper court lighting', 'Regulation markings', 'Equipment available', 'Court booking system'],
    icon: '🏸',
  },
  {
    id: 'fac-02',
    name: 'Gym & Strength Training',
    category: 'GYM',
    description: 'Fully equipped gym with modern strength training equipment. Designed for athletic conditioning and injury prevention.',
    features: ['Modern equipment', 'Free weights & machines', 'Cardio zone', 'Mirrors & ventilation', 'Personal training available'],
    icon: '💪',
  },
  {
    id: 'fac-03',
    name: 'Sauna & Steam Room',
    category: 'SAUNA',
    description: 'Recover faster after intense training sessions. Our sauna and steam facilities help with muscle recovery and relaxation.',
    features: ['Sauna therapy', 'Steam room', 'Muscle recovery', 'Post-workout relaxation', 'Clean & hygienic'],
    icon: '♨️',
  },
]

// ─── Stats (for homepage) ─────────────────────────────

export const STATS = [
  { value: '200+', label: 'Active Members' },
  { value: '5+', label: 'Expert Coaches' },
  { value: '3', label: 'Badminton Courts' },
  { value: '6', label: 'Years Experience' },
] as const

// ─── Testimonials ─────────────────────────────────────

export const TESTIMONIALS = [
  {
    name: 'Rajan Shrestha',
    role: 'Competitive Player',
    quote: 'BSA transformed my game. The coaching here is serious and the facilities are top-notch for Nepal.',
  },
  {
    name: 'Sita Maharjan',
    role: 'Youth Academy Parent',
    quote: 'My son loves coming here. The coaches know how to make training fun while teaching real skills.',
  },
  {
    name: 'Bikash Tamang',
    role: 'Gym Member',
    quote: 'Best gym in Bhaisepati. Clean, well-equipped, and the staff actually cares about your progress.',
  },
] as const

// ─── Court Booking ────────────────────────────────────

export const COURT_BOOKING = {
  minDuration: 60, // minutes
  maxDuration: 120,
  openTime: '06:00',
  closeTime: '21:00',
  pricePerHour: 50000, // NPR 500 in paisa
  discountPercentage: 15, // 15% discount on booking
  totalCourts: 3,
  timeSlots: [
    '06:00', '07:00', '08:00', '09:00', '10:00', '11:00',
    '12:00', '13:00', '14:00', '15:00', '16:00', '17:00',
    '18:00', '19:00',
  ],
} as const

// ─── OTP ──────────────────────────────────────────────

export const OTP = {
  length: 6,
  expiryMinutes: 10,
  resendCooldownSeconds: 60,
  maxResends: 3,
} as const

// ─── Schedule ─────────────────────────────────────────

export interface ScheduleSlot {
  day: string
  time: string
  program: string
  court: string
  coach: string
  level: 'beginner' | 'intermediate' | 'advanced' | 'all'
}

export const WEEKLY_SCHEDULE: ScheduleSlot[] = [
  { day: 'Sunday', time: '6:00 AM – 8:00 AM', program: 'Badminton Foundation', court: 'Court 1 & 2', coach: 'Coach Ramesh', level: 'beginner' },
  { day: 'Sunday', time: '8:00 AM – 10:00 AM', program: 'Intermediate Development', court: 'Court 1 & 2', coach: 'Coach Sunil', level: 'intermediate' },
  { day: 'Sunday', time: '4:00 PM – 6:00 PM', program: 'Youth Academy', court: 'Court 1', coach: 'Coach Ramesh', level: 'beginner' },
  { day: 'Monday', time: '6:00 AM – 8:00 AM', program: 'Advanced & Competitive', court: 'All Courts', coach: 'Coach Sunil', level: 'advanced' },
  { day: 'Monday', time: '4:00 PM – 6:00 PM', program: 'Badminton Foundation', court: 'Court 1 & 2', coach: 'Coach Ramesh', level: 'beginner' },
  { day: 'Tuesday', time: '6:00 AM – 8:00 AM', program: 'Intermediate Development', court: 'Court 1 & 2', coach: 'Coach Sunil', level: 'intermediate' },
  { day: 'Tuesday', time: '4:00 PM – 6:00 PM', program: 'Youth Academy', court: 'Court 1', coach: 'Coach Ramesh', level: 'beginner' },
  { day: 'Wednesday', time: '6:00 AM – 8:00 AM', program: 'Advanced & Competitive', court: 'All Courts', coach: 'Coach Sunil', level: 'advanced' },
  { day: 'Wednesday', time: '4:00 PM – 6:00 PM', program: 'Badminton Foundation', court: 'Court 1 & 2', coach: 'Coach Ramesh', level: 'beginner' },
  { day: 'Thursday', time: '6:00 AM – 8:00 AM', program: 'Intermediate Development', court: 'Court 1 & 2', coach: 'Coach Sunil', level: 'intermediate' },
  { day: 'Thursday', time: '4:00 PM – 6:00 PM', program: 'Youth Academy', court: 'Court 1', coach: 'Coach Ramesh', level: 'beginner' },
  { day: 'Friday', time: '6:00 AM – 8:00 AM', program: 'All Levels Open Play', court: 'All Courts', coach: 'Staff', level: 'all' },
  { day: 'Friday', time: '4:00 PM – 6:00 PM', program: 'Match Practice', court: 'All Courts', coach: 'Coach Sunil', level: 'intermediate' },
]

// Product categories (shop)
export type ProductCategory = 'EQUIPMENT' | 'APPAREL' | 'NUTRITION'

export const PRODUCT_CATEGORY_LABELS: Record<ProductCategory, string> = {
  EQUIPMENT: 'Equipment',
  APPAREL: 'Apparel',
  NUTRITION: 'Nutrition',
}

export const CATEGORY_LABELS = PRODUCT_CATEGORY_LABELS

export const CATEGORY_SLUGS: Record<string, string> = {
  equipment: 'EQUIPMENT',
  apparel: 'APPAREL',
  nutrition: 'NUTRITION',
}

export const CATEGORY_SLUG_BY_KEY: Record<string, string> = {
  EQUIPMENT: 'equipment',
  APPAREL: 'apparel',
  NUTRITION: 'nutrition',
}

// Payment
export const COD_LIMIT = 1500000 // NPR 15,000 in paisa

// Recently viewed products
export const RECENTLY_VIEWED_MAX = 4
export const RECENTLY_VIEWED_DAYS = 7

// ─── Kitchen ──────────────────────────────────────────

export interface KitchenItem {
  id: string
  name: string
  description: string
  price: number // paisa
  category: 'pre-workout' | 'post-workout' | 'snacks' | 'drinks'
  isPopular?: boolean
}

export const KITCHEN_CATEGORIES = {
  'pre-workout': 'Pre-Workout',
  'post-workout': 'Post-Workout Recovery',
  snacks: 'Snacks',
  drinks: 'Drinks & Shakes',
} as const

export const KITCHEN_MENU: KitchenItem[] = [
  // Pre-workout
  { id: 'banana-dates', name: 'Banana & Dates Pack', description: 'Natural energy boost with 2 ripe bananas and a handful of Medjool dates', price: 15000, category: 'pre-workout', isPopular: true },
  { id: 'oats-bowl', name: 'Overnight Oats Bowl', description: 'Rolled oats soaked overnight with honey, chia seeds, and seasonal fruit', price: 25000, category: 'pre-workout' },
  { id: 'granola-bar', name: 'Homemade Granola Bar', description: 'Oats, nuts, and honey pressed into a dense energy bar', price: 12000, category: 'pre-workout' },

  // Post-workout
  { id: 'protein-wrap', name: 'Chicken Protein Wrap', description: 'Grilled chicken, egg whites, veggies, and yogurt sauce in a wholewheat wrap', price: 35000, category: 'post-workout', isPopular: true },
  { id: 'dal-bhat', name: 'Recovery Dal Bhat', description: 'Classic Nepali dal bhat with lentils, rice, vegetables, and achaar', price: 30000, category: 'post-workout' },
  { id: 'egg-platter', name: 'Boiled Egg Platter', description: '4 boiled eggs with a sprinkle of black salt, chilli flakes, and crackers', price: 20000, category: 'post-workout' },

  // Snacks
  { id: 'peanut-butter-toast', name: 'Peanut Butter Toast', description: 'Whole-grain toast with natural peanut butter and banana slices', price: 18000, category: 'snacks' },
  { id: 'fruit-platter', name: 'Seasonal Fruit Platter', description: 'Mixed seasonal fruits from Nepal, freshly cut and served with chilli-salt', price: 22000, category: 'snacks', isPopular: true },
  { id: 'chana-chaat', name: 'Spiced Chickpea Chaat', description: 'Boiled chickpeas with onion, tomato, green chilli, and lemon juice', price: 15000, category: 'snacks' },

  // Drinks
  { id: 'protein-shake', name: 'Protein Shake', description: 'Whey protein blended with milk, banana, and peanut butter', price: 28000, category: 'drinks', isPopular: true },
  { id: 'green-smoothie', name: 'Green Recovery Smoothie', description: 'Spinach, cucumber, ginger, lemon, and apple blended fresh', price: 25000, category: 'drinks' },
  { id: 'lemon-electrolyte', name: 'Lemon Electrolyte Water', description: 'Chilled lemon water with Himalayan salt, honey, and tulsi', price: 10000, category: 'drinks' },
  { id: 'masala-tea', name: 'Masala Chiya', description: 'Traditional Nepali spiced milk tea, brewed to order', price: 8000, category: 'drinks' },
]
