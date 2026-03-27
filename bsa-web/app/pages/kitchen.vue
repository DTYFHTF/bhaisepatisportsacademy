<script setup lang="ts">
import { ChefHat, Zap, Leaf, ShoppingBag } from 'lucide-vue-next'
import { BRAND, KITCHEN_MENU, KITCHEN_CATEGORIES } from '~/utils/constants'
import { formatNPR } from '~/utils/formatters'

useHead({ title: 'Kitchen | Bhaisepati Sports Academy' })

const activeCategory = ref<keyof typeof KITCHEN_CATEGORIES | 'all'>('all')

const filteredItems = computed(() =>
  activeCategory.value === 'all'
    ? KITCHEN_MENU
    : KITCHEN_MENU.filter((item) => item.category === activeCategory.value),
)

const popular = computed(() => KITCHEN_MENU.filter((item) => item.isPopular))

function orderOnWhatsApp(itemName?: string) {
  const text = itemName
    ? `Hi BSA Kitchen! I'd like to order: ${itemName}`
    : `Hi BSA Kitchen! Please share today's menu.`
  window.open(
    `https://wa.me/${BRAND.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(text)}`,
    '_blank',
    'noopener',
  )
}
</script>

<template>
  <div>
    <!-- ═══ HERO ═══ -->
    <section class="relative overflow-hidden bg-gradient-to-br from-canvas via-surface to-canvas py-20">
      <div class="absolute inset-0 opacity-[0.025]" style="background-image: repeating-linear-gradient(45deg, #FFB800 0, #FFB800 1px, transparent 0, transparent 50%); background-size: 40px 40px;" />
      <div class="absolute top-8 right-8 opacity-10">
        <ChefHat class="h-32 w-32 text-accent" />
      </div>
      <div class="relative mx-auto max-w-7xl px-4 lg:px-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-accent/30 bg-accent/10 px-4 py-1.5 mb-6">
          <ChefHat class="h-3.5 w-3.5 text-accent" />
          <span class="text-xs font-semibold uppercase tracking-widest text-accent">BSA Kitchen</span>
        </div>
        <h1 class="font-display text-5xl uppercase tracking-wide text-ink lg:text-7xl">
          Fuel Your<br /><span class="text-accent">Performance</span>
        </h1>
        <p class="mt-4 max-w-xl text-ink-muted">
          Fresh, sport-specific meals prepared on-site. From pre-training energy snacks to full post-workout recovery meals, everything your body needs.
        </p>
        <div class="mt-8 flex flex-wrap items-center gap-4">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl bg-accent px-6 py-3 font-semibold text-canvas transition hover:bg-accent/90"
            @click="orderOnWhatsApp()"
          >
            <ShoppingBag class="h-4 w-4" />
            Order on WhatsApp
          </button>
          <div class="flex items-center gap-4 text-sm text-ink-muted">
            <span class="flex items-center gap-1.5"><Leaf class="h-4 w-4 text-green-400" />Fresh daily</span>
            <span class="flex items-center gap-1.5"><Zap class="h-4 w-4 text-accent" />Ready in minutes</span>
          </div>
        </div>
      </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">

      <!-- ═══ POPULAR ═══ -->
      <section class="mb-16">
        <h2 class="mb-6 font-display text-3xl uppercase tracking-wide text-ink">Popular Picks</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="item in popular"
            :key="item.id"
            class="group relative flex flex-col justify-between rounded-2xl border border-border bg-surface p-5 transition hover:border-accent/40"
          >
            <div class="mb-4">
              <div class="mb-1 flex items-center justify-between">
                <span class="rounded-full bg-accent/10 px-2 py-0.5 text-xs font-medium text-accent capitalize">
                  {{ KITCHEN_CATEGORIES[item.category] }}
                </span>
                <span class="text-sm font-bold text-accent">{{ formatNPR(item.price) }}</span>
              </div>
              <h3 class="mt-2 font-semibold text-ink">{{ item.name }}</h3>
              <p class="mt-1 text-xs text-ink-muted leading-relaxed">{{ item.description }}</p>
            </div>
            <button
              type="button"
              class="mt-auto w-full rounded-xl border border-accent/30 py-2 text-xs font-semibold text-accent transition hover:bg-accent hover:text-canvas"
              @click="orderOnWhatsApp(item.name)"
            >
              Order Now
            </button>
          </div>
        </div>
      </section>

      <!-- ═══ FULL MENU ═══ -->
      <section>
        <h2 class="mb-6 font-display text-3xl uppercase tracking-wide text-ink">Full Menu</h2>

        <!-- Category tabs -->
        <div class="mb-8 flex flex-wrap gap-2">
          <button
            type="button"
            :class="[
              'rounded-full px-4 py-1.5 text-sm font-medium transition-all',
              activeCategory === 'all'
                ? 'bg-accent text-canvas'
                : 'border border-border text-ink-muted hover:border-accent/40 hover:text-ink',
            ]"
            @click="activeCategory = 'all'"
          >
            All
          </button>
          <button
            v-for="(label, key) in KITCHEN_CATEGORIES"
            :key="key"
            type="button"
            :class="[
              'rounded-full px-4 py-1.5 text-sm font-medium transition-all',
              activeCategory === key
                ? 'bg-accent text-canvas'
                : 'border border-border text-ink-muted hover:border-accent/40 hover:text-ink',
            ]"
            @click="activeCategory = key"
          >
            {{ label }}
          </button>
        </div>

        <!-- Menu grid -->
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="item in filteredItems"
            :key="item.id"
            class="flex items-start justify-between gap-4 rounded-xl border border-border bg-surface p-4 transition hover:border-accent/30"
          >
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <h3 class="font-semibold text-ink text-sm">{{ item.name }}</h3>
                <span v-if="item.isPopular" class="rounded-full bg-accent/10 px-1.5 py-0.5 text-2xs font-bold uppercase tracking-wider text-accent">Hot</span>
              </div>
              <p class="text-xs text-ink-muted leading-relaxed">{{ item.description }}</p>
            </div>
            <div class="flex flex-col items-end gap-2 shrink-0">
              <span class="font-bold text-accent text-sm">{{ formatNPR(item.price) }}</span>
              <button
                type="button"
                class="rounded-lg border border-accent/30 px-3 py-1 text-xs font-medium text-accent transition hover:bg-accent hover:text-canvas"
                @click="orderOnWhatsApp(item.name)"
              >
                Order
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- ═══ INFO STRIP ═══ -->
      <section class="mt-16 rounded-2xl border border-border bg-surface p-6 sm:p-8">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 text-center">
          <div>
            <p class="font-display text-2xl uppercase text-accent">6 AM – 8 PM</p>
            <p class="mt-1 text-sm text-ink-muted">Kitchen hours, 7 days a week</p>
          </div>
          <div>
            <p class="font-display text-2xl uppercase text-accent">On-Site</p>
            <p class="mt-1 text-sm text-ink-muted">Prepared fresh at the academy</p>
          </div>
          <div>
            <p class="font-display text-2xl uppercase text-accent">WhatsApp</p>
            <p class="mt-1 text-sm text-ink-muted">Order ahead, skip the queue</p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
