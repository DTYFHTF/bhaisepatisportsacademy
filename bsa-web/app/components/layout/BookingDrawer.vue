<script setup lang="ts">
import { X, Calendar, Trash2, Clock } from 'lucide-vue-next'
import { formatPrice, formatDuration } from '~/utils/formatters'

const booking = useBookingStore()
</script>

<template>
  <UiAppSheet :open="booking.isOpen" side="right" @close="booking.closeDrawer()">
    <div class="flex h-full flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-border px-4 py-4">
        <h2 class="text-heading-md">Your Booking</h2>
        <button
          class="p-1 text-ink-muted hover:text-ink"
          aria-label="Close booking"
          @click="booking.closeDrawer()"
        >
          <X class="h-5 w-5" />
        </button>
      </div>

      <!-- Empty state -->
      <div
        v-if="booking.isEmpty"
        class="flex flex-1 flex-col items-center justify-center px-4"
      >
        <Calendar class="h-12 w-12 text-ink-faint mb-4" />
        <p class="text-ink-muted">No services selected yet.</p>
        <NuxtLink
          to="/services"
          class="mt-4 text-sm text-accent underline underline-offset-4"
          @click="booking.closeDrawer()"
        >
          Browse services
        </NuxtLink>
      </div>

      <!-- Items -->
      <div v-else class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
        <div
          v-for="item in booking.items"
          :key="item.serviceId"
          class="flex items-start justify-between rounded-lg border border-border p-3"
        >
          <div>
            <p class="text-sm font-medium">{{ item.serviceName }}</p>
            <div class="mt-1 flex items-center gap-3 text-xs text-ink-muted">
              <span class="flex items-center gap-1">
                <Clock class="h-3 w-3" />
                {{ formatDuration(item.duration) }}
              </span>
              <span>{{ formatPrice(item.price) }}</span>
            </div>
          </div>
          <button
            class="p-1 text-ink-faint hover:text-red-500"
            :aria-label="`Remove ${item.serviceName}`"
            @click="booking.removeService(item.serviceId)"
          >
            <Trash2 class="h-4 w-4" />
          </button>
        </div>
      </div>

      <!-- Footer -->
      <div v-if="!booking.isEmpty" class="border-t border-border px-4 py-4 space-y-3">
        <!-- Summary -->
        <div class="flex items-center justify-between text-sm">
          <span class="text-ink-muted">
            {{ booking.itemCount }} service{{ booking.itemCount > 1 ? 's' : '' }}
            · {{ formatDuration(booking.totalDuration) }}
          </span>
          <span class="text-base font-medium">{{ formatPrice(booking.total) }}</span>
        </div>

        <NuxtLink to="/book" @click="booking.closeDrawer()">
          <UiAppButton variant="primary" size="lg" class="w-full">
            Book Appointment
          </UiAppButton>
        </NuxtLink>
      </div>
    </div>
  </UiAppSheet>
</template>
