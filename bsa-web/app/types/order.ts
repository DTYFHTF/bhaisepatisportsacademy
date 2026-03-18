export type OrderStatus =
  | 'PENDING'
  | 'CONFIRMED'
  | 'PACKED'
  | 'DISPATCHED'
  | 'DELIVERED'
  | 'CANCELLED'

export type PaymentMethod = 'ESEWA' | 'COD'

export interface Order {
  id: string
  orderId: string // PP-YYMM-XXXX
  phoneHash: string
  customerName: string
  address: string
  city: string
  deliveryNote?: string
  latitude?: number | null
  longitude?: number | null
  formattedAddress?: string
  nearestLandmark?: string
  subtotal: number
  deliveryFee: number
  total: number
  paymentMethod: PaymentMethod
  paymentRef?: string
  status: OrderStatus
  courier?: string | null
  courierTrackingId?: string | null
  courierTrackingUrl?: string | null
  items: OrderItem[]
  statusHistory: OrderStatusHistory[]
  createdAt: string
  updatedAt: string
}

export interface OrderItem {
  id: string
  orderId: string
  productId: string
  variantId: string
  quantity: number
  unitPrice: number
  product?: {
    name: string
    images: { url: string }[]
  }
  variant?: {
    label: string
  }
}

export interface OrderStatusHistory {
  id: string
  orderId: string
  status: OrderStatus
  note?: string
  changedBy?: string
  changedAt: string
}

export const ORDER_STATUS_LABELS: Record<OrderStatus, string> = {
  PENDING: 'Order Pending',
  CONFIRMED: 'Confirmed',
  PACKED: 'Packed',
  DISPATCHED: 'Dispatched',
  DELIVERED: 'Delivered',
  CANCELLED: 'Cancelled',
}
