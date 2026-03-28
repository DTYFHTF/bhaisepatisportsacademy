<script setup lang="ts">
import { ChevronRight, Clock, Users, Zap } from 'lucide-vue-next'
import { PROGRAM_CATEGORIES, PROGRAM_CATEGORY_LABELS } from '~/utils/constants'
import type { ProgramCategory } from '~/types/service'
import { formatPrice } from '~/utils/formatters'

useSeoMeta({
  title: 'Programs | Bhaisepati Sports Academy',
  description: 'Professional badminton training, gym memberships, and fitness programs at BSA. From beginners to competitive players.',
})

const config = useRuntimeConfig()

const { data: programs } = await useFetch<{
  id: string; name: string; description: string; category: string; level: string; duration: string;
  sessions_per_week: number; price: number; is_popular: boolean; features: string[]
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
    <section class="relative overflow-hidden border-b border-border">
      <div class="absolute inset-0 bg-gradient-to-br from-canvas via-surface to-canvas" />
      <div class="absolute top-0 right-0 h-40 w-40 bg-accent/5 rounded-bl-[100px] blur-2xl" />
      <div class="section-container relative z-10 py-14 sm:py-20">
        <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-3">Train With Purpose</p>
        <h1 class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-ink">Our Programs</h1>
        <p class="mt-4 text-ink-muted max-w-lg leading-relaxed">
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
            v-for="program in filteredPrograms"
            :key="program.id"
            class="group rounded-2xl border border-border bg-surface overflow-hidden hover:border-accent/30 transition-all duration-300"
          >
            <!-- Accent top stripe -->
            <div class="h-1 bg-gradient-to-r from-accent via-accent to-accent/50" />

            <div class="p-6">
              <!-- Category + Popular badge -->
              <div class="flex items-center gap-2 mb-3">
                <span class="inline-block rounded-full bg-accent/10 px-3 py-1 text-xs font-medium uppercase tracking-wider text-accent">
                  {{ program.category }}
                </span>
                <span v-if="program.is_popular" class="inline-block rounded-full bg-energy/10 px-3 py-1 text-xs font-medium text-energy">
                  Popular
                </span>
              </div>

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
                  {{ program.sessions_per_week }}x per week
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
                  <NuxtLink
                    to="/book"
                    class="inline-flex items-center gap-1 rounded-lg bg-accent px-4 py-2 text-sm font-bold uppercase tracking-wider text-canvas hover:bg-accent-hover transition-colors"
                  >
                    Enroll
                    <ChevronRight class="h-4 w-4" />
                  </NuxtLink>
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
    <section class="border-t border-border bg-surface">
      <div class="mx-auto max-w-3xl px-4 py-16 text-center">
        <h2 class="font-display text-3xl uppercase tracking-tight text-ink">Not sure which program?</h2>
        <p class="mt-3 text-ink-muted">
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
