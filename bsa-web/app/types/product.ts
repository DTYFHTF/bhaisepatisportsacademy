export interface Product {
  id: string
  slug: string
  name: string
  tagline?: string
  price: number
  compareAtPrice?: number
  category: 'EQUIPMENT' | 'APPAREL' | 'NUTRITION'
  description: string
  productDetails?: {
    ingredients?: string
    howToUse?: string
    suitableFor?: string
  }
  tags: string[]
  isActive: boolean
  seoTitle?: string
  seoDescription?: string
  variants: ProductVariant[]
  images: ProductImage[]
  pairedWith?: Product[]
  createdAt: string
  updatedAt: string
}

export interface ProductVariant {
  id: string
  productId: string
  label: string // e.g., "50ml", "100ml", "Kit"
  sku: string
  stock: number
  reservedStock: number
  priceOverride?: number
}

export interface ProductImage {
  id: string
  productId: string
  cloudinaryId: string
  url: string
  altText?: string
  order: number
}
