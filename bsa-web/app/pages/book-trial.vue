<script setup lang="ts">
import { Calendar, Clock, ArrowLeft, CheckCircle2 } from 'lucide-vue-next'
import { BRAND } from '~/utils/constants'

usePageSeo({
  title: 'Book a Trial Session | Bhaisepati Sports Academy',
  description: 'Sign up for a free trial session at BSA. No commitment needed — just come try it out!',
})

definePageMeta({ ssr: false })

const step = ref<'details' | 'confirm' | 'success'>('details')
const config = useRuntimeConfig()

// Form state
const customerName = ref('')
const customerPhone = ref('')
const customerEmail = ref('')
const age = ref('')
const experienceLevel = ref<'beginner' | 'intermediate' | 'advanced'>('beginner')
const goals = ref('')
const preferredDate = ref('')
const preferredTime = ref('')
const notes = ref('')
const isSubmitting = ref(false)
const successRef = ref('')

const minDate = computed(() => {
  const d = new Date()
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

const canProceed = computed(() => {
  if (step.value === 'details') {
    return customerName.value.trim() && customerPhone.value.length === 10 && preferredDate.value && preferredTime.value && experienceLevel.value
  }
  return true
})

const formattedDate = computed(() => {
  if (!preferredDate.value) return ''
  return new Date(preferredDate.value).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
})

async function submitBooking() {
  isSubmitting.value = true
  try {
    // Save trial booking to database first
    const response = await $fetch(`${config.public.apiBase}/bookings/trial`, {
      method: 'POST',
      body: {
        customer_name: customerName.value,
        customer_phone: customerPhone.value,
        customer_email: customerEmail.value || null,
        scheduled_date: preferredDate.value,
        scheduled_time: preferredTime.value,
        age: age.value ? parseInt(age.value) : null,
        experience_level: experienceLevel.value,
        goals: goals.value || null,
        notes: notes.value || null,
      },
    })

    successRef.value = response.booking.ref

    // Then open WhatsApp with the trial details, same as court booking
    const message = `Hi! I've booked a trial session at BSA.\n\nBooking Ref: ${successRef.value}\nDate: ${formattedDate.value}\nTime: ${preferredTime.value}\nExperience: ${experienceLevel.value}\nName: ${customerName.value}\nPhone: ${customerPhone.value}${age.value ? `\nAge: ${age.value}` : ''}${goals.value ? `\nGoals: ${goals.value}` : ''}${notes.value ? `\nNotes: ${notes.value}` : ''}`
    const encoded = encodeURIComponent(message)
    window.open(`https://wa.me/977${BRAND.phone}?text=${encoded}`, '_blank')

    step.value = 'success'
  } catch (error: any) {
    alert(`Error: ${error.data?.error || 'Failed to book trial session'}`)
  } finally {
    isSubmitting.value = false
  }
}

function goBack() {
  if (step.value === 'confirm') {
    step.value = 'details'
  } else {
    navigateTo('/')
  }
}

function resetForm() {
  customerName.value = ''
  customerPhone.value = ''
  customerEmail.value = ''
  age.value = ''
  experienceLevel.value = 'beginner'
  goals.value = ''
  preferredDate.value = ''
  preferredTime.value = ''
  notes.value = ''
  successRef.value = ''
  step.value = 'details'
}
</script>

<template>
  <div class="min-h-screen bg-canvas">
    <!-- Header -->
    <div class="border-b border-border sticky top-16 z-20 bg-canvas/95 backdrop-blur-md">
      <div class="section-container py-4 flex items-center gap-4">
        <button @click="goBack" class="p-2 hover:bg-surface rounded-lg transition-colors">
          <ArrowLeft class="h-5 w-5 text-ink" />
        </button>
        <div>
          <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent">Free Trial</p>
          <h1 class="font-display text-2xl uppercase tracking-tight text-ink">Book Your Trial Session</h1>
        </div>
      </div>
    </div>

    <div class="section-padding">
      <div class="section-container max-w-2xl mx-auto">
        <!-- Success State -->
        <div v-if="step === 'success'" class="text-center py-12">
          <div class="flex justify-center mb-6">
            <div class="p-4 bg-energy/10 rounded-full">
              <CheckCircle2 class="h-12 w-12 text-energy" />
            </div>
          </div>
          <h2 class="font-display text-3xl uppercase tracking-tight text-ink mb-3">Trial Booked!</h2>
          <p class="text-ink-muted mb-6 leading-relaxed">
            Thank you for booking a trial session with us. We're excited to see you!
          </p>
          <div class="bg-surface p-6 rounded-2xl border border-border mb-8">
            <p class="text-sm text-ink-muted mb-2">Your booking reference</p>
            <p class="font-display text-2xl text-ink mb-4 font-mono">{{ successRef }}</p>
            <p class="text-sm text-ink-muted">
              We'll confirm your session shortly. Check your email or wait for our call.
            </p>
          </div>
          <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <NuxtLink to="/" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-accent text-white hover:bg-accent-hover transition-colors font-medium">
              Back to Home
            </NuxtLink>
            <button @click="resetForm" class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-border bg-canvas text-ink hover:bg-surface transition-colors font-medium">
              Book Another
            </button>
          </div>
        </div>

        <!-- Form State -->
        <div v-else class="space-y-8">
          <!-- Step Indicator -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div :class="['h-10 w-10 rounded-full flex items-center justify-center font-bold text-white', step === 'details' || step === 'confirm' ? 'bg-accent' : 'bg-green-500']">
                ✓
              </div>
              <span class="text-sm font-medium text-ink">Your Details</span>
            </div>
            <div class="h-0.5 flex-1 mx-4 bg-border" />
            <div class="flex items-center gap-3">
              <div :class="['h-10 w-10 rounded-full flex items-center justify-center font-bold text-white', step === 'confirm' || step === 'success' ? 'bg-accent' : 'bg-surface border border-border text-ink']">
                2
              </div>
              <span class="text-sm font-medium" :class="step === 'confirm' || step === 'success' ? 'text-ink' : 'text-ink-muted'">Confirm</span>
            </div>
          </div>

          <!-- Details Form -->
          <form v-if="step === 'details'" @submit.prevent="step = 'confirm'" class="space-y-6">
            <!-- Name & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-ink mb-2">Your Name *</label>
                <input
                  v-model="customerName"
                  type="text"
                  placeholder="e.g., Ramesh"
                  class="w-full px-4 py-3 rounded-lg border border-border bg-canvas text-ink placeholder-ink-muted focus:outline-none focus:ring-2 focus:ring-accent"
                  required
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-ink mb-2">Age</label>
                <input
                  v-model="age"
                  type="number"
                  placeholder="e.g., 25"
                  min="5"
                  max="120"
                  class="w-full px-4 py-3 rounded-lg border border-border bg-canvas text-ink placeholder-ink-muted focus:outline-none focus:ring-2 focus:ring-accent"
                />
              </div>
            </div>

            <!-- Phone & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-ink mb-2">Phone (10 digits) *</label>
                <input
                  v-model="customerPhone"
                  type="tel"
                  placeholder="9841234567"
                  maxlength="10"
                  pattern="\d{10}"
                  class="w-full px-4 py-3 rounded-lg border border-border bg-canvas text-ink placeholder-ink-muted focus:outline-none focus:ring-2 focus:ring-accent"
                  required
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-ink mb-2">Email</label>
                <input
                  v-model="customerEmail"
                  type="email"
                  placeholder="you@example.com"
                  class="w-full px-4 py-3 rounded-lg border border-border bg-canvas text-ink placeholder-ink-muted focus:outline-none focus:ring-2 focus:ring-accent"
                />
              </div>
            </div>

            <!-- Date & Time -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-ink mb-2">
                  <Calendar class="h-4 w-4 inline mr-1" />
                  Preferred Date *
                </label>
                <input
                  v-model="preferredDate"
                  type="date"
                  :min="minDate"
                  class="w-full px-4 py-3 rounded-lg border border-border bg-canvas text-ink focus:outline-none focus:ring-2 focus:ring-accent"
                  required
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-ink mb-2">
                  <Clock class="h-4 w-4 inline mr-1" />
                  Time *
                </label>
                <input
                  v-model="preferredTime"
                  type="time"
                  class="w-full px-4 py-3 rounded-lg border border-border bg-canvas text-ink focus:outline-none focus:ring-2 focus:ring-accent"
                  required
                />
              </div>
            </div>

            <!-- Experience Level -->
            <div>
              <label class="block text-sm font-medium text-ink mb-3">Your Experience Level *</label>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <label v-for="level in ['beginner', 'intermediate', 'advanced']" :key="level" class="flex items-center p-4 rounded-lg border-2 transition-colors cursor-pointer" :class="experienceLevel === level ? 'border-accent bg-accent/5' : 'border-border hover:border-accent/50'">
                  <input v-model="experienceLevel" type="radio" :value="level" class="mr-3" />
                  <span class="font-medium capitalize">{{ level }}</span>
                </label>
              </div>
            </div>

            <!-- Goals -->
            <div>
              <label class="block text-sm font-medium text-ink mb-2">What are your goals? (Optional)</label>
              <textarea
                v-model="goals"
                placeholder="e.g., improve fitness, learn badminton, try a new sport"
                rows="3"
                maxlength="500"
                class="w-full px-4 py-3 rounded-lg border border-border bg-canvas text-ink placeholder-ink-muted focus:outline-none focus:ring-2 focus:ring-accent resize-none"
              />
              <p class="text-xs text-ink-muted mt-1">{{ goals.length }}/500</p>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-sm font-medium text-ink mb-2">Additional Notes (Optional)</label>
              <textarea
                v-model="notes"
                placeholder="Any special requirements or questions?"
                rows="2"
                maxlength="1000"
                class="w-full px-4 py-3 rounded-lg border border-border bg-canvas text-ink placeholder-ink-muted focus:outline-none focus:ring-2 focus:ring-accent resize-none"
              />
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-6">
              <button
                type="button"
                @click="goBack"
                class="px-6 py-3 rounded-full border border-border text-ink hover:bg-surface transition-colors font-medium"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="!canProceed"
                :class="['flex-1 px-6 py-3 rounded-full font-medium transition-colors', canProceed ? 'bg-accent text-white hover:bg-accent-hover' : 'bg-surface text-ink-muted cursor-not-allowed']"
              >
                Continue
              </button>
            </div>
          </form>

          <!-- Confirm State -->
          <div v-if="step === 'confirm'" class="space-y-6">
            <div class="bg-surface p-8 rounded-2xl border border-border space-y-6">
              <div>
                <p class="text-sm text-ink-muted mb-1">Name</p>
                <p class="font-display text-xl text-ink">{{ customerName }}</p>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                  <p class="text-sm text-ink-muted mb-1">Phone</p>
                  <p class="font-medium text-ink">{{ customerPhone }}</p>
                </div>
                <div v-if="customerEmail">
                  <p class="text-sm text-ink-muted mb-1">Email</p>
                  <p class="font-medium text-ink">{{ customerEmail }}</p>
                </div>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div v-if="age">
                  <p class="text-sm text-ink-muted mb-1">Age</p>
                  <p class="font-medium text-ink">{{ age }}</p>
                </div>
                <div>
                  <p class="text-sm text-ink-muted mb-1">Experience Level</p>
                  <p class="font-medium text-ink capitalize">{{ experienceLevel }}</p>
                </div>
              </div>
              <div class="border-t border-border pt-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <div>
                    <p class="text-sm text-ink-muted mb-1">Date</p>
                    <p class="font-medium text-ink">{{ formattedDate }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-ink-muted mb-1">Time</p>
                    <p class="font-medium text-ink">{{ preferredTime }}</p>
                  </div>
                </div>
              </div>
              <div v-if="goals" class="border-t border-border pt-6">
                <p class="text-sm text-ink-muted mb-2">Goals</p>
                <p class="text-ink leading-relaxed">{{ goals }}</p>
              </div>
            </div>

            <!-- Confirmation Message -->
            <div class="bg-energy/10 border border-energy/20 rounded-lg p-4">
              <p class="text-sm text-ink">
                ✓ We'll contact you at <strong>{{ customerPhone }}</strong> to confirm your trial session. Get ready for an awesome experience!
              </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
              <button
                @click="step = 'details'"
                class="px-6 py-3 rounded-full border border-border text-ink hover:bg-surface transition-colors font-medium"
              >
                Edit
              </button>
              <button
                @click="submitBooking"
                :disabled="isSubmitting"
                :class="['flex-1 px-6 py-3 rounded-full font-medium transition-colors', isSubmitting ? 'bg-surface text-ink-muted cursor-not-allowed' : 'bg-accent text-white hover:bg-accent-hover']"
              >
                {{ isSubmitting ? 'Saving...' : 'Confirm via WhatsApp' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
