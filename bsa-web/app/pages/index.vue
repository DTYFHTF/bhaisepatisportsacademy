<script setup lang="ts">
import { Trophy, Zap, Users, Clock, MapPin, Phone, ChevronRight, Star, Dumbbell, Flame } from 'lucide-vue-next'
import { BRAND } from '~/utils/constants'
import { formatPrice } from '~/utils/formatters'

const config = useRuntimeConfig()

const { data: rawStats } = await useFetch<{ value_label: string; label: string }[]>(
  `${config.public.apiBase}/stats`, { server: false },
)

const { data: facilities } = await useFetch<{ id: string; name: string; category: string; description: string; features: string[]; icon: string }[]>(
  `${config.public.apiBase}/facilities`, { server: false },
)

const { data: allPrograms } = await useFetch<{ id: string; name: string; category: string; description: string; features: string[]; price: number; is_popular: boolean; sessions_per_week: number; duration: string; level: string }[]>(
  `${config.public.apiBase}/programs`, { server: false },
)

const { data: testimonials } = await useFetch<{ id: number; name: string; role: string; quote: string }[]>(
  `${config.public.apiBase}/testimonials`, { server: false },
)

// Today's schedule
const today = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][new Date().getDay()]
const { data: rawSchedule } = await useFetch<{ id: number; day: string; time: string; program_name: string; coach: string; level: string }[]>(
  `${config.public.apiBase}/schedule`,
  { server: false, query: { day: today } },
)

// Animated counter — must be called at setup time, not inside computed
// Build one counter per stat slot (max 6 expected)
const MAX_STATS = 6
const counterCounts = Array.from({ length: MAX_STATS }, () => ref(0))
const counterEls = Array.from({ length: MAX_STATS }, () => ref<HTMLElement | null>(null))

onMounted(() => {
  const statList = rawStats.value ?? []
  statList.forEach((s, i) => {
    if (i >= MAX_STATS) return
    const target = parseInt((s.value_label ?? '').replace(/\D/g, '')) || 0
    const el = counterEls[i]
    const count = counterCounts[i]
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          const start = performance.now()
          const animate = (now: number) => {
            const progress = Math.min((now - start) / 2000, 1)
            const eased = 1 - Math.pow(1 - progress, 3)
            count.value = Math.floor(eased * target)
            if (progress < 1) requestAnimationFrame(animate)
          }
          requestAnimationFrame(animate)
          observer.disconnect()
        }
      },
      { threshold: 0.3 },
    )
    if (el.value) observer.observe(el.value)
  })
})

const stats = computed(() =>
  (rawStats.value ?? []).map((s, i) => ({
    label: s.label,
    value_label: s.value_label,
    count: counterCounts[i] ?? ref(0),
    el: counterEls[i] ?? ref(null),
    suffix: (s.value_label ?? '').replace(/^\d+/, ''),
  })),
)

const popularPrograms = computed(() =>
  (allPrograms.value ?? []).filter((p) => p.is_popular).slice(0, 3),
)

const todaySchedule = computed(() => (rawSchedule.value ?? []).slice(0, 5))

const pillars = [
  { icon: Trophy, title: 'Competition Ready', desc: 'Professional courts with tournament-grade equipment and lighting' },
  { icon: Zap, title: 'Elite Coaching', desc: 'Trained coaches for every skill level, from beginner to competitive' },
  { icon: Users, title: 'Community Driven', desc: 'Join 500+ active members. Train together, grow together' },
]
</script>

<template>
  <div>
    <!-- ═══ HERO ═══ -->
    <section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden">
      <!-- Animated background -->
      <div class="absolute inset-0 bg-gradient-to-br from-canvas via-surface to-canvas">
        <!-- Diagonal accent lines -->
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: repeating-linear-gradient(45deg, #FFB800 0, #FFB800 1px, transparent 0, transparent 50%); background-size: 60px 60px;" />
        <!-- Glow orb -->
        <div class="absolute top-1/4 -right-32 h-96 w-96 rounded-full bg-accent/5 blur-3xl" />
        <div class="absolute bottom-1/4 -left-32 h-72 w-72 rounded-full bg-court/5 blur-3xl" />
      </div>

      <!-- Shuttlecock SVG decoration -->
      <div class="absolute top-20 right-10 sm:right-20 opacity-10 animate-float">
        <svg width="80" height="80" viewBox="0 0 100 100" fill="none" class="text-accent">
          <circle cx="50" cy="75" r="12" fill="currentColor" />
          <path d="M50 63 L35 15 Q50 25 50 25" stroke="currentColor" stroke-width="2" fill="currentColor" opacity="0.6" />
          <path d="M50 63 L50 10 Q50 20 50 20" stroke="currentColor" stroke-width="2" fill="currentColor" opacity="0.6" />
          <path d="M50 63 L65 15 Q50 25 50 25" stroke="currentColor" stroke-width="2" fill="currentColor" opacity="0.6" />
        </svg>
      </div>

      <div class="relative z-10 text-center px-4 py-20 max-w-4xl mx-auto">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 rounded-full border border-accent/30 bg-accent/10 px-4 py-1.5 mb-6">
          <span class="h-1.5 w-1.5 rounded-full bg-accent animate-pulse" />
          <span class="text-xs font-medium uppercase tracking-wider text-accent">Now Open | Bhaisepati, Lalitpur</span>
        </div>

        <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl uppercase leading-none tracking-tight">
          <span class="text-ink">Train Harder.</span><br />
          <span class="text-accent">Move Faster.</span><br />
          <span class="text-ink">Grow Stronger.</span>
        </h1>

        <p class="mt-6 text-lg sm:text-xl text-ink-muted max-w-xl mx-auto leading-relaxed">
          Professional badminton courts, fully equipped gym, and recovery facilities, all under one roof in Bhaisepati.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
          <NuxtLink to="/book">
            <UiAppButton variant="primary" size="lg">
              Book a Court
              <ChevronRight class="h-4 w-4 ml-1" />
            </UiAppButton>
          </NuxtLink>
          <NuxtLink to="/programs">
            <UiAppButton variant="secondary" size="lg">
              View Programs
            </UiAppButton>
          </NuxtLink>
        </div>

        <!-- Quick info -->
        <div class="mt-10 flex flex-wrap items-center justify-center gap-6 text-sm text-ink-muted">
          <span class="flex items-center gap-1.5">
            <MapPin class="h-4 w-4 text-accent" />
            Bhaisepati, Lalitpur
          </span>
          <span class="flex items-center gap-1.5">
            <Clock class="h-4 w-4 text-accent" />
            {{ BRAND.openingHours }}
          </span>
          <a :href="`tel:+977${BRAND.phone}`" class="flex items-center gap-1.5 hover:text-accent transition-colors">
            <Phone class="h-4 w-4 text-accent" />
            +977 {{ BRAND.phone }}
          </a>
        </div>
      </div>

      <!-- Scroll indicator -->
      <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2">
        <span class="text-xs uppercase tracking-wider text-ink-faint">Scroll</span>
        <div class="h-8 w-[1px] bg-gradient-to-b from-accent/50 to-transparent animate-pulse" />
      </div>
    </section>

    <!-- ═══ TRUST PILLARS ═══ -->
    <section class="border-y border-border bg-surface">
      <div class="mx-auto max-w-7xl px-4 py-14 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
          <div v-for="pillar in pillars" :key="pillar.title" class="text-center group">
            <div class="inline-flex items-center justify-center h-14 w-14 rounded-xl bg-accent/10 mb-4 group-hover:bg-accent/20 transition-colors">
              <component :is="pillar.icon" class="h-6 w-6 text-accent" />
            </div>
            <h3 class="font-display text-lg uppercase tracking-wider text-ink">{{ pillar.title }}</h3>
            <p class="mt-2 text-sm text-ink-muted leading-relaxed">{{ pillar.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ FACILITIES SHOWCASE ═══ -->
    <section class="section-padding">
      <div class="section-container">
        <div class="text-center mb-12">
          <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-2">World-Class Amenities</p>
          <h2 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-ink">Our Facilities</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="facility in (facilities ?? [])"
            :key="facility.id"
            class="group relative overflow-hidden rounded-2xl border border-border bg-surface p-6 hover:border-accent/30 transition-all duration-300"
          >
            <!-- Accent corner -->
            <div class="absolute top-0 right-0 h-20 w-20 bg-gradient-to-bl from-accent/10 to-transparent rounded-bl-3xl" />

            <div class="relative z-10">
              <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent/10 mb-4">
                <Dumbbell v-if="facility.category === 'GYM'" class="h-5 w-5 text-accent" />
                <Flame v-else-if="facility.category === 'SAUNA'" class="h-5 w-5 text-accent" />
                <svg v-else class="h-5 w-5 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="5" r="3" />
                  <path d="M12 8L6 20M12 8L18 20" />
                </svg>
              </div>

              <h3 class="font-display text-xl uppercase tracking-wider text-ink mb-2">{{ facility.name }}</h3>
              <p class="text-sm text-ink-muted mb-4 leading-relaxed">{{ facility.description }}</p>

              <ul class="space-y-2">
                <li v-for="feature in facility.features.slice(0, 3)" :key="feature" class="flex items-center gap-2 text-sm text-ink-muted">
                  <span class="h-1 w-1 rounded-full bg-accent flex-shrink-0" />
                  {{ feature }}
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="text-center mt-8">
          <NuxtLink to="/facilities" class="inline-flex items-center gap-1 text-sm font-medium text-accent hover:underline underline-offset-4">
            Explore all facilities
            <ChevronRight class="h-4 w-4" />
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- ═══ POPULAR PROGRAMS ═══ -->
    <section class="section-padding bg-surface">
      <div class="section-container">
        <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-10 gap-4">
          <div>
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-2">Train With Us</p>
            <h2 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-ink">Popular Programs</h2>
          </div>
          <NuxtLink to="/programs" class="inline-flex items-center gap-1 text-sm font-medium text-accent hover:underline underline-offset-4">
            View all programs
            <ChevronRight class="h-4 w-4" />
          </NuxtLink>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="program in popularPrograms"
            :key="program.id"
            class="group rounded-2xl border border-border bg-canvas overflow-hidden hover:border-accent/30 transition-all duration-300"
          >
            <!-- Header stripe -->
            <div class="h-1 bg-gradient-to-r from-accent via-accent to-accent/50" />

            <div class="p-6">
              <!-- Category badge -->
              <span class="inline-block rounded-full bg-accent/10 px-3 py-1 text-xs font-medium uppercase tracking-wider text-accent mb-3">
                {{ program.category }}
              </span>

              <h3 class="font-display text-xl uppercase tracking-wider text-ink mb-2">{{ program.name }}</h3>
              <p class="text-sm text-ink-muted mb-4 leading-relaxed">{{ program.description }}</p>

              <!-- Features -->
              <ul class="space-y-2 mb-6">
                <li v-for="feature in program.features.slice(0, 3)" :key="feature" class="flex items-center gap-2 text-sm text-ink-muted">
                  <span class="h-1 w-1 rounded-full bg-accent flex-shrink-0" />
                  {{ feature }}
                </li>
              </ul>

              <!-- Price + CTA -->
              <div class="flex items-end justify-between pt-4 border-t border-border">
                <div>
              <p class="text-2xl font-bold text-ink">{{ formatPrice(program.price) }}</p>
                  <p class="text-xs text-ink-muted">per month</p>
                </div>
                <NuxtLink to="/programs" class="inline-flex items-center gap-1 text-sm font-medium text-accent hover:underline underline-offset-4">
                  Learn more
                  <ChevronRight class="h-4 w-4" />
                </NuxtLink>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ STATS COUNTER ═══ -->
    <section class="border-y border-border bg-canvas">
      <div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="(stat, i) in stats" :key="stat.label" :ref="(el) => { stat.el.value = el as HTMLElement }" class="text-center">
            <p class="font-display text-4xl sm:text-5xl text-accent tracking-tight">
              {{ stat.count.value }}<span class="text-accent/60">{{ stat.suffix }}</span>
            </p>
            <p class="mt-2 text-sm uppercase tracking-wider text-ink-muted">{{ stat.label }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ TODAY'S SCHEDULE ═══ -->
    <section v-if="todaySchedule.length > 0" class="section-padding bg-surface">
      <div class="section-container">
        <div class="max-w-2xl mx-auto">
          <div class="text-center mb-8">
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-2">{{ today }}'s Schedule</p>
            <h2 class="font-display text-3xl uppercase tracking-tight text-ink">What's On Today</h2>
          </div>

          <div class="space-y-3">
            <div
              v-for="slot in todaySchedule"
              :key="slot.time"
              class="flex items-center justify-between rounded-xl border border-border bg-canvas px-5 py-4 hover:border-accent/30 transition-colors"
            >
              <div class="flex items-center gap-4">
                <span class="font-display text-lg text-accent">{{ slot.time }}</span>
                <div>
                  <p class="font-medium text-ink">{{ slot.program_name }}</p>
                  <p class="text-xs text-ink-muted">{{ slot.coach }}</p>
                </div>
              </div>
              <span class="rounded-full bg-accent/10 px-3 py-1 text-xs font-medium text-accent">
                {{ slot.level }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ TESTIMONIALS ═══ -->
    <section class="section-padding">
      <div class="section-container">
        <div class="text-center mb-10">
          <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-2">Community Voices</p>
          <h2 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-ink">What Players Say</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="testimonial in (testimonials ?? [])"
            :key="testimonial.name"
            class="rounded-2xl border border-border bg-surface p-6"
          >
            <!-- Stars -->
            <div class="flex gap-0.5 mb-4">
              <Star v-for="i in 5" :key="i" class="h-4 w-4 fill-accent text-accent" />
            </div>
            <p class="text-sm text-ink-muted leading-relaxed italic">"{{ testimonial.quote }}"</p>
            <div class="mt-4 flex items-center gap-3">
              <div class="h-8 w-8 rounded-full bg-accent/20 flex items-center justify-center">
                <span class="text-xs font-bold text-accent">{{ testimonial.name[0] }}</span>
              </div>
              <div>
                <p class="text-sm font-medium text-ink">{{ testimonial.name }}</p>
                <p class="text-xs text-ink-muted">{{ testimonial.role }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ CTA ═══ -->
    <section class="relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-r from-accent/10 via-accent/5 to-transparent" />
      <div class="absolute inset-0 opacity-[0.02]" style="background-image: repeating-linear-gradient(-45deg, #FFB800 0, #FFB800 1px, transparent 0, transparent 50%); background-size: 40px 40px;" />

      <div class="relative z-10 mx-auto max-w-3xl px-4 py-20 text-center">
        <h2 class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-ink">
          Ready to <span class="text-accent">Play?</span>
        </h2>
        <p class="mt-4 text-lg text-ink-muted max-w-md mx-auto">
          Book a court, join a program, or drop in for a session. Your game starts here.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
          <NuxtLink to="/book">
            <UiAppButton variant="primary" size="lg">
              Book a Court
              <ChevronRight class="h-4 w-4 ml-1" />
            </UiAppButton>
          </NuxtLink>
          <a :href="`tel:+977${BRAND.phone}`">
            <UiAppButton variant="ghost" size="lg">
              <Phone class="h-4 w-4 mr-2" />
              Call {{ BRAND.phone }}
            </UiAppButton>
          </a>
        </div>
      </div>
    </section>
  </div>
</template>
