<script setup lang="ts">
import { BRAND } from '~/utils/constants'

useHead({ title: 'FAQ | Bhaisepati Sports Academy' })

const faqs = [
  {
    q: 'How do I book a badminton court?',
    a: 'Visit our Book a Court page, select your preferred date and time slot, fill in your details, and confirm via WhatsApp. We\'ll confirm your booking shortly.',
  },
  {
    q: 'What facilities does BSA offer?',
    a: 'We have professional badminton courts with proper lighting and playing surface, a fully equipped gym for strength and conditioning, and sauna & steam rooms for recovery.',
  },
  {
    q: 'What training programs are available?',
    a: 'We offer Foundation Badminton (beginners), Intermediate Training, Competitive Training (tournament prep), Youth Academy (under 16), Gym & Strength, and Full Membership packages.',
  },
  {
    q: 'Do I need to bring my own equipment?',
    a: 'We have badminton rackets available for casual play. For regular training, we recommend bringing your own racket. Shuttlecocks are provided for all court bookings.',
  },
  {
    q: 'What are the membership options?',
    a: 'We offer monthly programs starting from NPR 2,500 for youth to NPR 8,000 for competitive training. Full membership (gym + courts + sauna) is NPR 6,000/month.',
  },
  {
    q: 'Can I get a trial session?',
    a: 'Yes! We offer trial sessions for all our programs. Book through the website or call us to arrange a visit.',
  },
  {
    q: 'What are your opening hours?',
    a: `We're open ${BRAND.openingHours}. ${BRAND.closedDay} is our rest day.`,
  },
  {
    q: 'Where is BSA located?',
    a: `We're at ${BRAND.address}. You can find us on Google Maps for easy navigation.`,
  },
  {
    q: 'How can I contact you?',
    a: `DM us on Instagram ${BRAND.instagramHandle}, WhatsApp us at +977 ${BRAND.phone}, or email ${BRAND.email}. We typically reply within a few hours.`,
  },
  {
    q: 'Is there parking available?',
    a: 'Yes, we have parking space for bikes and scooters. Limited car parking is also available.',
  },
]

const openIndex = ref<number | null>(null)

function toggle(index: number) {
  openIndex.value = openIndex.value === index ? null : index
}
</script>

<template>
  <div>
    <section class="border-b border-border">
      <div class="section-container py-14 sm:py-20">
        <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-3">Got Questions?</p>
        <h1 class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-ink">FAQ</h1>
      </div>
    </section>

    <div class="mx-auto max-w-3xl px-4 py-12 lg:px-8">
      <div class="divide-y divide-border">
        <div v-for="(faq, i) in faqs" :key="i">
          <button
            class="w-full flex items-center justify-between py-5 text-left group"
            :aria-expanded="openIndex === i"
            @click="toggle(i)"
          >
            <span class="text-sm font-medium text-ink group-hover:text-accent transition-colors">{{ faq.q }}</span>
            <span
              :class="[
                'ml-4 flex-shrink-0 text-accent transition-transform duration-200',
                openIndex === i ? 'rotate-45' : '',
              ]"
            >+</span>
          </button>
          <div
            v-show="openIndex === i"
            class="pb-5 text-sm text-ink-muted leading-relaxed"
          >
            {{ faq.a }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
