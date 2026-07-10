<script setup lang="ts">
import { ChevronRight, Clock, Users, Zap } from 'lucide-vue-next'
import { PROGRAM_CATEGORIES, PROGRAM_CATEGORY_LABELS, BRAND, IMAGES, PROGRAM_IMAGES } from '~/utils/constants'
import type { ProgramCategory } from '~/types/service'
import { formatPrice } from '~/utils/formatters'

useSeoMeta({
  title: 'Programs | Bhaisepati Sports Academy',
  description: 'Professional badminton training, gym memberships, and fitness programs at BSA. From beginners to competitive players.',
})

const config = useRuntimeConfig()

const { data: programs } = await useFetch<{
  id: string; name: string; description: string; category: string; level: string; duration: string;
  sessionsPerWeek: number; price: number; isPopular: boolean; features: string[]
}[]>(`${config.public.apiBase}/programs`, { server: false })

const activeCategory = ref<ProgramCategory | null>(null)

const filteredPrograms = computed(() => {
  if (!activeCategory.value) return programs.value ?? []
  return (programs.value ?? []).filter((p) => p.category === activeCategory.value)
})
</script>

<template>
  <div>
    <!-- Header -->
    <section class="relative overflow-hidden border-b border-border min-h-[300px] flex items-end">
      <div class="absolute inset-0">
        <img :src="IMAGES.gymTraining" alt="BSA Programs" class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20" />
      </div>
      <div class="section-container relative z-10 pb-10 pt-20">
        <p v-scroll="'fade-up'" class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-3">Train With Purpose</p>
        <h1 v-scroll:100="'fade-up'" class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-white">Our Programs</h1>
        <p v-scroll:200="'fade-up'" class="mt-4 text-white/70 max-w-lg leading-relaxed">
          From foundation-level badminton to competitive training and full gym memberships. Find the program that matches your goals.
        </p>
      </div>
    </section>

    <!-- Filters -->
    <section class="border-b border-border sticky top-16 z-30 bg-canvas/95 backdrop-blur-md">
      <div class="section-container py-3">
        <div class="flex gap-2 overflow-x-auto no-scrollbar">
          <button
            class="px-4 py-2 rounded-full text-sm font-medium uppercase tracking-wider whitespace-nowrap transition-colors"
            :class="!activeCategory ? 'bg-accent text-canvas' : 'bg-surface text-ink-muted hover:text-ink'"
            @click="activeCategory = null"
          >
            All
          </button>
          <button
            v-for="cat in PROGRAM_CATEGORIES"
            :key="cat"
            class="px-4 py-2 rounded-full text-sm font-medium uppercase tracking-wider whitespace-nowrap transition-colors"
            :class="activeCategory === cat ? 'bg-accent text-canvas' : 'bg-surface text-ink-muted hover:text-ink'"
            @click="activeCategory = cat"
          >
            {{ PROGRAM_CATEGORY_LABELS[cat] }}
          </button>
        </div>
      </div>
    </section>

    <!-- Programs grid -->
    <section class="section-padding">
      <div class="section-container">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="(program, pIdx) in filteredPrograms"
            :key="program.id"
            v-scroll:[pIdx%3*150]="'fade-up'"
            class="group rounded-2xl border border-border bg-surface overflow-hidden hover:border-accent/30 hover:shadow-xl hover:shadow-accent/5 transition-all duration-500"
          >
            <!-- Program Image -->
            <div class="relative h-44 overflow-hidden">
              <img
                :src="PROGRAM_IMAGES[program.category] || IMAGES.badmintonCourt"
                :alt="program.name"
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
              <!-- Badges on image -->
              <div class="absolute top-3 left-3 flex gap-2">
                <span class="rounded-full bg-accent px-3 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-md">
                  {{ program.category }}
                </span>
                <span v-if="program.isPopular" class="rounded-full bg-energy px-3 py-1 text-xs font-bold text-white shadow-md">
                  Popular
                </span>
              </div>
              <!-- Level badge -->
              <span class="absolute bottom-3 right-3 rounded-full bg-white/20 backdrop-blur-sm px-3 py-1 text-xs font-medium text-white">
                {{ program.level }}
              </span>
            </div>

            <div class="p-6">
              <h3 class="font-display text-xl uppercase tracking-wider text-ink mb-2">{{ program.name }}</h3>
              <p class="text-sm text-ink-muted mb-4 leading-relaxed">{{ program.description }}</p>

              <!-- Meta -->
              <div class="flex items-center gap-4 text-xs text-ink-muted mb-4">
                <span class="flex items-center gap-1">
                  <Clock class="h-3.5 w-3.5 text-accent" />
                  {{ program.duration }}
                </span>
                <span class="flex items-center gap-1">
                  <Zap class="h-3.5 w-3.5 text-accent" />
                  {{ program.sessionsPerWeek }}x per week
                </span>
              </div>

              <!-- Features -->
              <ul class="space-y-2 mb-6">
                <li v-for="feature in program.features" :key="feature" class="flex items-start gap-2 text-sm text-ink-muted">
                  <span class="h-1 w-1 rounded-full bg-accent flex-shrink-0 mt-2" />
                  {{ feature }}
                </li>
              </ul>

              <!-- Price + CTA -->
              <div class="pt-4 border-t border-border">
                <div class="flex items-end justify-between">
                  <div>
                  <p class="text-2xl font-bold text-ink">{{ formatPrice(program.price) }}</p>
                    <p class="text-xs text-ink-muted">per month</p>
                  </div>
                  <a
                    :href="`https://wa.me/977${BRAND.whatsapp}?text=${encodeURIComponent('Hi BSA! I\'d like to enroll in the ' + program.name + ' program. Please guide me on the next steps.')}`"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1 rounded-lg bg-accent px-4 py-2 text-sm font-bold uppercase tracking-wider text-white hover:bg-accent-hover transition-colors"
                  >
                    Enroll
                    <ChevronRight class="h-4 w-4" />
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="filteredPrograms.length === 0" class="text-center py-16">
          <p class="text-ink-muted">No programs in this category yet.</p>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="relative border-t border-border overflow-hidden">
      <div class="absolute inset-0">
        <img :src="IMAGES.badmintonCourt" alt="" class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-black/80" />
      </div>
      <div v-scroll="'fade-up'" class="relative z-10 mx-auto max-w-3xl px-4 py-16 text-center">
        <h2 class="font-display text-3xl uppercase tracking-tight text-white">Not sure which program?</h2>
        <p class="mt-3 text-white/70">
          Drop by for a trial session or call us to discuss your training goals.
        </p>
        <div class="mt-6">
          <NuxtLink to="/book">
            <UiAppButton variant="primary" size="lg">
              Book a Trial Session
              <ChevronRight class="h-4 w-4 ml-1" />
            </UiAppButton>
          </NuxtLink>
        </div>
      </div>
    </section>
  </div>
</template>
