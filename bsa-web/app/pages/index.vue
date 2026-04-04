<script setup lang="ts">
import { Trophy, Zap, Users, Clock, MapPin, Phone, ChevronRight, Star, Dumbbell, Flame, Send } from 'lucide-vue-next'
import { BRAND, IMAGES, PROGRAM_IMAGES } from '~/utils/constants'
import { formatPrice } from '~/utils/formatters'

const config = useRuntimeConfig()

const { data: rawStats } = await useFetch<{ value_label: string; label: string }[]>(
  `${config.public.apiBase}/stats`, { server: false },
)

const { data: facilities } = await useFetch<{ id: string; name: string; category: string; description: string; features: string[]; icon: string; image_url: string | null }[]>(
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

// Animated counter — wait for data before attaching IntersectionObservers
const MAX_STATS = 6
const counterCounts = Array.from({ length: MAX_STATS }, () => ref(0))
const counterEls = Array.from({ length: MAX_STATS }, () => ref<HTMLElement | null>(null))

watch(rawStats, (statList) => {
  if (!statList?.length) return
  nextTick(() => {
    statList.forEach((s, i) => {
      if (i >= MAX_STATS) return
      const target = parseInt((s.value_label ?? '').replace(/\D/g, '')) || 0
      const el = counterEls[i]
      const count = counterCounts[i]
      if (!el.value) return

      const runCounter = () => {
        const start = performance.now()
        const animate = (now: number) => {
          const progress = Math.min((now - start) / 2000, 1)
          const eased = 1 - Math.pow(1 - progress, 3)
          count.value = Math.floor(eased * target)
          if (progress < 1) requestAnimationFrame(animate)
        }
        requestAnimationFrame(animate)
      }

      // If already in viewport, run immediately
      const rect = el.value.getBoundingClientRect()
      if (rect.top < window.innerHeight && rect.bottom > 0) {
        runCounter()
        return
      }

      const observer = new IntersectionObserver(
        ([entry]) => {
          if (entry.isIntersecting) {
            runCounter()
            observer.disconnect()
          }
        },
        { threshold: 0 },
      )
      observer.observe(el.value)
    })
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

const { settings } = useSettings()

// Testimonial submission form
const tName = ref('')
const tRole = ref('')
const tQuote = ref('')
const tSubmitting = ref(false)
const tSubmitted = ref(false)
const tError = ref('')

async function submitTestimonial() {
  if (!tName.value.trim() || tQuote.value.trim().length < 20) return
  tSubmitting.value = true
  tError.value = ''
  try {
    await $fetch(`${config.public.apiBase}/testimonials`, {
      method: 'POST',
      body: { name: tName.value, role: tRole.value || undefined, quote: tQuote.value },
    })
    tSubmitted.value = true
  }
  catch {
    tError.value = 'Something went wrong. Please try again.'
  }
  finally {
    tSubmitting.value = false
  }
}

const pillars = [
  { icon: Trophy, title: 'Competition Ready', desc: 'Professional courts with tournament-grade equipment and lighting', iconSize: 'h-6 w-6' },
  { icon: Zap, title: 'Elite Coaching', desc: 'Trained coaches for every skill level, from beginner to competitive', iconSize: 'h-8 w-8' },
  { icon: Users, title: 'Community Driven', desc: 'Join 500+ active members. Train together, grow together', iconSize: 'h-6 w-6' },
]

// Hero parallax
const heroImgRef = ref<HTMLElement | null>(null)
onMounted(() => {
  const handleScroll = () => {
    if (heroImgRef.value) {
      const y = window.scrollY
      heroImgRef.value.style.transform = `translateY(${y * 0.35}px) scale(1.15)`
    }
  }
  window.addEventListener('scroll', handleScroll, { passive: true })
  onUnmounted(() => window.removeEventListener('scroll', handleScroll))
})
</script>

<template>
  <div>
    <!-- ═══ HERO ═══ -->
    <section class="relative min-h-[100vh] flex items-center justify-center overflow-hidden">
      <!-- Video background (free stock, no brand). Falls back to poster image if video unavailable. -->
      <div ref="heroImgRef" class="absolute inset-0 hero-parallax-img" style="transform: scale(1.15)">
        <video
          autoplay
          muted
          loop
          playsinline
          :poster="IMAGES.hero"
          class="absolute inset-0 w-full h-full object-cover"
        >
          <!-- Badminton & sports training – Mixkit free stock -->
          <source src="https://assets.mixkit.co/videos/preview/mixkit-sport-badminton-player-training-21143-large.mp4" type="video/mp4" />
          <source src="https://assets.mixkit.co/videos/preview/mixkit-two-people-playing-badminton-outdoors-34802-large.mp4" type="video/mp4" />
          <source src="https://assets.mixkit.co/videos/preview/mixkit-people-working-out-in-the-gym-34752-large.mp4" type="video/mp4" />
        </video>
      </div>
      <!-- Dark gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-black/70" />
      <!-- Bottom fade to white canvas -->
      <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-canvas to-transparent" />
      <!-- Subtle diagonal lines -->
      <div class="absolute inset-0 opacity-[0.04]" style="background-image: repeating-linear-gradient(45deg, #fff 0, #fff 1px, transparent 0, transparent 50%); background-size: 60px 60px;" />

      <div class="relative z-10 text-center px-4 py-20 max-w-4xl mx-auto">
        <!-- Badge — no v-scroll, always visible above fold -->
        <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 backdrop-blur-sm px-4 py-1.5 mb-6 animate-[fade-in_0.6s_ease-out_both]">
          <span class="h-1.5 w-1.5 rounded-full bg-accent animate-pulse" />
          <span class="text-xs font-medium uppercase tracking-wider text-white/90">Now Open | Bhaisepati, Lalitpur</span>
        </div>

        <h1 class="font-display text-5xl sm:text-6xl lg:text-8xl uppercase leading-none tracking-tight animate-[fade-in-up_0.7s_ease-out_0.1s_both]">
          <span class="text-white">Train Harder.</span><br />
          <span class="text-accent drop-shadow-[0_0_30px_rgba(232,0,30,0.5)]">Move Faster.</span><br />
          <span class="text-white">Grow Stronger.</span>
        </h1>

        <p class="mt-6 text-lg sm:text-xl text-white/80 max-w-xl mx-auto leading-relaxed animate-[fade-in-up_0.7s_ease-out_0.3s_both]">
          Professional badminton courts, fully equipped gym, and recovery facilities, all under one roof in Bhaisepati.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4 animate-[fade-in-up_0.7s_ease-out_0.5s_both]">
          <NuxtLink to="/book">
            <UiAppButton variant="primary" size="lg">
              Book a Court
              <ChevronRight class="h-4 w-4 ml-1" />
            </UiAppButton>
          </NuxtLink>
          <NuxtLink to="/programs">
            <button class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 backdrop-blur-sm px-6 py-3 text-sm font-bold uppercase tracking-wider text-white hover:bg-white/20 transition-all">
              View Programs
            </button>
          </NuxtLink>
        </div>

        <!-- Quick info -->
        <div class="mt-10 flex flex-wrap items-center justify-center gap-6 text-sm text-white/70 animate-[fade-in-up_0.7s_ease-out_0.7s_both]">
          <a :href="settings.googleMapsUrl ?? BRAND.googleMaps" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1.5 hover:text-white transition-colors">
            <MapPin class="h-4 w-4 text-accent" />
            Bhaisepati, Lalitpur
          </a>
          <span class="flex items-center gap-1.5">
            <Clock class="h-4 w-4 text-accent" />
            {{ BRAND.openingHours }}
          </span>
          <a :href="`tel:+977${BRAND.phone}`" class="flex items-center gap-1.5 hover:text-white transition-colors">
            <Phone class="h-4 w-4 text-accent" />
            +977 {{ BRAND.phone }}
          </a>
        </div>
      </div>

      <!-- Scroll indicator -->
      <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 z-10">
        <span class="text-xs uppercase tracking-wider text-white/50">Scroll</span>
        <div class="h-8 w-[1px] bg-gradient-to-b from-white/50 to-transparent animate-pulse" />
      </div>
    </section>

    <!-- ═══ TRUST PILLARS ═══ -->
    <section class="border-y border-border bg-surface">
      <div class="mx-auto max-w-7xl px-4 py-14 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
          <div v-for="(pillar, i) in pillars" :key="pillar.title" v-scroll:[i*150]="'fade-up'" class="text-center group">
            <div class="inline-flex items-center justify-center h-14 w-14 rounded-xl bg-accent/10 mb-4 group-hover:bg-accent/20 transition-colors">
              <component :is="pillar.icon" :class="[pillar.iconSize, 'text-accent']" />
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
        <div v-scroll="'fade-up'" class="text-center mb-12">
          <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-2">World-Class Amenities</p>
          <h2 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-ink">Our Facilities</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="(facility, fIdx) in (facilities ?? [])"
            :key="facility.id"
            v-scroll:[fIdx*150]="'scale-in'"
            class="group relative overflow-hidden rounded-2xl border border-border pb-16 hover:border-accent/30 transition-all duration-500 min-h-[300px] cursor-pointer"
          >
            <!-- Background image -->
            <img
              v-if="facility.image_url"
              :src="facility.image_url"
              :alt="facility.name"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
            />
            <!-- Overlay: dark gradient when image present, solid dark when not -->
            <div
              v-if="facility.image_url"
              class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/30 transition-opacity duration-500 group-hover:from-black/95 group-hover:via-black/70"
            />
            <div v-else class="absolute inset-0 bg-ink" />

            <div class="relative z-10 p-6">
              <h3 class="font-display text-xl uppercase tracking-wider text-white mb-2">{{ facility.name }}</h3>
              <p class="text-sm mb-4 leading-relaxed text-white/70">{{ facility.description }}</p>

              <ul class="space-y-2">
                <li v-for="feature in facility.features.slice(0, 3)" :key="feature" class="flex items-center gap-2 text-sm text-white/70">
                  <span class="h-1 w-1 rounded-full bg-accent flex-shrink-0" />
                  {{ feature }}
                </li>
              </ul>
            </div>

            <!-- Icon badge — bottom-left for first, bottom-right for last, bottom-center for middle -->
            <div
              class="absolute bottom-4 z-10 inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent shadow-lg"
              :class="fIdx === 0 ? 'left-4' : fIdx === (facilities ?? []).length - 1 ? 'right-4' : 'left-1/2 -translate-x-1/2'"
            >
              <Dumbbell v-if="facility.category === 'GYM'" class="h-5 w-5 text-white" />
              <Flame v-else-if="facility.category === 'SAUNA'" class="h-5 w-5 text-white" />
              <svg v-else class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="5" r="3" />
                <path d="M12 8L6 20M12 8L18 20" />
              </svg>
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
        <div v-scroll="'fade-up'" class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-10 gap-4">
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
            v-for="(program, pIdx) in popularPrograms"
            :key="program.id"
            v-scroll:[pIdx*150]="'fade-up'"
            class="group rounded-2xl border border-border bg-canvas overflow-hidden hover:border-accent/30 hover:shadow-xl hover:shadow-accent/5 transition-all duration-500"
          >
            <!-- Program category image -->
            <div class="relative h-44 overflow-hidden">
              <img
                :src="PROGRAM_IMAGES[program.category] || IMAGES.badmintonCourt"
                :alt="program.name"
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />
              <!-- Category badge on image -->
              <span class="absolute top-3 left-3 rounded-full bg-accent px-3 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-md">
                {{ program.category }}
              </span>
              <span v-if="program.is_popular" class="absolute top-3 right-3 rounded-full bg-energy px-3 py-1 text-xs font-bold text-white shadow-md">
                Popular
              </span>
            </div>

            <div class="p-6">
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
    <section class="relative border-y border-border overflow-hidden">
      <!-- Stats background image -->
      <div class="absolute inset-0">
        <img :src="IMAGES.teamSport" alt="" class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-black/80" />
      </div>
      <div class="relative z-10 mx-auto max-w-7xl px-4 py-16 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="(stat, i) in stats" :key="stat.label" v-scroll:[i*100]="'fade-up'" :ref="(el) => { stat.el.value = el as HTMLElement }" class="text-center">
            <p class="font-display text-4xl sm:text-5xl text-accent tracking-tight">
              {{ stat.count.value }}<span class="text-accent/60">{{ stat.suffix }}</span>
            </p>
            <p class="mt-2 text-sm uppercase tracking-wider text-white/70">{{ stat.label }}</p>
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
        <div v-scroll="'fade-up'" class="text-center mb-10">
          <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-2">Community Voices</p>
          <h2 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-ink">What Players Say</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="(testimonial, tIdx) in (testimonials ?? [])"
            :key="testimonial.name"
            v-scroll:[tIdx*150]="'fade-up'"
            class="rounded-2xl border border-border bg-surface p-6 hover:shadow-lg hover:shadow-accent/5 transition-all duration-500"
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

        <!-- Leave a testimonial -->
        <div class="mt-12 max-w-xl mx-auto">
          <div class="rounded-2xl border border-border bg-surface p-6">
            <h3 class="font-display text-lg uppercase tracking-wider text-ink mb-1">Share Your Experience</h3>
            <p class="text-xs text-ink-muted mb-5">Your review will be shown after approval.</p>

            <div v-if="tSubmitted" class="flex items-center gap-3 rounded-xl bg-accent/10 border border-accent/20 px-4 py-3">
              <span class="h-2 w-2 rounded-full bg-accent" />
              <p class="text-sm font-medium text-accent">Thank you! Your testimonial is pending review.</p>
            </div>

            <form v-else class="space-y-4" @submit.prevent="submitTestimonial">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-1.5 block">Name *</label>
                  <input
                    v-model="tName"
                    type="text"
                    required
                    maxlength="100"
                    placeholder="Your name"
                    class="w-full rounded-lg border border-border bg-canvas px-3 py-2.5 text-sm text-ink focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                  />
                </div>
                <div>
                  <label class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-1.5 block">Role</label>
                  <input
                    v-model="tRole"
                    type="text"
                    maxlength="100"
                    placeholder="e.g. Badminton Player"
                    class="w-full rounded-lg border border-border bg-canvas px-3 py-2.5 text-sm text-ink focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                  />
                </div>
              </div>
              <div>
                <label class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-1.5 block">Your Review * <span class="normal-case font-normal">(min 20 chars)</span></label>
                <textarea
                  v-model="tQuote"
                  required
                  rows="3"
                  maxlength="500"
                  placeholder="Tell us about your experience at BSA..."
                  class="w-full rounded-lg border border-border bg-canvas px-3 py-2.5 text-sm text-ink focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent resize-none"
                />
              </div>
              <p v-if="tError" class="text-xs text-error">{{ tError }}</p>
              <UiAppButton
                type="submit"
                variant="primary"
                size="sm"
                :loading="tSubmitting"
                :disabled="!tName.trim() || tQuote.trim().length < 20"
              >
                <Send class="h-3.5 w-3.5 mr-1.5" />
                Submit Review
              </UiAppButton>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ GALLERY ═══ -->
    <section class="section-padding bg-ink">
      <div class="section-container">
        <!-- Heading -->
        <div v-scroll="'fade-up'" class="text-center mb-10">
          <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-2">Visual Tour</p>
          <h2 class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-white">Life at BSA</h2>
          <p class="mt-3 text-sm text-white/50 max-w-sm mx-auto">Courts, gym, sauna — see what's waiting for you.</p>
        </div>

        <!-- Bento grid: 1 large (2×2) + 4 cells -->
        <div class="grid grid-cols-2 lg:grid-cols-4 auto-rows-[200px] lg:auto-rows-[240px] gap-3">
          <!-- Large featured -->
          <div
            v-scroll="'scale-in'"
            class="col-span-2 row-span-2 relative overflow-hidden rounded-2xl group cursor-pointer"
          >
            <img
              :src="IMAGES.badmintonCourt"
              alt="Professional badminton courts"
              loading="lazy"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent" />
            <div class="absolute inset-0 bg-accent/0 group-hover:bg-accent/10 transition-colors duration-300" />
            <div class="absolute bottom-5 left-5">
              <span class="inline-flex items-center gap-1.5 rounded-full bg-accent px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-white shadow-lg">
                Professional Courts
              </span>
            </div>
          </div>

          <!-- 4 smaller tiles -->
          <div
            v-for="(tile, i) in [
              { src: IMAGES.gym,         caption: 'Gym Floor' },
              { src: IMAGES.sauna,       caption: 'Sauna & Steam' },
              { src: IMAGES.gymTraining, caption: 'Strength Training' },
              { src: IMAGES.teamSport,   caption: 'Team Sessions' },
            ]"
            :key="tile.caption"
            v-scroll:[(i + 1) * 100]="'fade-up'"
            class="relative overflow-hidden rounded-2xl group cursor-pointer"
          >
            <img
              :src="tile.src"
              :alt="tile.caption"
              loading="lazy"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
            />
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/55 transition-colors duration-300 flex items-end p-3">
              <span class="text-xs font-bold uppercase tracking-wider text-white translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                {{ tile.caption }}
              </span>
            </div>
          </div>
        </div>

        <!-- Instagram link -->
        <div v-scroll="'fade-up'" class="text-center mt-8">
          <a
            :href="BRAND.instagram"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 text-sm font-medium text-white/50 hover:text-white transition-colors"
          >
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
            </svg>
            Follow <strong class="text-white/70">{{ BRAND.instagramHandle }}</strong> for daily updates
          </a>
        </div>
      </div>
    </section>

    <!-- ═══ CTA ═══ -->
    <section class="relative overflow-hidden">
      <!-- CTA background image -->
      <div class="absolute inset-0">
        <img :src="IMAGES.gym" alt="" class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/75 to-black/85" />
      </div>
      <div class="absolute inset-0 opacity-[0.03]" style="background-image: repeating-linear-gradient(-45deg, #fff 0, #fff 1px, transparent 0, transparent 50%); background-size: 40px 40px;" />

      <div v-scroll="'fade-up'" class="relative z-10 mx-auto max-w-3xl px-4 py-20 text-center">
        <h2 class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-white">
          Ready to <span class="text-accent">Play?</span>
        </h2>
        <p class="mt-4 text-lg text-white/70 max-w-md mx-auto">
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
            <button class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 backdrop-blur-sm px-6 py-3 text-sm font-bold uppercase tracking-wider text-white hover:bg-white/20 transition-all">
              <Phone class="h-4 w-4" />
              Call {{ BRAND.phone }}
            </button>
          </a>
        </div>
      </div>
    </section>
  </div>
</template>
