<script setup lang="ts">
import { Clock, Sparkles } from 'lucide-vue-next'
import { formatPrice, formatDuration } from '~/utils/formatters'
import { SERVICES } from '~/utils/constants'

interface Props {
  combo: {
    name: string
    tagline: string
    services: { id: string; name: string }[]
    originalTotal: number
    comboPrice: number
    savings: number
    totalDuration: number
    serviceIds: string[]
  }
}

defineProps<Props>()

const booking = useBookingStore()

function addCombo(serviceIds: string[]) {
  for (const id of serviceIds) {
    if (!booking.hasService(id)) {
      const s = SERVICES.find((svc) => svc.id === id)
      if (s) {
        booking.addService({
          serviceId: s.id,
          serviceName: s.name,
          duration: s.duration,
          price: s.price,
        })
      }
    }
  }
}
</script>

<template>
  <div class="rounded-xl border border-peach-200 bg-white p-5 hover:shadow-md transition-shadow">
    <!-- Badge -->
    <div class="flex items-center justify-between">
      <span class="flex items-center gap-1 text-label text-accent">
        <Sparkles class="h-3.5 w-3.5" />
        Combo
      </span>
      <span class="rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">
        Save {{ formatPrice(combo.savings) }}
      </span>
    </div>

    <!-- Name & tagline -->
    <h3 class="mt-2 text-lg font-medium text-ink">{{ combo.name }}</h3>
    <p class="mt-1 text-sm text-ink-muted">{{ combo.tagline }}</p>

    <!-- Services list -->
    <ul class="mt-3 space-y-1">
      <li
        v-for="s in combo.services"
        :key="s.id"
        class="text-sm text-ink-muted flex items-center gap-2"
      >
        <span class="h-1 w-1 rounded-full bg-accent" />
        {{ s.name }}
      </li>
    </ul>

    <!-- Meta -->
    <div class="mt-4 flex items-center gap-4 text-sm">
      <span class="flex items-center gap-1 text-ink-muted">
        <Clock class="h-4 w-4" />
        {{ formatDuration(combo.totalDuration) }}
      </span>
      <div class="flex items-center gap-2">
        <span class="line-through text-ink-faint">{{ formatPrice(combo.originalTotal) }}</span>
        <span class="font-medium text-ink">{{ formatPrice(combo.comboPrice) }}</span>
      </div>
    </div>

    <!-- Action -->
    <button
      class="mt-4 w-full rounded-lg bg-accent py-2.5 text-sm font-medium text-white hover:bg-accent-hover transition-colors"
      @click="addCombo(combo.serviceIds)"
    >
      Add Combo to Booking
    </button>
  </div>
</template>
