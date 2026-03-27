<script setup lang="ts">
import { ShoppingBag, CheckCircle } from 'lucide-vue-next'
import type { Product } from '~/types/product'
import { BRAND, CATEGORY_LABELS, CATEGORY_SLUGS, CATEGORY_SLUG_BY_KEY } from '~/utils/constants'

// Shop interest survey
const surveyOptions = [
  { id: 'rackets', label: 'Badminton Rackets & Strings' },
  { id: 'shuttles', label: 'Shuttlecocks' },
  { id: 'shoes', label: 'Court Shoes' },
  { id: 'apparel', label: 'Sports Apparel' },
  { id: 'gym', label: 'Gym & Fitness Equipment' },
  { id: 'nutrition', label: 'Sports Nutrition' },
  { id: 'accessories', label: 'Court Accessories' },
  { id: 'other', label: 'Something Else' },
]
const selectedOptions = ref<string[]>([])
const surveySent = ref(false)

function toggleOption(id: string) {
  const idx = selectedOptions.value.indexOf(id)
  if (idx >= 0) selectedOptions.value.splice(idx, 1)
  else selectedOptions.value.push(id)
}

function submitSurvey() {
  if (selectedOptions.value.length === 0) return
  // Persist locally so we don't spam them
  if (import.meta.client) {
    localStorage.setItem('bsa_shop_survey', JSON.stringify({ ts: Date.now(), picks: selectedOptions.value }))
  }
  surveySent.value = true
}

const config = useRuntimeConfig()
const route = useRoute()

// Route param is the URL slug (e.g. 'jackets') - map it to the DB enum key
const category = computed(() => (route.params.category as string) || null)
const categoryKey = computed(() =>
  category.value ? (CATEGORY_SLUGS[category.value.toLowerCase()] ?? null) : null
)

const pageTitle = computed(() =>
  categoryKey.value ? (CATEGORY_LABELS[categoryKey.value] ?? category.value ?? 'All Products') : 'All Products'
)

useHead({
  title: `${pageTitle.value} | Bhaisepati Sports Academy`,
})

const { data: products, status } = await useFetch<Product[]>(`${config.public.apiBase}/products`, {
  query: computed(() => ({
    category: categoryKey.value ?? undefined,
  })),
  default: () => [],
})
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-heading-xl">{{ pageTitle }}</h1>
    </div>

    <!-- Category filters -->
    <nav class="mb-8 flex gap-4 overflow-x-auto" aria-label="Product categories">
      <NuxtLink
        to="/shop"
        :class="[
          'text-label whitespace-nowrap pb-1 border-b-2 transition-colors',
          !category ? 'border-ink text-ink' : 'border-transparent text-ink-muted hover:text-ink',
        ]"
      >
        All
      </NuxtLink>
      <NuxtLink
        v-for="(label, key) in CATEGORY_LABELS"
        :key="key"
        :to="`/shop/${CATEGORY_SLUG_BY_KEY[key]}`"
        :class="[
          'text-label whitespace-nowrap pb-1 border-b-2 transition-colors',
          categoryKey === key ? 'border-ink text-ink' : 'border-transparent text-ink-muted hover:text-ink',
        ]"
      >
        {{ label }}
      </NuxtLink>
    </nav>

    <!-- Products grid -->
    <div v-if="status === 'pending'" class="grid grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
      <div v-for="i in 6" :key="i" class="space-y-3">
        <UiAppSkeleton class="aspect-[3/4] w-full" />
        <UiAppSkeleton class="h-4 w-3/4" />
        <UiAppSkeleton class="h-4 w-1/2" />
      </div>
    </div>

    <ProductGrid
      v-else-if="products && products.length > 0"
      :products="products"
      :columns="3"
    />

    <div v-else class="py-12">
      <!-- Survey: submitted state -->
      <div v-if="surveySent" class="flex flex-col items-center gap-6 text-center max-w-sm mx-auto">
        <CheckCircle class="h-12 w-12 text-accent" />
        <div>
          <p class="font-display text-2xl text-ink">Thanks for letting us know!</p>
          <p class="mt-2 text-ink-muted text-sm">We'll stock up on what you need. Message us on WhatsApp to get notified first.</p>
        </div>
        <a
          :href="`https://wa.me/${BRAND.whatsapp.replace(/\D/g, '')}?text=Hey%20BSA%2C%20please%20notify%20me%20when%20your%20shop%20is%20stocked!`"
          target="_blank"
          rel="noopener"
          class="inline-flex items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-semibold text-canvas transition hover:bg-accent/90"
        >
          Notify me on WhatsApp
        </a>
      </div>

      <!-- Survey: selection state -->
      <div v-else class="max-w-lg mx-auto">
        <div class="flex items-center gap-3 mb-6">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent/10">
            <ShoppingBag class="h-5 w-5 text-accent" />
          </div>
          <div>
            <p class="font-semibold text-ink">Our shop is being stocked.</p>
            <p class="text-xs text-ink-muted">Help us prioritise — what do you want to buy?</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <button
            v-for="opt in surveyOptions"
            :key="opt.id"
            type="button"
            @click="toggleOption(opt.id)"
            :class="[
              'rounded-xl border px-4 py-3 text-left text-sm transition-all',
              selectedOptions.includes(opt.id)
                ? 'border-accent bg-accent/10 text-accent font-medium'
                : 'border-white/10 bg-surface text-ink-muted hover:border-white/20 hover:text-ink',
            ]"
          >
            {{ opt.label }}
          </button>
        </div>

        <button
          type="button"
          @click="submitSurvey"
          :disabled="selectedOptions.length === 0"
          :class="[
            'mt-6 w-full rounded-xl py-3 text-sm font-semibold transition-all',
            selectedOptions.length > 0
              ? 'bg-accent text-canvas hover:bg-accent/90'
              : 'bg-white/5 text-ink-muted cursor-not-allowed',
          ]"
        >
          Submit ({{ selectedOptions.length }} selected)
        </button>
      </div>
    </div>
  </div>
</template>
