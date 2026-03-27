<script setup lang="ts">
import { Calendar, Menu, ShoppingBag, X } from 'lucide-vue-next'

const booking = useBookingStore()
const cart = useCartStore()
const mobileMenuOpen = ref(false)

const navLinks = [
  { label: 'Programs', to: '/programs' },
  { label: 'Facilities', to: '/facilities' },
  { label: 'Shop', to: '/shop' },
  { label: 'About', to: '/about' },
  { label: 'FAQ', to: '/faq' },
]
</script>

<template>
  <header class="sticky top-0 z-40 border-b border-border bg-canvas/95 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 lg:px-8">
      <!-- Mobile menu button -->
      <button
        class="lg:hidden p-2 -ml-2 text-ink-muted hover:text-accent transition-colors"
        aria-label="Menu"
        @click="mobileMenuOpen = !mobileMenuOpen"
      >
        <component :is="mobileMenuOpen ? X : Menu" class="h-5 w-5" />
      </button>

      <!-- Logo -->
      <NuxtLink to="/" class="flex items-center gap-2" aria-label="Bhaisepati Sports Academy | home">
        <span class="font-display text-xl uppercase tracking-wider text-accent">BSA</span>
        <span class="hidden sm:inline text-sm font-medium text-ink">Sports Academy</span>
      </NuxtLink>

      <!-- Desktop nav -->
      <nav class="hidden lg:flex items-center gap-8" aria-label="Main navigation">
        <NuxtLink
          v-for="link in navLinks"
          :key="link.to"
          :to="link.to"
          class="text-sm font-medium uppercase tracking-wider text-ink-muted transition-colors hover:text-accent"
          active-class="!text-accent"
        >
          {{ link.label }}
        </NuxtLink>
      </nav>

      <!-- Action buttons -->
      <div class="flex items-center gap-3">
        <!-- Cart badge -->
        <button
          v-if="cart.itemCount > 0"
          class="relative p-2 text-ink-muted hover:text-accent transition-colors"
          aria-label="View cart"
          @click="cart.toggleDrawer()"
        >
          <ShoppingBag class="h-5 w-5" />
          <span
            class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center bg-accent text-canvas text-2xs rounded-full font-bold"
            aria-live="polite"
          >
            {{ cart.itemCount }}
          </span>
        </button>

        <!-- Booking badge -->
        <button
          v-if="booking.itemCount > 0"
          class="relative p-2 text-ink-muted hover:text-accent transition-colors"
          aria-label="View booking"
          @click="booking.toggleDrawer()"
        >
          <Calendar class="h-5 w-5" />
          <span
            class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center bg-accent text-canvas text-2xs rounded-full font-bold"
            aria-live="polite"
          >
            {{ booking.itemCount }}
          </span>
        </button>

        <!-- CTA -->
        <NuxtLink to="/book" class="hidden sm:block">
          <UiAppButton variant="primary" size="sm">
            Book Court
          </UiAppButton>
        </NuxtLink>
      </div>
    </div>

    <!-- Mobile nav -->
    <LayoutMobileNav :open="mobileMenuOpen" :links="navLinks" @close="mobileMenuOpen = false" />
  </header>
</template>
