/**
 * Google Maps composable for delivery location selection.
 * Uses Maps JavaScript API + Places API + Geocoding API.
 */
/// <reference types="google.maps" />

declare global {
  interface Window {
    google?: typeof google
  }
}

let mapsLoaded = false
let mapsLoadPromise: Promise<void> | null = null

function loadGoogleMaps(apiKey: string): Promise<void> {
  if (mapsLoaded) return Promise.resolve()
  if (mapsLoadPromise) return mapsLoadPromise

  mapsLoadPromise = new Promise((resolve, reject) => {
    if (typeof window === 'undefined') return reject(new Error('SSR'))

    const script = document.createElement('script')
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&v=weekly`
    script.async = true
    script.defer = true
    script.onload = () => {
      mapsLoaded = true
      resolve()
    }
    script.onerror = () => reject(new Error('Failed to load Google Maps'))
    document.head.appendChild(script)
  })

  return mapsLoadPromise
}

export interface DeliveryLocation {
  lat: number
  lng: number
  formattedAddress: string
  city: string
  district: string
  landmark: string
}

export function useGoogleMaps() {
  const config = useRuntimeConfig()
  const apiKey = config.public.googleMapsKey as string

  const loading = ref(false)
  const error = ref<string | null>(null)
  const location = ref<DeliveryLocation | null>(null)

  let map: google.maps.Map | null = null
  let marker: google.maps.marker.AdvancedMarkerElement | google.maps.Marker | null = null
  let autocomplete: google.maps.places.Autocomplete | null = null

  async function initMap(element: HTMLElement, initialCenter?: { lat: number; lng: number }) {
    if (!apiKey) {
      error.value = 'Google Maps API key not configured'
      return
    }

    await loadGoogleMaps(apiKey)

    const center = initialCenter || { lat: 27.7172, lng: 85.324 } // Kathmandu default

    map = new google.maps.Map(element, {
      center,
      zoom: 14,
      disableDefaultUI: true,
      zoomControl: true,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false,
    })

    marker = new google.maps.Marker({
      map,
      position: center,
      draggable: true,
    })

    // Update location when marker is dragged
    marker.addListener('dragend', () => {
      const pos = (marker as google.maps.Marker).getPosition()
      if (pos) {
        reverseGeocode(pos.lat(), pos.lng())
      }
    })

    // Update location when map is clicked
    map.addListener('click', (e: google.maps.MapMouseEvent) => {
      if (e.latLng) {
        (marker as google.maps.Marker).setPosition(e.latLng)
        reverseGeocode(e.latLng.lat(), e.latLng.lng())
      }
    })
  }

  function initAutocomplete(inputElement: HTMLInputElement) {
    if (!window.google?.maps?.places) return

    autocomplete = new google.maps.places.Autocomplete(inputElement, {
      componentRestrictions: { country: 'np' },
      fields: ['geometry', 'formatted_address', 'address_components'],
    })

    autocomplete.addListener('place_changed', () => {
      const place = autocomplete!.getPlace()
      if (!place.geometry?.location) return

      const lat = place.geometry.location.lat()
      const lng = place.geometry.location.lng()

      if (map) {
        map.setCenter({ lat, lng })
        map.setZoom(16)
      }
      if (marker) {
        (marker as google.maps.Marker).setPosition({ lat, lng })
      }

      location.value = extractLocationData(place)
    })
  }

  async function getCurrentLocation() {
    if (!navigator.geolocation) {
      error.value = 'Geolocation not supported by your browser'
      return
    }

    loading.value = true
    error.value = null

    try {
      const pos = await new Promise<GeolocationPosition>((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(resolve, reject, {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 300000, // 5 minutes cache
        })
      })

      const { latitude, longitude } = pos.coords

      if (map) {
        map.setCenter({ lat: latitude, lng: longitude })
        map.setZoom(16)
      }
      if (marker) {
        (marker as google.maps.Marker).setPosition({ lat: latitude, lng: longitude })
      }

      await reverseGeocode(latitude, longitude)
    } catch (e: any) {
      if (e.code === 1) error.value = 'Location permission denied'
      else if (e.code === 2) error.value = 'Location unavailable'
      else error.value = 'Could not get your location'
    } finally {
      loading.value = false
    }
  }

  async function reverseGeocode(lat: number, lng: number) {
    if (!window.google?.maps) return

    const geocoder = new google.maps.Geocoder()

    try {
      const response = await geocoder.geocode({ location: { lat, lng } })
      if (response.results[0]) {
        location.value = {
          lat,
          lng,
          formattedAddress: response.results[0].formatted_address,
          city: extractComponent(response.results[0], 'locality')
            || extractComponent(response.results[0], 'administrative_area_level_2')
            || '',
          district: extractComponent(response.results[0], 'administrative_area_level_1') || '',
          landmark: '',
        }
      }
    } catch {
      // Geocoding failed silently — user can still type address manually
    }
  }

  function extractComponent(
    result: google.maps.GeocoderResult,
    type: string,
  ): string {
    const component = result.address_components?.find((c) => c.types.includes(type))
    return component?.long_name || ''
  }

  function extractLocationData(place: google.maps.places.PlaceResult): DeliveryLocation {
    const lat = place.geometry!.location!.lat()
    const lng = place.geometry!.location!.lng()
    const components = place.address_components || []

    return {
      lat,
      lng,
      formattedAddress: place.formatted_address || '',
      city: components.find((c) => c.types.includes('locality'))?.long_name
        || components.find((c) => c.types.includes('administrative_area_level_2'))?.long_name
        || '',
      district: components.find((c) => c.types.includes('administrative_area_level_1'))?.long_name || '',
      landmark: '',
    }
  }

  function setLandmark(value: string) {
    if (location.value) {
      location.value.landmark = value
    }
  }

  function cleanup() {
    if (autocomplete) {
      google.maps.event.clearInstanceListeners(autocomplete)
      autocomplete = null
    }
    map = null
    marker = null
  }

  return {
    loading,
    error,
    location,
    initMap,
    initAutocomplete,
    getCurrentLocation,
    setLandmark,
    cleanup,
  }
}
