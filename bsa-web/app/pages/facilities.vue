<script setup lang="ts">
import { ChevronRight, Dumbbell, Flame, Phone } from 'lucide-vue-next'
import { BRAND, IMAGES } from '~/utils/constants'

useSeoMeta({
  title: 'Facilities | Bhaisepati Sports Academy',
  description: 'Professional badminton courts, fully equipped gym, and sauna & steam recovery at BSA Bhaisepati.',
})

const config = useRuntimeConfig()
const { data: facilities } = await useFetch<{
  id: string; name: string; category: string; description: string; features: string[]; image_url: string | null
}[]>(`${config.public.apiBase}/facilities`, { server: false })

// Fallback images per category
const categoryImage = (cat: string, imgUrl: string | null) => {
  if (imgUrl) return imgUrl
  const map: Record<string, string> = { BADMINTON: IMAGES.badmintonCourt, GYM: IMAGES.gym, SAUNA: IMAGES.sauna }
  return map[cat] || IMAGES.badmintonCourt
}
</script>

<template>
  <div>
    <!-- Header -->
    <section class="relative overflow-hidden border-b border-border min-h-[300px] flex items-end">
      <div class="absolute inset-0">
        <img :src="IMAGES.badmintonCourt" alt="BSA Facilities" class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20" />
      </div>
      <div class="section-container relative z-10 pb-10 pt-20">
        <p v-scroll="'fade-up'" class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-3">World-Class Amenities</p>
        <h1 v-scroll:100="'fade-up'" class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-white">Our Facilities</h1>
        <p v-scroll:200="'fade-up'" class="mt-4 text-white/70 max-w-lg leading-relaxed">
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
              <div v-scroll="index % 2 === 0 ? 'fade-left' : 'fade-right'" :class="index % 2 === 1 ? 'lg:order-2' : ''">
                <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent/10 mb-4">
                  <Dumbbell v-if="facility.category === 'GYM'" class="h-5 w-5 text-accent" />
                  <Flame v-else-if="facility.category === 'SAUNA'" class="h-5 w-5 text-accent" />
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

              <!-- Facility Image -->
              <div
                v-scroll="index % 2 === 0 ? 'fade-right' : 'fade-left'"
                :class="index % 2 === 1 ? 'lg:order-1' : ''"
                class="relative aspect-[4/3] rounded-2xl overflow-hidden border border-border group"
              >
                <img
                  :src="categoryImage(facility.category, facility.image_url)"
                  :alt="facility.name"
                  class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent" />
                <!-- Category icon badge -->
                <div class="absolute bottom-4 right-4 inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent shadow-lg">
                  <Dumbbell v-if="facility.category === 'GYM'" class="h-5 w-5 text-white" />
                  <Flame v-else-if="facility.category === 'SAUNA'" class="h-5 w-5 text-white" />
                  <svg v-else class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="5" r="3" />
                    <path d="M12 8L6 20M12 8L18 20" />
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="relative border-t border-border overflow-hidden">
      <div class="absolute inset-0">
        <img :src="IMAGES.gym" alt="" class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-black/80" />
      </div>
      <div v-scroll="'fade-up'" class="relative z-10 mx-auto max-w-3xl px-4 py-16 text-center">
        <h2 class="font-display text-3xl uppercase tracking-tight text-white">Want a Tour?</h2>
        <p class="mt-3 text-white/70">
          Visit us at Bhaisepati or call for a guided tour of all our facilities.
        </p>
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-4">
          <NuxtLink to="/book">
            <UiAppButton variant="primary" size="lg">
              Book a Visit
            </UiAppButton>
          </NuxtLink>
          <a :href="`tel:+977${BRAND.phone}`">
            <button class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 backdrop-blur-sm px-6 py-3 text-sm font-bold uppercase tracking-wider text-white hover:bg-white/20 transition-all">
              <Phone class="h-4 w-4" />
              Call Us
            </button>
          </a>
        </div>
      </div>
    </section>
  </div>
</template>
