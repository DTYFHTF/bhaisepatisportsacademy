<script setup lang="ts">
import { MapPin, Phone, Clock, Instagram } from 'lucide-vue-next'
import { BRAND } from '~/utils/constants'

useSeoMeta({
  title: 'About — Bhaisepati Sports Academy',
  description: 'Premium waxing studio in Chakrapath, Kathmandu. Learn about our story, team, and commitment to quality skincare.',
})
</script>

<template>
  <div>
    <!-- Header -->
    <section class="bg-peach-50 border-b border-peach-200">
      <div class="section-container py-12 sm:py-16 text-center">
        <p class="text-label text-accent mb-2">Our Story</p>
        <h1 class="text-display-lg text-ink">About Bhaisepati Sports Academy</h1>
      </div>
    </section>

    <!-- Mission -->
    <section class="section-padding">
      <div class="section-container max-w-3xl">
        <div class="prose prose-neutral mx-auto">
          <p class="text-lg text-ink-muted leading-relaxed">
            Bhaisepati Sports Academy was founded with a simple belief: everyone deserves access to professional, hygienic, and comfortable waxing services — right here in Kathmandu.
          </p>
          <p class="text-ink-muted leading-relaxed">
            We use only premium imported waxes — Rica, honey, chocolate, and sugar — because your skin deserves the best. Our trained professionals prioritise your comfort, ensuring every visit leaves you feeling smooth and confident.
          </p>
          <p class="text-ink-muted leading-relaxed">
            Whether it's your first wax or your hundredth, we treat every appointment with the same level of care and attention. No rushing, no shortcuts — just expert service in a clean and welcoming space.
          </p>
        </div>
      </div>
    </section>

    <!-- Values -->
    <section class="section-padding bg-surface">
      <div class="section-container">
        <h2 class="text-heading-xl text-center mb-10">What We Stand For</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div class="rounded-xl border border-border bg-canvas p-6 text-center">
            <span class="text-3xl mb-3 block">🧴</span>
            <h3 class="font-medium text-ink">Quality Products</h3>
            <p class="mt-2 text-sm text-ink-muted">Only imported, dermatologist-approved waxes touch your skin.</p>
          </div>
          <div class="rounded-xl border border-border bg-canvas p-6 text-center">
            <span class="text-3xl mb-3 block">💆‍♀️</span>
            <h3 class="font-medium text-ink">Trained Professionals</h3>
            <p class="mt-2 text-sm text-ink-muted">Every therapist is certified and trained in gentle waxing techniques.</p>
          </div>
          <div class="rounded-xl border border-border bg-canvas p-6 text-center">
            <span class="text-3xl mb-3 block">✨</span>
            <h3 class="font-medium text-ink">Clean & Safe</h3>
            <p class="mt-2 text-sm text-ink-muted">Fresh supplies for every client. Strict hygiene standards, always.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact info -->
    <section class="section-padding">
      <div class="section-container max-w-2xl">
        <h2 class="text-heading-xl text-center mb-8">Visit Us</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div class="flex items-start gap-3">
            <MapPin class="h-5 w-5 text-accent mt-0.5 flex-shrink-0" />
            <div>
              <p class="font-medium text-ink">Location</p>
              <p class="text-sm text-ink-muted">{{ BRAND.address }}</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <Phone class="h-5 w-5 text-accent mt-0.5 flex-shrink-0" />
            <div>
              <p class="font-medium text-ink">Phone</p>
              <a :href="`tel:+977${BRAND.phone}`" class="text-sm text-ink-muted hover:text-ink">+977 {{ BRAND.phone }}</a>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <Clock class="h-5 w-5 text-accent mt-0.5 flex-shrink-0" />
            <div>
              <p class="font-medium text-ink">Hours</p>
              <p class="text-sm text-ink-muted">{{ BRAND.openingHours }}</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <Instagram class="h-5 w-5 text-accent mt-0.5 flex-shrink-0" />
            <div>
              <p class="font-medium text-ink">Instagram</p>
              <a
                :href="`https://instagram.com/${BRAND.instagram}`"
                target="_blank"
                rel="noopener noreferrer"
                class="text-sm text-ink-muted hover:text-ink"
              >@{{ BRAND.instagram }}</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
