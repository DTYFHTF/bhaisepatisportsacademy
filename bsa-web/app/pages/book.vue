<script setup lang="ts">
import { Calendar, Clock, ArrowLeft, Plus, Phone } from 'lucide-vue-next'
import { formatPrice, formatDuration } from '~/utils/formatters'
import { BRAND } from '~/utils/constants'
import type { Service } from '~/types/service'

useSeoMeta({
  title: 'Book Appointment — Bhaisepati Sports Academy',
  description: 'Book your waxing appointment at Bhaisepati Sports Academy, Kathmandu. Select services, pick a date, and confirm.',
})

definePageMeta({ ssr: false })

const config = useRuntimeConfig()
const { data: services } = await useFetch<Service[]>(`${config.public.apiBase}/services`, {
  default: () => [],
})

const booking = useBookingStore()
const { trackBookingStepDetails, trackBookingStepConfirm, trackBookingConfirmed } = useUmami()
const step = ref<'services' | 'details' | 'confirm'>('services')

const customerName = ref('')
const customerPhone = ref('')
const preferredDate = ref('')
const preferredTime = ref('')
const notes = ref('')

const availableTimes = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30']

const minDate = computed(() => {
  const d = new Date()
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

const canProceed = computed(() => {
  if (step.value === 'services') return !booking.isEmpty
  if (step.value === 'details') {
    return customerName.value.trim() && customerPhone.value.trim().length === 10 && preferredDate.value && preferredTime.value
  }
  return true
})

function nextStep() {
  if (step.value === 'services') {
    step.value = 'details'
    trackBookingStepDetails()
  } else if (step.value === 'details') {
    booking.setSlot({ date: preferredDate.value, time: preferredTime.value })
    step.value = 'confirm'
    trackBookingStepConfirm(booking.total / 100, booking.itemCount)
  }
}

function goBack() {
  if (step.value === 'details') step.value = 'services'
  else if (step.value === 'confirm') step.value = 'details'
}

function confirmBooking() {
  trackBookingConfirmed(booking.total / 100, booking.itemCount)
  const message = `Hi! I'd like to book at Bhaisepati Sports Academy.\n\nServices: ${booking.items.map((i) => i.serviceName).join(', ')}\nDate: ${preferredDate.value}\nTime: ${preferredTime.value}\nName: ${customerName.value}\nPhone: ${customerPhone.value}${notes.value ? `\nNotes: ${notes.value}` : ''}`
  const encoded = encodeURIComponent(message)
  window.open(`https://wa.me/977${BRAND.phone}?text=${encoded}`, '_blank')
}
</script>

<template>
  <div class="min-h-[80vh]">
    <!-- Header -->
    <section class="bg-peach-50 border-b border-peach-200">
      <div class="section-container py-8">
        <p class="text-label text-accent mb-1">Step {{ step === 'services' ? 1 : step === 'details' ? 2 : 3 }} of 3</p>
        <h1 class="text-heading-xl text-ink">
          {{ step === 'services' ? 'Select Services' : step === 'details' ? 'Your Details' : 'Confirm Booking' }}
        </h1>
      </div>
    </section>

    <div class="section-container py-8">
      <div class="grid lg:grid-cols-3 gap-8">
        <!-- Main content -->
        <div class="lg:col-span-2">
          <!-- Step: Services -->
          <div v-if="step === 'services'">
            <ServiceGrid :services="services ?? []" :columns="2" />
          </div>

          <!-- Step: Details -->
          <div v-else-if="step === 'details'" class="max-w-md space-y-5">
            <button class="flex items-center gap-1 text-sm text-ink-muted hover:text-ink mb-4" @click="goBack">
              <ArrowLeft class="h-4 w-4" /> Back to services
            </button>

            <div>
              <label for="name" class="text-label mb-1 block">Full Name</label>
              <input
                id="name"
                v-model="customerName"
                type="text"
                class="w-full rounded-lg border border-border bg-canvas px-4 py-3 text-sm focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                placeholder="Your name"
              />
            </div>

            <div>
              <label for="phone" class="text-label mb-1 block">Phone Number</label>
              <input
                id="phone"
                v-model="customerPhone"
                type="tel"
                maxlength="10"
                class="w-full rounded-lg border border-border bg-canvas px-4 py-3 text-sm focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                placeholder="98XXXXXXXX"
              />
            </div>

            <div>
              <label for="date" class="text-label mb-1 block">Preferred Date</label>
              <input
                id="date"
                v-model="preferredDate"
                type="date"
                :min="minDate"
                class="w-full rounded-lg border border-border bg-canvas px-4 py-3 text-sm focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
              />
            </div>

            <div>
              <label for="time" class="text-label mb-1 block">Preferred Time</label>
              <div class="grid grid-cols-4 gap-2">
                <button
                  v-for="t in availableTimes"
                  :key="t"
                  class="rounded-lg border px-3 py-2 text-sm transition-colors"
                  :class="preferredTime === t ? 'border-accent bg-accent text-white' : 'border-border hover:border-peach-300'"
                  @click="preferredTime = t"
                >
                  {{ t }}
                </button>
              </div>
            </div>

            <div>
              <label for="notes" class="text-label mb-1 block">Notes (optional)</label>
              <textarea
                id="notes"
                v-model="notes"
                rows="3"
                class="w-full rounded-lg border border-border bg-canvas px-4 py-3 text-sm focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent resize-none"
                placeholder="Any preferences or allergies..."
              />
            </div>
          </div>

          <!-- Step: Confirm -->
          <div v-else class="max-w-md space-y-5">
            <button class="flex items-center gap-1 text-sm text-ink-muted hover:text-ink mb-4" @click="goBack">
              <ArrowLeft class="h-4 w-4" /> Back to details
            </button>

            <div class="rounded-xl border border-peach-200 bg-peach-50/50 p-5 space-y-4">
              <div>
                <p class="text-label text-accent mb-1">Services</p>
                <ul class="space-y-1">
                  <li v-for="item in booking.items" :key="item.serviceId" class="text-sm text-ink flex justify-between">
                    <span>{{ item.serviceName }}</span>
                    <span class="text-ink-muted">{{ formatPrice(item.price) }}</span>
                  </li>
                </ul>
              </div>

              <div class="border-t border-peach-200 pt-3">
                <p class="text-label text-accent mb-1">Appointment</p>
                <p class="text-sm text-ink">{{ preferredDate }} at {{ preferredTime }}</p>
              </div>

              <div class="border-t border-peach-200 pt-3">
                <p class="text-label text-accent mb-1">Contact</p>
                <p class="text-sm text-ink">{{ customerName }}</p>
                <p class="text-sm text-ink-muted">{{ customerPhone }}</p>
              </div>

              <div class="border-t border-peach-200 pt-3 flex justify-between font-medium">
                <span>Total</span>
                <span>{{ formatPrice(booking.total) }}</span>
              </div>
            </div>

            <p class="text-sm text-ink-muted">
              Clicking confirm will open WhatsApp with your booking details. Our team will confirm your appointment shortly.
            </p>
          </div>
        </div>

        <!-- Sidebar: Booking summary -->
        <div class="lg:col-span-1">
          <div class="sticky top-24 rounded-xl border border-border bg-surface p-5 space-y-4">
            <h3 class="font-medium text-ink flex items-center gap-2">
              <Calendar class="h-4 w-4 text-accent" />
              Booking Summary
            </h3>

            <div v-if="booking.isEmpty" class="text-sm text-ink-muted py-4 text-center">
              Select services to get started
            </div>

            <div v-else class="space-y-2">
              <div
                v-for="item in booking.items"
                :key="item.serviceId"
                class="flex justify-between text-sm"
              >
                <span class="text-ink">{{ item.serviceName }}</span>
                <span class="text-ink-muted">{{ formatPrice(item.price) }}</span>
              </div>

              <div class="border-t border-border pt-2 flex items-center gap-3 text-sm text-ink-muted">
                <Clock class="h-4 w-4" />
                <span>{{ formatDuration(booking.totalDuration) }}</span>
              </div>

              <div class="border-t border-border pt-2 flex justify-between font-medium text-ink">
                <span>Total</span>
                <span>{{ formatPrice(booking.total) }}</span>
              </div>
            </div>

            <UiAppButton
              variant="primary"
              size="lg"
              class="w-full"
              :disabled="!canProceed"
              @click="step === 'confirm' ? confirmBooking() : nextStep()"
            >
              {{ step === 'confirm' ? 'Confirm via WhatsApp' : 'Continue' }}
            </UiAppButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
