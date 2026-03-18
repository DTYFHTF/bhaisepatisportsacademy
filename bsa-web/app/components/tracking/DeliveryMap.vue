<script setup lang="ts">
/// <reference types="google.maps" />
import { useGoogleMaps } from '~/composables/useGoogleMaps'

const props = defineProps<{
  lat: number
  lng: number
  status: string
}>()

const config = useRuntimeConfig()
const { loadGoogleMaps } = useGoogleMaps()
const mapEl = ref<HTMLElement | null>(null)
const eta = ref<string | null>(null)

// Kathmandu store coordinates (used as directions origin)
const STORE = {
  lat: config.public.storeLat as number,
  lng: config.public.storeLng as number,
}

const isOutForDelivery = computed(() =>
  ['DISPATCHED', 'OUT_FOR_DELIVERY'].includes(props.status),
)

// Open directions in Google Maps (for customer)
const directionsUrl = computed(
  () =>
    `https://www.google.com/maps/dir/?api=1&origin=${STORE.lat},${STORE.lng}&destination=${props.lat},${props.lng}&travelmode=driving`,
)

onMounted(async () => {
  if (!mapEl.value || !config.public.googleMapsKey) return

  try {
    await loadGoogleMaps(config.public.googleMapsKey as string)

    const map = new google.maps.Map(mapEl.value, {
      center: { lat: props.lat, lng: props.lng },
      zoom: 15,
      disableDefaultUI: true,
      zoomControl: true,
      mapTypeControl: false,
      streetViewControl: false,
    })

    // Customer delivery pin
    new google.maps.Marker({
      position: { lat: props.lat, lng: props.lng },
      map,
      title: 'Your Delivery Address',
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 10,
        fillColor: '#A69885',
        fillOpacity: 1,
        strokeWeight: 3,
        strokeColor: '#1A1A1A',
      },
    })

    // If out for delivery, render the actual route from store
    if (isOutForDelivery.value) {
      const directionsService = new google.maps.DirectionsService()
      const directionsRenderer = new google.maps.DirectionsRenderer({
        map,
        suppressMarkers: false,
        polylineOptions: {
          strokeColor: '#A69885',
          strokeWeight: 4,
          strokeOpacity: 0.85,
        },
      })

      directionsService.route(
        {
          origin: { lat: STORE.lat, lng: STORE.lng },
          destination: { lat: props.lat, lng: props.lng },
          travelMode: google.maps.TravelMode.DRIVING,
        },
        (result, status) => {
          if (status === google.maps.DirectionsStatus.OK && result) {
            directionsRenderer.setDirections(result)
            const leg = result.routes[0]?.legs[0]
            if (leg?.duration?.text) {
              eta.value = leg.duration.text
            }
          }
        },
      )
    }
  }
  catch (e) {
    // Maps failed silently — fallback to static embed link
  }
})
</script>

<template>
  <div class="overflow-hidden rounded-lg border border-border">
    <!-- Status header -->
    <div
      v-if="isOutForDelivery"
      class="flex items-center gap-2 bg-accent/10 px-4 py-3 text-sm font-medium text-ink"
    >
      <span class="h-2 w-2 animate-pulse rounded-full bg-accent" />
      Your order is out for delivery
      <span v-if="eta" class="ml-auto text-ink-muted">~{{ eta }} away</span>
    </div>

    <!-- Map canvas -->
    <div ref="mapEl" class="h-56 w-full bg-surface" />

    <!-- Open in Maps link -->
    <div class="flex items-center justify-between border-t border-border px-4 py-2.5">
      <span class="text-xs text-ink-muted">Delivery destination</span>
      <a
        :href="isOutForDelivery ? directionsUrl : `https://www.google.com/maps?q=${lat},${lng}`"
        target="_blank"
        rel="noopener noreferrer"
        class="text-xs font-medium text-accent underline-offset-2 hover:underline"
      >
        {{ isOutForDelivery ? 'View route →' : 'Open in Maps →' }}
      </a>
    </div>
  </div>
</template>
