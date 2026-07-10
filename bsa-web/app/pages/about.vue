<script setup lang="ts">
import { MapPin, Phone, Clock, Trophy, Users, Heart, Target } from 'lucide-vue-next'
import { BRAND, IMAGES, GALLERY_IMAGES } from '~/utils/constants'

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

    <!-- Facility Photo Gallery -->
    <section class="section-padding bg-surface">
      <div class="section-container">
        <div v-scroll="'fade-up'" class="text-center mb-10">
          <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-2">Inside BSA</p>
          <h2 class="font-display text-3xl sm:text-4xl uppercase tracking-tight text-ink">Facility Gallery</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
          <div
            v-for="(img, i) in GALLERY_IMAGES"
            :key="img.caption"
            v-scroll="'scale-in'"
            class="relative aspect-[4/3] overflow-hidden rounded-2xl group cursor-pointer"
          >
            <img
              :src="img.src"
              :alt="img.caption"
              loading="lazy"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
            />
            <!-- Dark overlay with caption on hover -->
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/50 transition-colors duration-300 flex items-end p-4">
              <span
                class="text-xs font-bold uppercase tracking-widest text-white translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300"
              >{{ img.caption }}</span>
            </div>
            <!-- Red accent corner -->
            <div class="absolute top-3 right-3 h-1.5 w-1.5 rounded-full bg-accent opacity-0 group-hover:opacity-100 scale-0 group-hover:scale-100 transition-all duration-300" />
          </div>
        </div>
        <div class="text-center mt-8">
          <a
            :href="BRAND.instagram"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 text-sm font-medium text-ink-muted hover:text-accent transition-colors"
          >
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
            </svg>
            More photos on <strong>{{ BRAND.instagramHandle }}</strong>
          </a>
        </div>
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
              <br />
              <a href="tel:+9779843484950" class="text-sm text-ink-muted hover:text-accent transition-colors">+977 9843484950</a>
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
