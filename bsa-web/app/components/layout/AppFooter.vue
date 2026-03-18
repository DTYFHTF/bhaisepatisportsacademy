<script setup lang="ts">
const currentYear = new Date().getFullYear()
const { settings, whatsappUrl } = useSettings()
</script>

<template>
  <footer class="border-t border-border bg-surface">
    <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
      <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 md:grid-cols-4">
        <!-- Brand + Contact -->
        <div>
          <NuxtLink to="/" class="inline-block" aria-label="Bhaisepati Sports Academy — home">
            <span class="font-serif text-xl font-medium text-ink">Bhaisepati Sports Academy</span>
          </NuxtLink>
          <p class="mt-3 text-sm text-ink-muted leading-relaxed">
            {{ settings.storeTagline }}
          </p>
          <div class="mt-4 flex flex-col gap-1.5">
            <a
              :href="`mailto:${settings.contactEmail}`"
              class="text-sm text-ink-muted hover:text-ink transition-colors"
            >{{ settings.contactEmail }}</a>
            <a
              :href="`tel:+977${settings.contactPhone}`"
              class="text-sm text-ink-muted hover:text-ink transition-colors"
            >+977 {{ settings.contactPhone }}</a>
            <p class="text-sm text-ink-muted">{{ settings.contactAddress }}</p>
          </div>
        </div>

        <!-- Services Links -->
        <div>
          <h3 class="text-label mb-4">Services</h3>
          <nav class="flex flex-col gap-2" aria-label="Footer service links">
            <NuxtLink to="/services" class="text-sm text-ink-muted hover:text-ink transition-colors">All Services</NuxtLink>
            <NuxtLink to="/services#waxing" class="text-sm text-ink-muted hover:text-ink transition-colors">Waxing</NuxtLink>
            <NuxtLink to="/services#facial" class="text-sm text-ink-muted hover:text-ink transition-colors">Facial</NuxtLink>
            <NuxtLink to="/services#brow" class="text-sm text-ink-muted hover:text-ink transition-colors">Brow</NuxtLink>
            <NuxtLink to="/shop" class="text-sm text-ink-muted hover:text-ink transition-colors">Products</NuxtLink>
          </nav>
        </div>

        <!-- Info -->
        <div>
          <h3 class="text-label mb-4">Info</h3>
          <nav class="flex flex-col gap-2" aria-label="Footer info links">
            <NuxtLink to="/about" class="text-sm text-ink-muted hover:text-ink transition-colors">About Us</NuxtLink>
            <NuxtLink to="/glow-guide" class="text-sm text-ink-muted hover:text-ink transition-colors">Glow Guide</NuxtLink>
            <NuxtLink to="/faq" class="text-sm text-ink-muted hover:text-ink transition-colors">FAQ</NuxtLink>
            <NuxtLink to="/track" class="text-sm text-ink-muted hover:text-ink transition-colors">Track Order</NuxtLink>
          </nav>
        </div>

        <!-- Policies -->
        <div>
          <h3 class="text-label mb-4">Policies</h3>
          <nav class="flex flex-col gap-2" aria-label="Footer policy links">
            <NuxtLink to="/terms" class="text-sm text-ink-muted hover:text-ink transition-colors">Terms &amp; Conditions</NuxtLink>
            <NuxtLink to="/privacy" class="text-sm text-ink-muted hover:text-ink transition-colors">Privacy Policy</NuxtLink>
            <NuxtLink to="/refund-policy" class="text-sm text-ink-muted hover:text-ink transition-colors">Cancellation Policy</NuxtLink>
          </nav>
        </div>
      </div>

      <!-- Bottom bar -->
      <div class="mt-12 border-t border-border pt-6 flex items-center justify-between gap-4">
        <p class="text-sm text-ink-muted">&copy; {{ currentYear }} Bhaisepati Sports Academy. Made in <strong class="text-ink font-semibold">Nepal</strong>.</p>

        <!-- Social icons -->
        <div class="flex items-center gap-5">
          <a
            v-if="settings.instagramUrl"
            :href="settings.instagramUrl"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Instagram"
            class="text-ink-muted hover:text-ink transition-colors"
          >
            <Icon name="simple-icons:instagram" size="18" aria-hidden="true" />
          </a>

          <a
            v-if="whatsappUrl"
            :href="whatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="WhatsApp"
            class="text-ink-muted hover:text-ink transition-colors"
          >
            <Icon name="simple-icons:whatsapp" size="18" aria-hidden="true" />
          </a>
        </div>
      </div>
    </div>
  </footer>
</template>

