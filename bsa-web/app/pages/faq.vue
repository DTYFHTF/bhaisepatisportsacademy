<script setup lang="ts">
useHead({ title: 'FAQ — Bhaisepati Sports Academy' })

const faqs = [
  {
    q: 'How do I book an appointment?',
    a: 'You can book through our website by visiting the Services page and selecting the services you want. Choose your preferred date and time, and confirm via WhatsApp. We\'ll confirm your slot shortly.',
  },
  {
    q: 'What wax types do you offer?',
    a: 'We offer four premium wax types: Rica (gentlest, ideal for sensitive skin), Honey (classic all-rounder), Chocolate (nourishing, great for dry skin), and Sugar (natural, suitable for all skin types).',
  },
  {
    q: 'Is waxing painful?',
    a: 'Some discomfort is normal, especially for first-timers. We use premium imported waxes that are gentler on the skin. Rica wax is our gentlest option for sensitive areas. With regular sessions, discomfort decreases significantly.',
  },
  {
    q: 'How should I prepare for my appointment?',
    a: 'Hair should be at least 1/4 inch long (about 2 weeks of growth). Exfoliate gently 24 hours before. Avoid sun exposure and hot showers right before your appointment.',
  },
  {
    q: 'Do you sell aftercare products?',
    a: 'Yes! We carry skincare and aftercare products specifically for post-wax care. You can order them through our shop with delivery across Kathmandu.',
  },
  {
    q: 'What is the delivery fee for products?',
    a: 'NPR 100 for Kathmandu Valley, NPR 150 for other cities. Orders above NPR 5,000 get free delivery.',
  },
  {
    q: 'Can I cancel or reschedule my booking?',
    a: 'Yes. Please give us at least 4 hours notice for cancellations or reschedules. You can contact us via WhatsApp or phone.',
  },
  {
    q: 'What are your opening hours?',
    a: 'We\'re open Saturday through Thursday, 10am to 7pm. Friday is closed.',
  },
  {
    q: 'Where are you located?',
    a: 'We\'re at Chakrapath, Kathmandu (near Narayangopal Chowk). Easily accessible by public transport.',
  },
  {
    q: 'How do I contact you?',
    a: 'Send us a DM on Instagram @bsa.example.com, WhatsApp us at +977 9821357118, or email hello@bsa.example.com. We typically reply within a few hours.',
  },
]

const openIndex = ref<number | null>(null)

function toggle(index: number) {
  openIndex.value = openIndex.value === index ? null : index
}
</script>

<template>
  <div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <h1 class="text-heading-xl mb-12">Frequently Asked Questions</h1>

    <div class="divide-y divide-border">
      <div v-for="(faq, i) in faqs" :key="i">
        <button
          class="w-full flex items-center justify-between py-5 text-left"
          :aria-expanded="openIndex === i"
          @click="toggle(i)"
        >
          <span class="text-sm font-medium">{{ faq.q }}</span>
          <span
            :class="[
              'ml-4 flex-shrink-0 transition-transform duration-200',
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
</template>
