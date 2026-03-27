export type FacilityCategory = 'BADMINTON' | 'GYM' | 'SAUNA'
export type ProgramCategory = 'BADMINTON' | 'GYM' | 'MEMBERSHIP'

export interface Program {
  id: string
  slug: string
  name: string
  description: string
  category: ProgramCategory
  duration: number // minutes per session
  sessionsPerWeek: number
  priceMonthly: number // paisa
  features: string[]
  images: string[] | null
  isPopular: boolean
  isActive: boolean
  sortOrder: number
  createdAt: string
  updatedAt: string
}

export interface Facility {
  id: string
  name: string
  category: FacilityCategory
  description: string
  features: string[]
  images: string[] | null
}

export interface CourtSlot {
  id: string
  court: number
  date: string // ISO date "YYYY-MM-DD"
  time: string // "HH:MM"
  duration: number // minutes
  isAvailable: boolean
}

export interface BookingItem {
  serviceId: string
  serviceName: string
  duration: number
  price: number
}

export interface BookingSlot {
  date: string // ISO date "YYYY-MM-DD"
  time: string // "HH:MM"
}

export interface Booking {
  id: string
  ref: string // BSA-YYMM-XXXX
  items: BookingItem[]
  slot?: BookingSlot
  customerName: string
  customerPhone: string
  customerEmail?: string
  notes?: string
  status: 'PENDING' | 'CONFIRMED' | 'COMPLETED' | 'CANCELLED' | 'NO_SHOW'
  total: number
  createdAt: string
}

export interface Enrollment {
  id: string
  enrollmentId: string // BSA-YYMM-XXXX
  programId: string
  programName: string
  customerName: string
  customerPhone: string
  customerEmail?: string
  startDate: string
  status: 'PENDING' | 'ACTIVE' | 'EXPIRED' | 'CANCELLED'
  total: number
  paymentMethod: 'ESEWA' | 'KHALTI' | 'COD'
  paymentRef?: string
  createdAt: string
}
