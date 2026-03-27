<script setup lang="ts">
import { Calendar, Clock, ArrowLeft, Phone } from 'lucide-vue-next'
import { BRAND, COURT_BOOKING } from '~/utils/constants'
import { formatPrice, formatTime } from '~/utils/formatters'

useSeoMeta({
  title: 'Book a Court | Bhaisepati Sports Academy',
  description: 'Book a badminton court at BSA Bhaisepati. Select your time, fill in your details, and confirm via WhatsApp.',
})

definePageMeta({ ssr: false })

const step = ref<'slot' | 'details' | 'confirm'>('slot')

const selectedCourt = ref<number | null>(null)
const preferredDate = ref('')
const preferredTime = ref('')
const duration = ref(60)
const customerName = ref('')
const customerPhone = ref('')
const notes = ref('')

const minDate = computed(() => {
  const d = new Date()
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

const availableTimes = COURT_BOOKING.timeSlots

const canProceed = computed(() => {
  if (step.value === 'slot') return preferredDate.value && preferredTime.value
  if (step.value === 'details') return customerName.value.trim() && customerPhone.value.trim().length === 10
  return true
})

const totalPrice = computed(() => {
  const hours = duration.value / 60
  return COURT_BOOKING.pricePerHour * hours
})

function nextStep() {
  if (step.value === 'slot') step.value = 'details'
  else if (step.value === 'details') step.value = 'confirm'
}

function goBack() {
  if (step.value === 'details') step.value = 'slot'
  else if (step.value === 'confirm') step.value = 'details'
}

function confirmBooking() {
  const message = `Hi! I'd like to book a badminton court at BSA.\n\nDate: ${preferredDate.value}\nTime: ${preferredTime.value}\nDuration: ${duration.value} min${selectedCourt.value ? `\nCourt: ${selectedCourt.value}` : ''}\nName: ${customerName.value}\nPhone: ${customerPhone.value}${notes.value ? `\nNotes: ${notes.value}` : ''}`
  const encoded = encodeURIComponent(message)
  window.open(`https://wa.me/977${BRAND.phone}?text=${encoded}`, '_blank')
}
</script>

<template>
  <div class="min-h-[80vh]">
    <!-- Header -->
    <section class="border-b border-border">
      <div class="section-container py-8">
        <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-2">Step {{ step === 'slot' ? 1 : step === 'details' ? 2 : 3 }} of 3</p>
        <h1 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-ink">
          {{ step === 'slot' ? 'Select Time Slot' : step === 'details' ? 'Your Details' : 'Confirm Booking' }}
        </h1>
      </div>
    </section>

    <div class="section-container py-8">
      <div class="grid lg:grid-cols-3 gap-8">
        <!-- Main content -->
        <div class="lg:col-span-2">
          <!-- Step: Slot Selection -->
          <div v-if="step === 'slot'" class="space-y-6">
            <!-- Date -->
            <div>
              <label for="date" class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-2 block">Select Date</label>
              <input
                id="date"
                v-model="preferredDate"
                type="date"
                :min="minDate"
                class="w-full max-w-xs rounded-lg border border-border bg-surface px-4 py-3 text-sm text-ink focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
              />
            </div>

            <!-- Duration -->
            <div>
              <label class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-2 block">Duration</label>
              <div class="flex gap-2">
                <button
                  v-for="d in [60, 90, 120]"
                  :key="d"
                  class="rounded-lg border px-4 py-2 text-sm font-medium transition-colors"
                  :class="duration === d ? 'border-accent bg-accent text-canvas' : 'border-border text-ink-muted hover:border-accent/30'"
                  @click="duration = d"
                >
                  {{ d }} min
                </button>
              </div>
            </div>

            <!-- Time -->
            <div>
              <label class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-2 block">Select Time</label>
              <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-2">
                <button
                  v-for="t in availableTimes"
                  :key="t"
                  class="rounded-lg border px-3 py-2.5 text-sm font-medium transition-colors"
                  :class="preferredTime === t ? 'border-accent bg-accent text-canvas' : 'border-border text-ink-muted hover:border-accent/30'"
                  @click="preferredTime = t"
                >
                  {{ formatTime(t) }}
                </button>
              </div>
            </div>

            <!-- Court preference -->
            <div>
              <label class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-2 block">Court Preference (optional)</label>
              <div class="flex gap-2">
                <button
                  class="rounded-lg border px-4 py-2 text-sm font-medium transition-colors"
                  :class="!selectedCourt ? 'border-accent bg-accent text-canvas' : 'border-border text-ink-muted hover:border-accent/30'"
                  @click="selectedCourt = null"
                >
                  Any
                </button>
                <button
                  v-for="c in COURT_BOOKING.totalCourts"
                  :key="c"
                  class="rounded-lg border px-4 py-2 text-sm font-medium transition-colors"
                  :class="selectedCourt === c ? 'border-accent bg-accent text-canvas' : 'border-border text-ink-muted hover:border-accent/30'"
                  @click="selectedCourt = c"
                >
                  Court {{ c }}
                </button>
              </div>
            </div>
          </div>

          <!-- Step: Details -->
          <div v-else-if="step === 'details'" class="max-w-md space-y-5">
            <button class="flex items-center gap-1 text-sm text-ink-muted hover:text-accent mb-4 transition-colors" @click="goBack">
              <ArrowLeft class="h-4 w-4" /> Back
            </button>

            <div>
              <label for="name" class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-2 block">Full Name</label>
              <input
                id="name"
                v-model="customerName"
                type="text"
                class="w-full rounded-lg border border-border bg-surface px-4 py-3 text-sm text-ink focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                placeholder="Your name"
              />
            </div>

            <div>
              <label for="phone" class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-2 block">Phone Number</label>
              <input
                id="phone"
                v-model="customerPhone"
                type="tel"
                maxlength="10"
                class="w-full rounded-lg border border-border bg-surface px-4 py-3 text-sm text-ink focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                placeholder="98XXXXXXXX"
              />
            </div>

            <div>
              <label for="notes" class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-2 block">Notes (optional)</label>
              <textarea
                id="notes"
                v-model="notes"
                rows="3"
                class="w-full rounded-lg border border-border bg-surface px-4 py-3 text-sm text-ink focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent resize-none"
                placeholder="Any preferences..."
              />
            </div>
          </div>

          <!-- Step: Confirm -->
          <div v-else class="max-w-md space-y-5">
            <button class="flex items-center gap-1 text-sm text-ink-muted hover:text-accent mb-4 transition-colors" @click="goBack">
              <ArrowLeft class="h-4 w-4" /> Back
            </button>

            <div class="rounded-2xl border border-accent/20 bg-accent/5 p-6 space-y-4">
              <div>
                <p class="text-xs font-medium uppercase tracking-wider text-accent mb-1">Court Booking</p>
                <p class="text-sm text-ink">{{ preferredDate }} at {{ formatTime(preferredTime) }}</p>
                <p class="text-sm text-ink-muted">{{ duration }} minutes{{ selectedCourt ? ` · Court ${selectedCourt}` : '' }}</p>
              </div>

              <div class="border-t border-accent/10 pt-3">
                <p class="text-xs font-medium uppercase tracking-wider text-accent mb-1">Contact</p>
                <p class="text-sm text-ink">{{ customerName }}</p>
                <p class="text-sm text-ink-muted">{{ customerPhone }}</p>
              </div>

              <div class="border-t border-accent/10 pt-3 flex justify-between font-medium text-ink">
                <span>Total</span>
                <span class="text-accent">{{ formatPrice(totalPrice) }}</span>
              </div>
            </div>

            <p class="text-sm text-ink-muted">
              Clicking confirm opens WhatsApp with your booking details. Our team will confirm your slot.
            </p>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
          <div class="sticky top-24 rounded-2xl border border-border bg-surface p-5 space-y-4">
            <h3 class="font-display text-sm uppercase tracking-wider text-ink flex items-center gap-2">
              <Calendar class="h-4 w-4 text-accent" />
              Booking Summary
            </h3>

            <div v-if="!preferredDate || !preferredTime" class="text-sm text-ink-muted py-4 text-center">
              Select a date and time to get started
            </div>

            <div v-else class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-ink-muted">Date</span>
                <span class="text-ink">{{ preferredDate }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-ink-muted">Time</span>
                <span class="text-ink">{{ formatTime(preferredTime) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-ink-muted">Duration</span>
                <span class="text-ink">{{ duration }} min</span>
              </div>
              <div v-if="selectedCourt" class="flex justify-between text-sm">
                <span class="text-ink-muted">Court</span>
                <span class="text-ink">Court {{ selectedCourt }}</span>
              </div>

              <div class="border-t border-border pt-2 flex justify-between font-medium text-ink">
                <span>Total</span>
                <span class="text-accent">{{ formatPrice(totalPrice) }}</span>
              </div>
            </div>

            <!-- Info -->
            <div class="border-t border-border pt-3 space-y-2 text-xs text-ink-muted">
              <div class="flex items-center gap-2">
                <Clock class="h-3.5 w-3.5 text-accent" />
                {{ BRAND.openingHours }}
              </div>
              <div class="flex items-center gap-2">
                <Phone class="h-3.5 w-3.5 text-accent" />
                +977 {{ BRAND.phone }}
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
