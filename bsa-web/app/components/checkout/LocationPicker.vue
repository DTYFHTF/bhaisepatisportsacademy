<script setup lang="ts">
import { MapPin, LocateFixed, Search, Map } from 'lucide-vue-next'
import type { DeliveryLocation } from '~/composables/useGoogleMaps'

const emit = defineEmits<{
  'update:location': [location: DeliveryLocation]
}>()

const props = defineProps<{
  location: DeliveryLocation | null
}>()

const { loading, error, location, initMap, initAutocomplete, getCurrentLocation, setLandmark, cleanup } = useGoogleMaps()

const mapContainer = ref<HTMLElement | null>(null)
const searchInput = ref<HTMLInputElement | null>(null)
const landmarkInput = ref('')
const expanded = ref(true)

watch(location, (loc) => {
  if (loc) {
    emit('update:location', loc)
  }
})

watch(landmarkInput, (val) => {
  setLandmark(val)
  if (location.value) {
    emit('update:location', { ...location.value, landmark: val })
  }
})

async function toggleMap() {
  expanded.value = !expanded.value
  if (expanded.value) {
    await nextTick()
    if (mapContainer.value) {
      const initial = props.location
        ? { lat: props.location.lat, lng: props.location.lng }
        : undefined
      await initMap(mapContainer.value, initial)
    }
    if (searchInput.value) {
      initAutocomplete(searchInput.value)
    }
  }
}

async function handleGPS() {
  if (!expanded.value) {
    expanded.value = true
    await nextTick()
    if (mapContainer.value) {
      await initMap(mapContainer.value)
    }
    if (searchInput.value) {
      initAutocomplete(searchInput.value)
    }
  }
  await getCurrentLocation()
}

onMounted(async () => {
  await nextTick()
  if (mapContainer.value) {
    const initial = props.location
      ? { lat: props.location.lat, lng: props.location.lng }
      : undefined
    await initMap(mapContainer.value, initial)
  }
  if (searchInput.value) {
    initAutocomplete(searchInput.value)
  }
})

onBeforeUnmount(() => {
  cleanup()
})
</script>

<template>
  <div class="space-y-3">
    <div class="flex items-center gap-2">
      <button
        type="button"
        class="flex items-center gap-2 text-sm text-accent hover:text-accent/80 transition-colors"
        @click="toggleMap"
      >
        <Map class="h-4 w-4" />
        {{ expanded ? 'Hide map' : 'Show map' }}
      </button>

      <button
        type="button"
        class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-semibold transition-colors"
        :disabled="loading"
        @click="handleGPS"
      >
        <LocateFixed class="h-4 w-4" :class="{ 'animate-pulse': loading }" />
        {{ loading ? 'Locating...' : 'Use my Current Location' }}
      </button>
    </div>

    <p v-if="error" class="text-sm text-error">{{ error }}</p>

    <div v-if="expanded" class="space-y-3">
      <!-- Search input -->
      <div class="relative">
        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-faint" />
        <input
          ref="searchInput"
          type="text"
          placeholder="Search for a place..."
          class="w-full border border-border bg-transparent py-2.5 pl-9 pr-4 text-sm text-ink placeholder:text-ink-faint focus:border-ink focus:outline-none"
        />
      </div>

      <!-- Map -->
      <div
        ref="mapContainer"
        class="h-52 w-full border border-border bg-surface"
      />

      <p v-if="location" class="text-sm text-ink-muted">
        <MapPin class="mr-1 inline h-3.5 w-3.5" />
        {{ location.formattedAddress }}
      </p>
    </div>

    <!-- Landmark -->
    <div class="flex flex-col gap-1.5">
      <label class="text-label text-ink-muted">
        Nearest Landmark (optional)
      </label>
      <input
        v-model="landmarkInput"
        type="text"
        placeholder="e.g. Near Bhatbhateni, opposite City Hospital"
        class="w-full border border-border bg-transparent px-4 py-3 text-base text-ink placeholder:text-ink-faint focus:border-ink focus:outline-none transition-colors duration-fast"
      />
    </div>
  </div>
</template>
