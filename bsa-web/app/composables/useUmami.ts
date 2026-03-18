/**
 * Thin wrapper around window.umami.track for type-safe event tracking.
 * All calls are no-ops when Umami hasn't loaded (SSR, ad-blockers, dev without env).
 */

type UmamiEventData = Record<string, string | number | boolean>

function track(event: string, data?: UmamiEventData) {
  if (import.meta.server) return
  if (typeof window !== 'undefined' && (window as any).umami) {
    ;(window as any).umami.track(event, data)
  }
}

export function useUmami() {
  return {
    // ── Services ──────────────────────────────────────────────────────────────
    trackServiceAdd(serviceName: string, category: string, priceNpr: number) {
      track('service_add', { service: serviceName, category, price_npr: priceNpr })
    },
    trackServiceRemove(serviceName: string) {
      track('service_remove', { service: serviceName })
    },

    // ── Booking funnel ────────────────────────────────────────────────────────
    trackBookingStepDetails() {
      track('booking_step_details')
    },
    trackBookingStepConfirm(totalNpr: number, serviceCount: number) {
      track('booking_step_confirm', { total_npr: totalNpr, services: serviceCount })
    },
    trackBookingConfirmed(totalNpr: number, serviceCount: number) {
      track('booking_confirmed', { total_npr: totalNpr, services: serviceCount })
    },

    // ── Products ──────────────────────────────────────────────────────────────
    trackProductAddToCart(productName: string, priceNpr: number) {
      track('product_add_cart', { product: productName, price_npr: priceNpr })
    },
    trackCheckoutStart(totalNpr: number) {
      track('checkout_start', { total_npr: totalNpr })
    },
    trackCheckoutComplete(totalNpr: number) {
      track('checkout_complete', { total_npr: totalNpr })
    },

    // ── Generic ───────────────────────────────────────────────────────────────
    track,
  }
}
