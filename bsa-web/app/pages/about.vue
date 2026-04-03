<script setup lang="ts">
import { MapPin, Phone, Clock, Trophy, Users, Heart, Target } from 'lucide-vue-next'
import { BRAND, IMAGES } from '~/utils/constants'

useSeoMeta({
  title: 'About | Bhaisepati Sports Academy',
  description: 'BSA is a community sports academy in Bhaisepati, Lalitpur with badminton courts, gym, and sauna facilities.',
})
</script>

<template>
  <div>
    <!-- Header -->
    <section class="relative overflow-hidden border-b border-border min-h-[350px] flex items-end">
      <div class="absolute inset-0">
        <img :src="IMAGES.teamSport" alt="About BSA" class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20" />
      </div>
      <div class="section-container relative z-10 pb-10 pt-20 text-center">
        <p v-scroll="'fade-up'" class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-3">Est. 2024 | Bhaisepati</p>
        <h1 v-scroll:100="'fade-up'" class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-white">About BSA</h1>
      </div>
    </section>

    <!-- Mission with side image -->
    <section class="section-padding">
      <div class="section-container">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <!-- Text -->
          <div v-scroll="'fade-left'" class="space-y-6 text-ink-muted leading-relaxed">
            <p class="text-lg">
              Bhaisepati Sports Academy was built with a clear mission: make professional sports training accessible to everyone in Bhaisepati and the greater Lalitpur area.
            </p>
            <p>
              What started as a passion for badminton has grown into a full sports facility, complete with professional courts, a fully equipped gym for strength and conditioning, and sauna & steam rooms for recovery. We believe that great training facilities shouldn't require a trip across the city.
            </p>
            <p>
              Our focus is on building a <strong class="text-ink">community of athletes</strong>, from kids picking up their first racket to competitive players training for tournaments. Every session, every match, every rep at BSA is a step toward becoming a stronger version of yourself.
            </p>
          </div>
          <!-- Image -->
          <div v-scroll="'fade-right'" class="relative aspect-[4/3] rounded-2xl overflow-hidden group">
            <img :src="IMAGES.badmintonCourt" alt="BSA Badminton Courts" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent" />
            <div class="absolute bottom-4 left-4 inline-flex items-center gap-2 rounded-full bg-accent px-4 py-2 shadow-lg">
              <span class="text-xs font-bold uppercase tracking-wider text-white">Since 2024</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Values -->
    <section class="section-padding bg-surface">
      <div class="section-container">
        <h2 v-scroll="'fade-up'" class="font-display text-3xl uppercase tracking-tight text-ink text-center mb-10">What We Stand For</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-scroll:0="'fade-up'" class="rounded-2xl border border-border bg-canvas p-6 text-center hover:border-accent/30 hover:shadow-lg transition-all duration-500">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent/10 mb-4">
              <Trophy class="h-5 w-5 text-accent" />
            </div>
            <h3 class="font-display text-sm uppercase tracking-wider text-ink">Competitive Spirit</h3>
            <p class="mt-2 text-sm text-ink-muted">We push each other to be better. Every session has purpose.</p>
          </div>
          <div v-scroll:100="'fade-up'" class="rounded-2xl border border-border bg-canvas p-6 text-center hover:border-accent/30 hover:shadow-lg transition-all duration-500">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent/10 mb-4">
              <Users class="h-5 w-5 text-accent" />
            </div>
            <h3 class="font-display text-sm uppercase tracking-wider text-ink">Community First</h3>
            <p class="mt-2 text-sm text-ink-muted">BSA is more than a gym. It's where Bhaisepati comes to move.</p>
          </div>
          <div v-scroll:200="'fade-up'" class="rounded-2xl border border-border bg-canvas p-6 text-center hover:border-accent/30 hover:shadow-lg transition-all duration-500">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent/10 mb-4">
              <Target class="h-5 w-5 text-accent" />
            </div>
            <h3 class="font-display text-sm uppercase tracking-wider text-ink">Discipline</h3>
            <p class="mt-2 text-sm text-ink-muted">Consistency beats talent. We build habits, not hype.</p>
          </div>
          <div v-scroll:300="'fade-up'" class="rounded-2xl border border-border bg-canvas p-6 text-center hover:border-accent/30 hover:shadow-lg transition-all duration-500">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent/10 mb-4">
              <Heart class="h-5 w-5 text-accent" />
            </div>
            <h3 class="font-display text-sm uppercase tracking-wider text-ink">Accessibility</h3>
            <p class="mt-2 text-sm text-ink-muted">World-class training, local pricing. Sports for everyone.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Gallery strip -->
    <section class="py-2 bg-canvas overflow-hidden">
      <div class="flex gap-2 animate-scroll-x" style="width: max-content;">
        <img :src="IMAGES.badmintonCourt" alt="" class="h-48 w-72 object-cover rounded-xl" />
        <img :src="IMAGES.gym" alt="" class="h-48 w-72 object-cover rounded-xl" />
        <img :src="IMAGES.sauna" alt="" class="h-48 w-72 object-cover rounded-xl" />
        <img :src="IMAGES.teamSport" alt="" class="h-48 w-72 object-cover rounded-xl" />
        <img :src="IMAGES.gymTraining" alt="" class="h-48 w-72 object-cover rounded-xl" />
        <img :src="IMAGES.badmintonPlayer" alt="" class="h-48 w-72 object-cover rounded-xl" />
        <!-- Duplicate for infinite scroll -->
        <img :src="IMAGES.badmintonCourt" alt="" class="h-48 w-72 object-cover rounded-xl" />
        <img :src="IMAGES.gym" alt="" class="h-48 w-72 object-cover rounded-xl" />
        <img :src="IMAGES.sauna" alt="" class="h-48 w-72 object-cover rounded-xl" />
        <img :src="IMAGES.teamSport" alt="" class="h-48 w-72 object-cover rounded-xl" />
      </div>
    </section>

    <!-- Contact info -->
    <section class="section-padding">
      <div class="section-container max-w-2xl">
        <h2 v-scroll="'fade-up'" class="font-display text-3xl uppercase tracking-tight text-ink text-center mb-8">Visit Us</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div v-scroll:0="'fade-up'" class="flex items-start gap-3">
            <MapPin class="h-5 w-5 text-accent mt-0.5 flex-shrink-0" />
            <div>
              <p class="font-medium text-ink">Location</p>
              <a :href="BRAND.googleMaps" target="_blank" rel="noopener noreferrer" class="text-sm text-ink-muted hover:text-accent transition-colors">
                {{ BRAND.address }}
              </a>
            </div>
          </div>
          <div v-scroll:100="'fade-up'" class="flex items-start gap-3">
            <Phone class="h-5 w-5 text-accent mt-0.5 flex-shrink-0" />
            <div>
              <p class="font-medium text-ink">Phone</p>
              <a :href="`tel:+977${BRAND.phone}`" class="text-sm text-ink-muted hover:text-accent transition-colors">+977 {{ BRAND.phone }}</a>
            </div>
          </div>
          <div v-scroll:200="'fade-up'" class="flex items-start gap-3">
            <Clock class="h-5 w-5 text-accent mt-0.5 flex-shrink-0" />
            <div>
              <p class="font-medium text-ink">Hours</p>
              <p class="text-sm text-ink-muted">{{ BRAND.openingHours }}</p>
              <p class="text-xs text-ink-faint">Closed on {{ BRAND.closedDay }}</p>
            </div>
          </div>
          <div v-scroll:300="'fade-up'" class="flex items-start gap-3">
            <Icon name="simple-icons:instagram" size="20" class="text-accent mt-0.5 flex-shrink-0" aria-hidden="true" />
            <div>
              <p class="font-medium text-ink">Instagram</p>
              <a
                :href="BRAND.instagram"
                target="_blank"
                rel="noopener noreferrer"
                class="text-sm text-ink-muted hover:text-accent transition-colors"
              >{{ BRAND.instagramHandle }}</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
