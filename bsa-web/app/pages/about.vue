<script setup lang="ts">
import { MapPin, Phone, Clock, Trophy, Users, Heart, Target } from 'lucide-vue-next'
import { BRAND, STATS } from '~/utils/constants'

useSeoMeta({
  title: 'About | Bhaisepati Sports Academy',
  description: 'BSA is a community sports academy in Bhaisepati, Lalitpur with badminton courts, gym, and sauna facilities.',
})
</script>

<template>
  <div>
    <!-- Header -->
    <section class="relative overflow-hidden border-b border-border">
      <div class="absolute inset-0 bg-gradient-to-br from-canvas via-surface to-canvas" />
      <div class="section-container relative z-10 py-14 sm:py-20 text-center">
        <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-3">Est. 2024 | Bhaisepati</p>
        <h1 class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-ink">About BSA</h1>
      </div>
    </section>

    <!-- Mission -->
    <section class="section-padding">
      <div class="section-container max-w-3xl">
        <div class="space-y-6 text-ink-muted leading-relaxed">
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
      </div>
    </section>

    <!-- Values -->
    <section class="section-padding bg-surface">
      <div class="section-container">
        <h2 class="font-display text-3xl uppercase tracking-tight text-ink text-center mb-10">What We Stand For</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="rounded-2xl border border-border bg-canvas p-6 text-center hover:border-accent/30 transition-colors">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent/10 mb-4">
              <Trophy class="h-5 w-5 text-accent" />
            </div>
            <h3 class="font-display text-sm uppercase tracking-wider text-ink">Competitive Spirit</h3>
            <p class="mt-2 text-sm text-ink-muted">We push each other to be better. Every session has purpose.</p>
          </div>
          <div class="rounded-2xl border border-border bg-canvas p-6 text-center hover:border-accent/30 transition-colors">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent/10 mb-4">
              <Users class="h-5 w-5 text-accent" />
            </div>
            <h3 class="font-display text-sm uppercase tracking-wider text-ink">Community First</h3>
            <p class="mt-2 text-sm text-ink-muted">BSA is more than a gym. It's where Bhaisepati comes to move.</p>
          </div>
          <div class="rounded-2xl border border-border bg-canvas p-6 text-center hover:border-accent/30 transition-colors">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent/10 mb-4">
              <Target class="h-5 w-5 text-accent" />
            </div>
            <h3 class="font-display text-sm uppercase tracking-wider text-ink">Discipline</h3>
            <p class="mt-2 text-sm text-ink-muted">Consistency beats talent. We build habits, not hype.</p>
          </div>
          <div class="rounded-2xl border border-border bg-canvas p-6 text-center hover:border-accent/30 transition-colors">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-accent/10 mb-4">
              <Heart class="h-5 w-5 text-accent" />
            </div>
            <h3 class="font-display text-sm uppercase tracking-wider text-ink">Accessibility</h3>
            <p class="mt-2 text-sm text-ink-muted">World-class training, local pricing. Sports for everyone.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact info -->
    <section class="section-padding">
      <div class="section-container max-w-2xl">
        <h2 class="font-display text-3xl uppercase tracking-tight text-ink text-center mb-8">Visit Us</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div class="flex items-start gap-3">
            <MapPin class="h-5 w-5 text-accent mt-0.5 flex-shrink-0" />
            <div>
              <p class="font-medium text-ink">Location</p>
              <a :href="BRAND.googleMaps" target="_blank" rel="noopener noreferrer" class="text-sm text-ink-muted hover:text-accent transition-colors">
                {{ BRAND.address }}
              </a>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <Phone class="h-5 w-5 text-accent mt-0.5 flex-shrink-0" />
            <div>
              <p class="font-medium text-ink">Phone</p>
              <a :href="`tel:+977${BRAND.phone}`" class="text-sm text-ink-muted hover:text-accent transition-colors">+977 {{ BRAND.phone }}</a>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <Clock class="h-5 w-5 text-accent mt-0.5 flex-shrink-0" />
            <div>
              <p class="font-medium text-ink">Hours</p>
              <p class="text-sm text-ink-muted">{{ BRAND.openingHours }}</p>
              <p class="text-xs text-ink-faint">Closed on {{ BRAND.closedDay }}</p>
            </div>
          </div>
          <div class="flex items-start gap-3">
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
