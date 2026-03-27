<script setup lang="ts">
import { Clock, Plus, Check } from 'lucide-vue-next'
import { formatPrice, formatDuration } from '~/utils/formatters'
import type { Program } from '~/types/service'

interface Props {
  service: Program
}

defineProps<Props>()

const booking = useBookingStore()
</script>

<template>
  <div class="group rounded-2xl border border-border bg-surface overflow-hidden transition-all duration-300 hover:border-accent/30">
    <!-- Image gallery -->
    <div v-if="service.images && service.images.length" class="relative aspect-[4/3] overflow-hidden bg-canvas">
      <img
        :src="service.images[0]"
        :alt="service.name"
        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
        loading="lazy"
      />
      <!-- Popular badge -->
      <div v-if="service.isPopular" class="absolute top-2 left-2">
        <span class="rounded-full bg-accent px-2.5 py-1 text-xs font-bold text-canvas uppercase tracking-wider">Popular</span>
      </div>
    </div>

    <!-- Content -->
    <div class="p-5">
      <!-- Category badge -->
      <span class="inline-block rounded-full bg-accent/10 px-3 py-1 text-xs font-medium uppercase tracking-wider text-accent">
        {{ service.category }}
      </span>

      <!-- Name & description -->
      <h3 class="mt-2 text-lg font-bold text-ink">{{ service.name }}</h3>
      <p class="mt-1 text-sm text-ink-muted line-clamp-2">{{ service.description }}</p>

      <!-- Meta -->
      <div class="mt-3 flex items-center gap-4 text-sm text-ink-muted">
        <span class="flex items-center gap-1">
          <Clock class="h-4 w-4 text-accent" />
          {{ formatDuration(service.duration) }}
        </span>
        <span class="font-bold text-ink">{{ formatPrice(service.priceMonthly ?? service.price) }}</span>
      </div>

      <!-- Action -->
      <button
        class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-bold uppercase tracking-wider transition-colors"
        :class="
          booking.hasService(service.id)
            ? 'bg-accent/10 text-accent'
            : 'bg-accent text-canvas hover:bg-accent-hover'
        "
        @click="
          booking.hasService(service.id)
            ? booking.removeService(service.id)
            : booking.addService({ serviceId: service.id, serviceName: service.name, duration: service.duration, price: service.priceMonthly ?? service.price })
        "
      >
        <Check v-if="booking.hasService(service.id)" class="h-4 w-4" />
        <Plus v-else class="h-4 w-4" />
        {{ booking.hasService(service.id) ? 'Added' : 'Add to Booking' }}
      </button>
    </div>
  </div>
</template>
