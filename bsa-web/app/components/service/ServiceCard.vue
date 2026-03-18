<script setup lang="ts">
import { Clock, Plus, Check } from 'lucide-vue-next'
import { formatPrice, formatDuration } from '~/utils/formatters'
import type { Service } from '~/types/service'

interface Props {
  service: Service
}

defineProps<Props>()

const booking = useBookingStore()
const { trackServiceAdd, trackServiceRemove } = useUmami()

const activeImage = ref(0)
</script>

<template>
  <div class="group rounded-xl border border-border bg-canvas overflow-hidden transition-shadow hover:shadow-md">
    <!-- Image gallery -->
    <div v-if="service.images && service.images.length" class="relative aspect-[4/3] overflow-hidden bg-surface">
      <img
        :src="service.images[activeImage]"
        :alt="service.name"
        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
        loading="lazy"
      />
      <!-- Dot navigation -->
      <div v-if="service.images.length > 1" class="absolute bottom-2 left-0 right-0 flex justify-center gap-1.5">
        <button
          v-for="(_, i) in service.images"
          :key="i"
          class="h-1.5 rounded-full transition-all"
          :class="i === activeImage ? 'w-4 bg-white' : 'w-1.5 bg-white/50'"
          @click.prevent="activeImage = i"
        />
      </div>
      <!-- Popular badge -->
      <div v-if="service.isPopular" class="absolute top-2 left-2">
        <span class="rounded-full bg-accent px-2.5 py-1 text-xs font-medium text-white">Popular</span>
      </div>
    </div>

    <!-- Content -->
    <div class="p-5">
      <!-- Category badge -->
      <span class="text-label text-accent">
        {{ service.category.replace('_', ' ') }}
      </span>

      <!-- Name & description -->
      <h3 class="mt-2 text-lg font-medium text-ink">{{ service.name }}</h3>
      <p class="mt-1 text-sm text-ink-muted line-clamp-2">{{ service.description }}</p>

      <!-- Meta -->
      <div class="mt-3 flex items-center gap-4 text-sm text-ink-muted">
        <span class="flex items-center gap-1">
          <Clock class="h-4 w-4" />
          {{ formatDuration(service.duration) }}
        </span>
        <span class="font-medium text-ink">{{ formatPrice(service.price) }}</span>
      </div>

      <!-- Wax types -->
      <div v-if="service.waxTypes && service.waxTypes.length > 1" class="mt-3 flex flex-wrap gap-1.5">
        <span
          v-for="wax in service.waxTypes"
          :key="wax"
          class="rounded-full bg-peach-50 px-2.5 py-0.5 text-xs text-peach-800"
        >
          {{ wax }}
        </span>
      </div>

      <!-- Action -->
      <button
        class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-medium transition-colors"
        :class="
          booking.hasService(service.id)
            ? 'bg-peach-100 text-peach-800'
            : 'bg-accent text-white hover:bg-accent-hover'
        "
        @click="
          booking.hasService(service.id)
            ? (booking.removeService(service.id), trackServiceRemove(service.name))
            : (booking.addService({ serviceId: service.id, serviceName: service.name, duration: service.duration, price: service.price }), trackServiceAdd(service.name, service.category, service.price / 100))
        "
      >
        <Check v-if="booking.hasService(service.id)" class="h-4 w-4" />
        <Plus v-else class="h-4 w-4" />
        {{ booking.hasService(service.id) ? 'Added' : 'Add to booking' }}
      </button>
    </div>
  </div>
</template>
