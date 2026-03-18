/**
 * Format price in NPR. Prices are stored as paisa (integers), so divide by 100.
 * e.g. 550000 paisa → "NPR 5,500"
 */
export function formatPrice(price: number): string {
  const npr = price / 100
  return `NPR ${npr.toLocaleString('en-NP')}`
}

/** Alias used throughout the codebase. */
export const formatNPR = formatPrice

/**
 * Format duration in minutes to a human-readable string.
 * e.g. 45 → "45 min", 60 → "1h", 90 → "1h 30m"
 */
export function formatDuration(minutes: number): string {
  if (minutes < 60) return `${minutes} min`
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  return m === 0 ? `${h}h` : `${h}h ${m}m`
}

/**
 * Get Cloudinary optimized image URL.
 */
export function getCloudinaryUrl(
  cloudinaryId: string,
  width: number,
  quality: string = 'auto',
): string {
  const cloudName = useRuntimeConfig().public.cloudinaryCloudName || 'dhknx0eac'
  return `https://res.cloudinary.com/${cloudName}/image/upload/f_auto,q_${quality},w_${width}/${cloudinaryId}`
}

/**
 * Generate responsive srcset for Cloudinary images.
 */
export function getCloudinarySrcSet(cloudinaryId: string): string {
  const widths = [400, 600, 800, 1200]
  return widths
    .map((w) => `${getCloudinaryUrl(cloudinaryId, w)} ${w}w`)
    .join(', ')
}

/**
 * Calculate delivery fee based on city and subtotal.
 */
export function calculateDeliveryFee(city: string, subtotal: number): number {
  // Subtotal and return values are in paisa (NPR × 100)
  if (subtotal >= 500000) return 0 // free above NPR 5,000

  const valleyCities = ['kathmandu', 'lalitpur', 'bhaktapur']
  return valleyCities.includes(city.toLowerCase()) ? 10000 : 15000 // NPR 100 / 150
}

/**
 * Format order ID in PP-YYMM-XXXX format.
 */
export function formatOrderId(orderId: string): string {
  return orderId.startsWith('PP-') ? orderId : `PP-${orderId}`
}

/**
 * Truncate text to a max length with ellipsis.
 */
export function truncate(text: string, maxLength: number): string {
  if (text.length <= maxLength) return text
  return text.slice(0, maxLength).trimEnd() + '…'
}
