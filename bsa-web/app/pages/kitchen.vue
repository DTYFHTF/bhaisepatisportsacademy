<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next'
import { BRAND } from '~/utils/constants'
import { formatPrice } from '~/utils/formatters'

useSeoMeta({
  title: 'Kitchen | Bhaisepati Sports Academy',
  description: 'Fuel your training with BSA Kitchen. Pre-workout snacks, post-workout recovery meals, and fresh drinks.',
})

const config = useRuntimeConfig()
const { data: menu } = await useFetch<{
  id: string; slug: string; name: string; description: string; price: number;
  category: string; is_popular: boolean
}[]>(`${config.public.apiBase}/kitchen`)

const KITCHEN_CATEGORIES: Record<string, string> = {
  'pre-workout': 'Pre-Workout',
  'post-workout': 'Post-Workout Recovery',
  'snacks': 'Snacks',
  'drinks': 'Drinks & Shakes',
}

const activeCategory = ref<string | null>(null)

const filteredMenu = computed(() => {
  if (!activeCategory.value) return menu.value ?? []
  return (menu.value ?? []).filter((item) => item.category === activeCategory.value)
})

const categories = computed(() => {
  const cats = [...new Set((menu.value ?? []).map((item) => item.category))]
  return cats
})

const whatsappOrder = (item: { name: string; price: number }) => {
  const msg = encodeURIComponent(`Hi BSA Kitchen! I'd like to order: ${item.name} (${formatPrice(item.price)}). Please let me know when it's ready.`)
  return `https://wa.me/977${BRAND.whatsapp}?text=${msg}`
}
</script>

<template>
  <div>
    <!-- Header -->
    <section class="relative overflow-hidden border-b border-border">
      <div class="absolute inset-0 bg-gradient-to-br from-canvas via-surface to-canvas" />
      <div class="absolute top-0 right-0 h-48 w-48 bg-energy/5 rounded-bl-[100px] blur-2xl" />
      <div class="section-container relative z-10 py-14 sm:py-20">
        <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-3">Fuel Your Training</p>
        <h1 class="font-display text-4xl sm:text-5xl uppercase tracking-tight text-ink">BSA Kitchen</h1>
        <p class="mt-4 text-ink-muted max-w-lg leading-relaxed">
          Clean, energizing food made for athletes. Pre-workout boosts, recovery meals, and refreshing drinks, all freshly prepared on site.
        </p>
        <div class="mt-6 flex items-center gap-3">
          <a
            :href="`https://wa.me/977${BRAND.whatsapp}?text=${encodeURIComponent('Hi BSA Kitchen! I\'d like to place an order.')}`"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center gap-2 rounded-lg bg-[#25D366] px-5 py-2.5 text-sm font-bold uppercase tracking-wider text-white hover:bg-[#22c55e] transition-colors"
          >
            Order on WhatsApp
            <ChevronRight class="h-4 w-4" />
          </a>
        </div>
      </div>
    </section>

    <!-- Category tabs -->
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
            v-for="cat in categories"
            :key="cat"
            class="px-4 py-2 rounded-full text-sm font-medium uppercase tracking-wider whitespace-nowrap transition-colors"
            :class="activeCategory === cat ? 'bg-accent text-canvas' : 'bg-surface text-ink-muted hover:text-ink'"
            @click="activeCategory = cat"
          >
            {{ KITCHEN_CATEGORIES[cat] ?? cat }}
          </button>
        </div>
      </div>
    </section>

    <!-- Menu grid -->
    <section class="section-padding">
      <div class="section-container">
        <div v-if="(menu ?? []).length === 0" class="py-16 text-center text-ink-muted">
          Loading menu...
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="item in filteredMenu"
            :key="item.id"
            class="group rounded-2xl border border-border bg-surface overflow-hidden hover:border-accent/30 transition-all duration-300"
          >
            <div class="h-1 bg-gradient-to-r from-energy via-energy to-energy/50" />

            <div class="p-5">
              <div class="flex items-start justify-between gap-2 mb-2">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="inline-block rounded-full bg-accent/10 px-2.5 py-0.5 text-xs font-medium uppercase tracking-wider text-accent">
                    {{ KITCHEN_CATEGORIES[item.category] ?? item.category }}
                  </span>
                  <span v-if="item.is_popular" class="inline-block rounded-full bg-energy/10 px-2.5 py-0.5 text-xs font-medium text-energy">
                    Popular
                  </span>
                </div>
              </div>

              <h3 class="font-display text-lg uppercase tracking-wide text-ink mb-1">{{ item.name }}</h3>
              <p class="text-sm text-ink-muted mb-4 leading-relaxed">{{ item.description }}</p>

              <div class="flex items-center justify-between pt-3 border-t border-border">
                <p class="text-xl font-bold text-ink">{{ formatPrice(item.price) }}</p>
                <a
                  :href="whatsappOrder(item)"
                  target="_blank"
                  rel="noopener"
                  class="inline-flex items-center gap-1.5 rounded-lg bg-[#25D366]/10 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-[#25D366] hover:bg-[#25D366]/20 transition-colors"
                >
                  Order
                  <ChevronRight class="h-3.5 w-3.5" />
                </a>
              </div>
            </div>
          </div>
        </div>

        <div v-if="filteredMenu.length === 0 && (menu ?? []).length > 0" class="text-center py-16">
          <p class="text-ink-muted">No items in this category.</p>
        </div>
      </div>
    </section>

    <!-- Info note -->
    <section class="border-t border-border bg-surface">
      <div class="mx-auto max-w-3xl px-4 py-10 text-center">
        <p class="text-sm text-ink-muted">
          Orders are prepared fresh. Contact us on WhatsApp to place your order and we'll have it ready when you arrive.
          Menu items may vary based on availability.
        </p>
        <a
          :href="`https://wa.me/977${BRAND.whatsapp}?text=${encodeURIComponent('Hi BSA! I have a question about the kitchen menu.')}`"
          target="_blank"
          rel="noopener"
          class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-accent hover:underline underline-offset-4"
        >
          WhatsApp us
          <ChevronRight class="h-4 w-4" />
        </a>
      </div>
    </section>
  </div>
</template>
