<script setup lang="ts">
import { ShoppingBag, CheckCircle } from 'lucide-vue-next'
import type { Product } from '~/types/product'
import { BRAND, CATEGORY_LABELS, CATEGORY_SLUGS, CATEGORY_SLUG_BY_KEY, IMAGES } from '~/utils/constants'

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

const { data: apiProducts, status } = await useFetch<Product[]>(`${config.public.apiBase}/products`, {
  query: computed(() => ({
    category: categoryKey.value ?? undefined,
  })),
  default: () => [],
})

// Static fallback products when the API is unreachable
const fallbackProducts: Product[] = [
  {
    id: 'fallback-1', slug: 'badminton-racket-pro', name: 'Pro Badminton Racket',
    tagline: 'Carbon fiber, lightweight frame', price: 450000, compareAtPrice: 550000,
    category: 'EQUIPMENT', description: 'Professional-grade badminton racket with carbon fiber frame.',
    tags: ['new-arrival'], isActive: true, variants: [
      { id: 'v-f1', productId: 'fallback-1', label: 'Standard', sku: 'RKT-PRO-STD', stock: 10, reservedStock: 0 },
    ],
    images: [{ id: 'i-f1', productId: 'fallback-1', cloudinaryId: '', url: IMAGES.badmintonCourt, altText: 'Pro Badminton Racket', order: 0 }],
    createdAt: '', updatedAt: '',
  },
  {
    id: 'fallback-2', slug: 'shuttlecock-tube', name: 'Premium Shuttlecocks (Tube of 6)',
    tagline: 'Tournament-grade feather shuttles', price: 85000,
    category: 'EQUIPMENT', description: 'High-quality feather shuttlecocks for competitive play.',
    tags: [], isActive: true, variants: [
      { id: 'v-f2', productId: 'fallback-2', label: 'Tube of 6', sku: 'SHUT-T6', stock: 25, reservedStock: 0 },
    ],
    images: [{ id: 'i-f2', productId: 'fallback-2', cloudinaryId: '', url: IMAGES.badmintonPlayer, altText: 'Shuttlecocks', order: 0 }],
    createdAt: '', updatedAt: '',
  },
  {
    id: 'fallback-3', slug: 'sports-t-shirt', name: 'BSA Training Tee',
    tagline: 'Moisture-wicking, breathable fabric', price: 120000,
    category: 'APPAREL', description: 'Official BSA training t-shirt. Comfortable and durable.',
    tags: [], isActive: true, variants: [
      { id: 'v-f3', productId: 'fallback-3', label: 'Small', sku: 'TEE-S', stock: 15, reservedStock: 0 },
      { id: 'v-f4', productId: 'fallback-3', label: 'Medium', sku: 'TEE-M', stock: 20, reservedStock: 0 },
      { id: 'v-f5', productId: 'fallback-3', label: 'Large', sku: 'TEE-L', stock: 18, reservedStock: 0 },
    ],
    images: [{ id: 'i-f3', productId: 'fallback-3', cloudinaryId: '', url: IMAGES.teamSport, altText: 'BSA Training Tee', order: 0 }],
    createdAt: '', updatedAt: '',
  },
  {
    id: 'fallback-4', slug: 'gym-gloves', name: 'Grip Pro Gym Gloves',
    tagline: 'Anti-slip silicone palm', price: 65000,
    category: 'APPAREL', description: 'Durable gym gloves with anti-slip silicone palm padding.',
    tags: [], isActive: true, variants: [
      { id: 'v-f6', productId: 'fallback-4', label: 'M', sku: 'GLOV-M', stock: 12, reservedStock: 0 },
      { id: 'v-f7', productId: 'fallback-4', label: 'L', sku: 'GLOV-L', stock: 10, reservedStock: 0 },
    ],
    images: [{ id: 'i-f4', productId: 'fallback-4', cloudinaryId: '', url: IMAGES.gymTraining, altText: 'Gym Gloves', order: 0 }],
    createdAt: '', updatedAt: '',
  },
  {
    id: 'fallback-5', slug: 'protein-powder', name: 'Whey Protein Isolate (1kg)',
    tagline: '25g protein per serving, chocolate', price: 350000,
    category: 'NUTRITION', description: 'Premium whey protein isolate for post-workout recovery.',
    tags: [], isActive: true, variants: [
      { id: 'v-f8', productId: 'fallback-5', label: '1kg', sku: 'PROT-1KG', stock: 8, reservedStock: 0 },
    ],
    images: [{ id: 'i-f5', productId: 'fallback-5', cloudinaryId: '', url: IMAGES.food, altText: 'Protein Powder', order: 0 }],
    createdAt: '', updatedAt: '',
  },
  {
    id: 'fallback-6', slug: 'energy-drink', name: 'BSA Energy Drink (500ml)',
    tagline: 'Electrolytes + natural caffeine', price: 25000,
    category: 'NUTRITION', description: 'Refreshing energy drink with electrolytes and natural caffeine.',
    tags: [], isActive: true, variants: [
      { id: 'v-f9', productId: 'fallback-6', label: '500ml', sku: 'ENRG-500', stock: 30, reservedStock: 0 },
    ],
    images: [{ id: 'i-f6', productId: 'fallback-6', cloudinaryId: '', url: IMAGES.smoothie, altText: 'Energy Drink', order: 0 }],
    createdAt: '', updatedAt: '',
  },
]

const products = computed(() => {
  if (apiProducts.value && apiProducts.value.length > 0) {
    return apiProducts.value
  }
  // Fallback to static data when the API is unreachable
  if (categoryKey.value) {
    return fallbackProducts.filter((p) => p.category === categoryKey.value)
  }
  return fallbackProducts
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
          categoryKey === (key as string) ? 'border-ink text-ink' : 'border-transparent text-ink-muted hover:text-ink',
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
