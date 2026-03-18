<script setup lang="ts">
import { ShoppingBag, Calendar, Menu, X } from 'lucide-vue-next'

const cart = useCartStore()
const booking = useBookingStore()
const mobileMenuOpen = ref(false)
const { settings } = useSettings()

const navLinks = [
  { label: 'Services', to: '/services' },
  { label: 'Shop', to: '/shop' },
  { label: 'Glow Guide', to: '/glow-guide' },
  { label: 'About', to: '/about' },
]
</script>

<template>
  <header class="sticky top-0 z-40 border-b border-border bg-canvas/95 backdrop-blur-sm">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 lg:px-8">
      <!-- Mobile menu button -->
      <button
        class="lg:hidden p-2 -ml-2"
        aria-label="Menu"
        @click="mobileMenuOpen = !mobileMenuOpen"
      >
        <component :is="mobileMenuOpen ? X : Menu" class="h-5 w-5" />
      </button>

      <!-- Logo -->
      <NuxtLink to="/" class="flex items-center gap-2" aria-label="Bhaisepati Sports Academy — home">
        <span class="font-serif text-xl font-medium text-ink">Bhaisepati Sports Academy</span>
      </NuxtLink>

      <!-- Desktop nav -->
      <nav class="hidden lg:flex items-center gap-8" aria-label="Main navigation">
        <NuxtLink
          v-for="link in navLinks"
          :key="link.to"
          :to="link.to"
          class="text-label text-ink-muted transition-colors hover:text-ink"
          active-class="text-ink"
        >
          {{ link.label }}
        </NuxtLink>
      </nav>

      <!-- Action buttons -->
      <div class="flex items-center gap-1">
        <!-- Booking button -->
        <button
          class="relative p-2"
          aria-label="Booking"
          @click="booking.toggleDrawer()"
        >
          <Calendar class="h-5 w-5" />
          <span
            v-if="booking.itemCount > 0"
            class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center bg-accent text-white text-2xs rounded-full"
            aria-live="polite"
          >
            {{ booking.itemCount }}
          </span>
        </button>

        <!-- Cart button -->
        <button
          class="relative p-2 -mr-2"
          aria-label="Cart"
          @click="cart.toggleDrawer()"
        >
          <ShoppingBag class="h-5 w-5" />
          <span
            v-if="cart.itemCount > 0"
            class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center bg-ink text-canvas text-2xs rounded-full"
            aria-live="polite"
          >
            {{ cart.itemCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Mobile nav -->
    <LayoutMobileNav :open="mobileMenuOpen" :links="navLinks" @close="mobileMenuOpen = false" />
  </header>
</template>
