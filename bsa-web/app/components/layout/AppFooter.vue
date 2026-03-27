<script setup lang="ts">
import { MapPin } from 'lucide-vue-next'
import { BRAND } from '~/utils/constants'

const currentYear = new Date().getFullYear()
const { settings, whatsappUrl } = useSettings()
</script>

<template>
  <footer class="border-t border-border bg-surface">
    <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
      <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 md:grid-cols-4">
        <!-- Brand + Contact -->
        <div>
          <NuxtLink to="/" class="inline-block" aria-label="BSA | home">
            <span class="font-display text-2xl uppercase tracking-wider text-accent">BSA</span>
          </NuxtLink>
          <p class="mt-3 text-sm text-ink-muted leading-relaxed">
            {{ settings.storeTagline }}
          </p>
          <div class="mt-4 flex flex-col gap-1.5">
            <a
              :href="`tel:+977${settings.contactPhone}`"
              class="text-sm text-ink-muted hover:text-accent transition-colors"
            >+977 {{ settings.contactPhone }}</a>
            <a
              :href="BRAND.googleMaps"
              target="_blank"
              rel="noopener noreferrer"
              class="text-sm text-ink-muted hover:text-accent transition-colors flex items-center gap-1"
            >
              <MapPin class="h-3 w-3" />
              {{ settings.contactAddress }}
            </a>
          </div>
        </div>

        <!-- Programs -->
        <div>
          <h3 class="font-display text-sm uppercase tracking-wider text-ink mb-4">Programs</h3>
          <nav class="flex flex-col gap-2" aria-label="Footer program links">
            <NuxtLink to="/programs" class="text-sm text-ink-muted hover:text-accent transition-colors">All Programs</NuxtLink>
            <NuxtLink to="/programs#badminton" class="text-sm text-ink-muted hover:text-accent transition-colors">Badminton Training</NuxtLink>
            <NuxtLink to="/programs#gym" class="text-sm text-ink-muted hover:text-accent transition-colors">Gym & Strength</NuxtLink>
            <NuxtLink to="/book" class="text-sm text-ink-muted hover:text-accent transition-colors">Book a Court</NuxtLink>
          </nav>
        </div>

        <!-- Facilities -->
        <div>
          <h3 class="font-display text-sm uppercase tracking-wider text-ink mb-4">Facilities</h3>
          <nav class="flex flex-col gap-2" aria-label="Footer facility links">
            <NuxtLink to="/facilities" class="text-sm text-ink-muted hover:text-accent transition-colors">All Facilities</NuxtLink>
            <NuxtLink to="/facilities#courts" class="text-sm text-ink-muted hover:text-accent transition-colors">Badminton Courts</NuxtLink>
            <NuxtLink to="/facilities#gym" class="text-sm text-ink-muted hover:text-accent transition-colors">Gym Area</NuxtLink>
            <NuxtLink to="/facilities#sauna" class="text-sm text-ink-muted hover:text-accent transition-colors">Sauna & Steam</NuxtLink>
          </nav>
        </div>

        <!-- Info -->
        <div>
          <h3 class="font-display text-sm uppercase tracking-wider text-ink mb-4">Info</h3>
          <nav class="flex flex-col gap-2" aria-label="Footer info links">
            <NuxtLink to="/about" class="text-sm text-ink-muted hover:text-accent transition-colors">About BSA</NuxtLink>
            <NuxtLink to="/faq" class="text-sm text-ink-muted hover:text-accent transition-colors">FAQ</NuxtLink>
            <NuxtLink to="/terms" class="text-sm text-ink-muted hover:text-accent transition-colors">Terms & Conditions</NuxtLink>
          </nav>
        </div>
      </div>

      <!-- Bottom bar -->
      <div class="mt-12 border-t border-border pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-sm text-ink-muted">&copy; {{ currentYear }} Bhaisepati Sports Academy. Made in <strong class="text-accent font-semibold">Nepal</strong> 🇳🇵</p>

        <!-- Social icons -->
        <div class="flex items-center gap-5">
          <a
            :href="BRAND.instagram"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Instagram"
            class="text-ink-muted hover:text-accent transition-colors"
          >
            <Icon name="simple-icons:instagram" size="18" aria-hidden="true" />
          </a>

          <a
            v-if="whatsappUrl"
            :href="whatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="WhatsApp"
            class="text-ink-muted hover:text-accent transition-colors"
          >
            <Icon name="simple-icons:whatsapp" size="18" aria-hidden="true" />
          </a>
        </div>
      </div>
    </div>
  </footer>
</template>

