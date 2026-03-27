<script setup lang="ts">
import type { Product } from '~/types/product'
import { formatPrice, getCloudinaryUrl } from '~/utils/formatters'
import { useRecentlyViewed } from '~/composables/useRecentlyViewed'

const config = useRuntimeConfig()
const route = useRoute()
const cart = useCartStore()
const { add: addToRecentlyViewed } = useRecentlyViewed()

const slug = computed(() => route.params.slug as string)

const { data: product, status } = await useFetch<Product>(`${config.public.apiBase}/products/${slug.value}`)

// Track recently viewed
watch(product, (p) => {
  if (p) addToRecentlyViewed(p)
}, { immediate: true })

// SEO
useHead(() => ({
  title: product.value ? `${product.value.name} | Bhaisepati Sports Academy` : 'Product | Bhaisepati Sports Academy',
  meta: product.value
    ? [{ name: 'description', content: product.value.description }]
    : [],
}))

// Variant selection
const selectedVariantId = ref<string | null>(null)

const selectedVariant = computed(() => {
  if (!product.value || !selectedVariantId.value) return null
  return product.value.variants.find((v) => v.id === selectedVariantId.value)
})

// Auto-select first variant if only one
watch(product, (p) => {
  if (p && p.variants.length === 1) {
    selectedVariantId.value = p.variants[0].id
  }
}, { immediate: true })

// Product detail sections
const detailSections = computed(() => {
  if (!product.value) return []
  const sections = []
  const details = product.value.productDetails
  if (details?.ingredients) {
    sections.push({ title: 'Ingredients', content: details.ingredients })
  }
  if (details?.howToUse) {
    sections.push({ title: 'How to Use', content: details.howToUse })
  }
  if (details?.suitableFor) {
    sections.push({ title: 'Suitable For', content: details.suitableFor })
  }
  sections.push({
    title: 'Delivery',
    content: '<p>Kathmandu Valley: NPR 100, 1–2 days</p><p>Other cities: NPR 150, 3–5 days</p><p>Free delivery on orders above NPR 5,000</p>',
  })
  return sections
})

function addToCart() {
  if (!product.value || !selectedVariant.value) return

  const img = product.value.images[0]
  const imageUrl = img?.cloudinaryId
    ? getCloudinaryUrl(img.cloudinaryId, 400)
    : img?.url || ''

  cart.addItem({
    productId: product.value.id,
    slug: product.value.slug,
    name: product.value.name,
    variantLabel: selectedVariant.value.label,
    variantId: selectedVariant.value.id,
    price: selectedVariant.value.priceOverride ?? product.value.price,
    image: imageUrl,
  })
}
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
    <!-- Loading -->
    <div v-if="status === 'pending'" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <UiAppSkeleton class="aspect-[4/5] w-full" />
      <div class="space-y-4">
        <UiAppSkeleton class="h-8 w-3/4" />
        <UiAppSkeleton class="h-6 w-1/4" />
        <UiAppSkeleton class="h-12 w-full" />
      </div>
    </div>

    <!-- Product -->
    <div v-else-if="product" class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
      <!-- Gallery -->
      <ProductGallery :images="product.images" :product-name="product.name" />

      <!-- Details -->
      <div class="space-y-6">
        <div>
          <h1 class="text-heading-xl">{{ product.name }}</h1>
          <p v-if="product.tagline" class="text-ink-muted">{{ product.tagline }}</p>
        </div>

        <!-- Price -->
        <div class="flex items-center gap-3">
          <span class="text-xl font-medium">{{ formatPrice(product.price) }}</span>
          <span v-if="product.compareAtPrice" class="text-ink-faint line-through">
            {{ formatPrice(product.compareAtPrice) }}
          </span>
        </div>

        <!-- Description -->
        <p class="text-sm text-ink-muted leading-relaxed">{{ product.description }}</p>

        <!-- Variant selector -->
        <div v-if="product.variants.length > 1" class="space-y-2">
          <p class="text-label">Size / Volume</p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="v in product.variants"
              :key="v.id"
              class="rounded-lg border px-4 py-2 text-sm transition-colors"
              :class="selectedVariantId === v.id ? 'border-accent bg-accent/10 text-ink' : 'border-border text-ink-muted hover:border-ink'"
              :disabled="v.stock - v.reservedStock <= 0"
              @click="selectedVariantId = v.id"
            >
              {{ v.label }}
              <span v-if="v.priceOverride" class="ml-1 text-xs text-ink-faint">({{ formatPrice(v.priceOverride) }})</span>
            </button>
          </div>
        </div>

        <!-- Add to cart -->
        <UiAppButton
          size="lg"
          class="w-full"
          :disabled="!selectedVariant || (selectedVariant.stock - selectedVariant.reservedStock <= 0)"
          @click="addToCart"
        >
          {{ selectedVariant ? 'Add to Cart' : 'Select an Option' }}
        </UiAppButton>

        <!-- Detail sections accordion -->
        <ProductFabricStory v-if="detailSections.length" :sections="detailSections" />
      </div>
    </div>

    <!-- Related products -->
    <div v-if="product?.pairedWith?.length" class="mt-16">
      <ProductRelatedProducts :products="product.pairedWith" title="You Might Also Like" />
    </div>
  </div>
</template>
