<script setup lang="ts">
import { Calendar, Shield, Heart, Sparkles, Clock, MapPin, Phone } from 'lucide-vue-next'
import type { Product } from '~/types/product'
import { BRAND, WAX_TYPES } from '~/utils/constants'
import { formatPrice, formatDuration } from '~/utils/formatters'
import type { Service } from '~/types/service'

const config = useRuntimeConfig()

const { data: popularServices } = await useFetch<Service[]>(`${config.public.apiBase}/services`, {
  query: { popular: true },
  default: () => [],
})

const { data: featuredProducts } = await useFetch<Product[]>(`${config.public.apiBase}/products`, {
  query: { limit: 4, featured: true },
  default: () => [],
})

const trustPillars = [
  { icon: Shield, title: 'Premium Waxes', desc: 'Rica, honey, chocolate & sugar — chosen for your skin type' },
  { icon: Heart, title: 'Gentle Process', desc: 'Trained professionals, minimal discomfort, maximum results' },
  { icon: Clock, title: 'Quick & Clean', desc: 'Walk in bare, walk out smooth — most sessions under an hour' },
]
</script>

<template>
  <div>
    <!-- Hero -->
    <section class="relative flex min-h-[70vh] sm:min-h-[80vh] items-center justify-center overflow-hidden bg-peach-50">
      <div class="absolute inset-0 bg-gradient-to-b from-peach-100/50 to-transparent" />

      <div class="relative z-10 text-center px-4 py-20 max-w-2xl mx-auto">
        <p class="text-label text-accent mb-3">Premium Waxing Studio in Kathmandu</p>
        <h1 class="text-display-lg text-ink">Smooth skin starts here.</h1>
        <p class="mt-4 text-lg text-ink-muted max-w-lg mx-auto">
          Professional waxing with premium products and expert hands. Because you deserve to feel confident in your skin.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
          <NuxtLink to="/services">
            <UiAppButton variant="primary" size="lg">
              <Calendar class="h-4 w-4 mr-2" />
              Book Appointment
            </UiAppButton>
          </NuxtLink>
          <NuxtLink to="/glow-guide">
            <UiAppButton variant="ghost" size="lg">
              <Sparkles class="h-4 w-4 mr-2" />
              Take Glow Quiz
            </UiAppButton>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Trust pillars -->
    <section class="border-y border-border bg-canvas">
      <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
          <div v-for="pillar in trustPillars" :key="pillar.title" class="text-center">
            <component :is="pillar.icon" class="h-6 w-6 text-accent mx-auto mb-3" />
            <h3 class="font-medium text-ink">{{ pillar.title }}</h3>
            <p class="mt-1 text-sm text-ink-muted">{{ pillar.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Popular services -->
    <section class="section-padding">
      <div class="section-container">
        <div class="flex items-end justify-between mb-8">
          <div>
            <p class="text-label text-accent mb-1">Most Booked</p>
            <h2 class="text-heading-xl">Popular Services</h2>
          </div>
          <NuxtLink to="/services" class="text-sm text-accent hover:underline underline-offset-4">
            View all services →
          </NuxtLink>
        </div>

        <ServiceGrid :services="popularServices" :columns="3" />
      </div>
    </section>

    <!-- Wax types education -->
    <section class="section-padding bg-surface">
      <div class="section-container">
        <div class="text-center mb-10">
          <p class="text-label text-accent mb-1">Know Your Wax</p>
          <h2 class="text-heading-xl">We Use the Best</h2>
          <p class="mt-2 text-ink-muted max-w-lg mx-auto">Every skin type has its perfect match. Our waxes are imported and chosen for safety, comfort, and results.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="wax in WAX_TYPES"
            :key="wax.name"
            class="rounded-xl border border-border bg-canvas p-5 text-center"
          >
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-peach-100 mb-3">
              <span class="text-xl">{{ wax.name === 'Rica' ? '🌿' : wax.name === 'Honey' ? '🍯' : wax.name === 'Chocolate' ? '🍫' : '🌸' }}</span>
            </div>
            <h3 class="font-medium text-ink">{{ wax.name }}</h3>
            <p class="mt-1 text-sm text-ink-muted">{{ wax.description }}</p>
            <p class="mt-2 text-xs text-accent font-medium">Best for: {{ wax.bestFor }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Products teaser -->
    <section v-if="featuredProducts && featuredProducts.length > 0" class="section-padding">
      <div class="section-container">
        <div class="flex items-end justify-between mb-8">
          <div>
            <p class="text-label text-accent mb-1">Aftercare</p>
            <h2 class="text-heading-xl">Skincare Products</h2>
          </div>
          <NuxtLink to="/shop" class="text-sm text-accent hover:underline underline-offset-4">
            Browse all →
          </NuxtLink>
        </div>

        <ProductGrid :products="featuredProducts" :columns="3" />
      </div>
    </section>

    <!-- Booking CTA -->
    <section class="bg-peach-50 border-t border-peach-200">
      <div class="mx-auto max-w-3xl px-4 py-16 text-center lg:px-8">
        <h2 class="text-display-lg text-ink">Ready for smooth skin?</h2>
        <p class="mt-3 text-ink-muted max-w-md mx-auto">
          Book your appointment today. Walk in confident, walk out glowing.
        </p>
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
          <NuxtLink to="/services">
            <UiAppButton variant="primary" size="lg">
              <Calendar class="h-4 w-4 mr-2" />
              Book Now
            </UiAppButton>
          </NuxtLink>
          <a :href="`tel:+977${BRAND.phone}`">
            <UiAppButton variant="ghost" size="lg">
              <Phone class="h-4 w-4 mr-2" />
              Call Us
            </UiAppButton>
          </a>
        </div>
        <div class="mt-6 flex items-center justify-center gap-4 text-sm text-ink-muted">
          <span class="flex items-center gap-1">
            <MapPin class="h-4 w-4" />
            {{ BRAND.address }}
          </span>
          <span>{{ BRAND.openingHours }}</span>
        </div>
      </div>
    </section>
  </div>
</template>
