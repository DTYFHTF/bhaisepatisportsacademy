export type ServiceCategory = 'WAXING' | 'FACIAL' | 'BODY_CARE' | 'BROW'

export interface Service {
  id: string
  slug: string
  name: string
  description: string
  category: ServiceCategory
  duration: number // minutes
  price: number // paisa
  waxTypes: string[]
  images: string[] | null
  isPopular: boolean
  isActive: boolean
  sortOrder: number
  createdAt: string
  updatedAt: string
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
  ref: string
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
