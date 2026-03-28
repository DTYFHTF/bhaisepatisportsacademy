<script setup lang="ts">
import { ChevronRight, Dumbbell, Flame, Phone } from 'lucide-vue-next'
import { BRAND } from '~/utils/constants'

useSeoMeta({
  title: 'Facilities | Bhaisepati Sports Academy',
  description: 'Professional badminton courts, fully equipped gym, and sauna & steam recovery at BSA Bhaisepati.',
})

const config = useRuntimeConfig()
const { data: facilities } = await useFetch<{
  id: string; name: string; category: string; description: string; features: string[]
}[]>(`${config.public.apiBase}/facilities`)
</script>

<template>
  <div>
    <!-- Header -->
    <section class="relative overflow-hidden border-b border-border">
      <div class="absolute inset-0 bg-gradient-to-br from-canvas via-surface to-canvas" />
      <div class="absolute bottom-0 left-0 h-40 w-40 bg-court/5 rounded-tr-[100px] blur-2xl" />
      <div class="section-container relative z-10 py-14 sm:py-20">
        <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-3">World-Class Amenities</p>
        <h1 class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-ink">Our Facilities</h1>
        <p class="mt-4 text-ink-muted max-w-lg leading-relaxed">
          Everything you need to train, compete, and recover, all under one roof in Bhaisepati, Lalitpur.
        </p>
      </div>
    </section>

    <!-- Facilities -->
    <section class="section-padding">
      <div class="section-container">
        <div class="space-y-20">
          <div
            v-for="(facility, index) in (facilities ?? [])"
            :id="facility.category === 'badminton' ? 'courts' : facility.category"
            :key="facility.id"
            class="scroll-mt-24"
          >
            <div
              class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center"
              :class="index % 2 === 1 ? 'lg:flex-row-reverse' : ''"
            >
              <!-- Content -->
              <div :class="index % 2 === 1 ? 'lg:order-2' : ''">
                <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent/10 mb-4">
                  <Dumbbell v-if="facility.category === 'gym'" class="h-5 w-5 text-accent" />
                  <Flame v-else-if="facility.category === 'sauna'" class="h-5 w-5 text-accent" />
                  <svg v-else class="h-5 w-5 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="5" r="3" />
                    <path d="M12 8L6 20M12 8L18 20" />
                  </svg>
                </div>

                <h2 class="font-display text-3xl uppercase tracking-tight text-ink mb-3">{{ facility.name }}</h2>
                <p class="text-ink-muted leading-relaxed mb-6">{{ facility.description }}</p>

                <ul class="space-y-3">
                  <li v-for="feature in facility.features" :key="feature" class="flex items-start gap-3 text-sm text-ink-muted">
                    <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-accent flex-shrink-0" />
                    {{ feature }}
                  </li>
                </ul>

                <div class="mt-6">
                  <NuxtLink v-if="facility.category === 'BADMINTON'" to="/book">
                    <UiAppButton variant="primary" size="md">
                      Book a Court
                      <ChevronRight class="h-4 w-4 ml-1" />
                    </UiAppButton>
                  </NuxtLink>
                  <NuxtLink v-else to="/programs">
                    <UiAppButton variant="secondary" size="md">
                      View Programs
                      <ChevronRight class="h-4 w-4 ml-1" />
                    </UiAppButton>
                  </NuxtLink>
                </div>
              </div>

              <!-- Visual placeholder -->
              <div
                :class="index % 2 === 1 ? 'lg:order-1' : ''"
                class="relative aspect-[4/3] rounded-2xl overflow-hidden border border-border bg-surface"
              >
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="text-center">
                    <div class="inline-flex items-center justify-center h-20 w-20 rounded-2xl bg-accent/10 mb-3">
                      <Dumbbell v-if="facility.category === 'GYM'" class="h-8 w-8 text-accent" />
                      <Flame v-else-if="facility.category === 'SAUNA'" class="h-8 w-8 text-accent" />
                      <svg v-else class="h-8 w-8 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="5" r="3" />
                        <path d="M12 8L6 20M12 8L18 20" />
                      </svg>
                    </div>
                    <p class="text-sm text-ink-muted">{{ facility.name }}</p>
                  </div>
                </div>
                <!-- Decorative gradient -->
                <div class="absolute inset-0 bg-gradient-to-br from-accent/5 to-transparent" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="border-t border-border bg-surface">
      <div class="mx-auto max-w-3xl px-4 py-16 text-center">
        <h2 class="font-display text-3xl uppercase tracking-tight text-ink">Want a Tour?</h2>
        <p class="mt-3 text-ink-muted">
          Visit us at Bhaisepati or call for a guided tour of all our facilities.
        </p>
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-4">
          <NuxtLink to="/book">
            <UiAppButton variant="primary" size="lg">
              Book a Visit
            </UiAppButton>
          </NuxtLink>
          <a :href="`tel:+977${BRAND.phone}`">
            <UiAppButton variant="ghost" size="lg">
              <Phone class="h-4 w-4 mr-2" />
              Call Us
            </UiAppButton>
          </a>
        </div>
      </div>
    </section>
  </div>
</template>
