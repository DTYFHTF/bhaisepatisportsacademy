<script setup lang="ts">
import type { Product } from '~/types/product'
import { formatPrice, getCloudinaryUrl, getCloudinarySrcSet } from '~/utils/formatters'

interface Props {
  product: Product
}

defineProps<Props>()

function getAvailableStock(product: Product): number {
  return product.variants.reduce((sum, v) => sum + Math.max(0, v.stock - v.reservedStock), 0)
}
</script>

<template>
  <NuxtLink
    :to="`/p/${product.slug}`"
    class="group block"
  >
    <!-- Image -->
    <div class="relative aspect-[3/4] overflow-hidden bg-surface">
      <img
        v-if="product.images[0]"
        :src="product.images[0].cloudinaryId
          ? getCloudinaryUrl(product.images[0].cloudinaryId, 800)
          : product.images[0].url"
        :srcset="product.images[0].cloudinaryId
          ? getCloudinarySrcSet(product.images[0].cloudinaryId)
          : undefined"
        sizes="(max-width: 480px) 50vw, (max-width: 1024px) 33vw, 25vw"
        :alt="product.images[0].altText || product.name"
        class="h-full w-full object-cover transition-transform duration-slow group-hover:scale-[1.02]"
        loading="lazy"
      />
      <!-- New badge -->
      <span
        v-if="product.tags.includes('new-arrival')"
        class="absolute top-3 left-3 border border-ink px-2 py-0.5 text-label bg-canvas"
      >
        New
      </span>
      <!-- Out of stock overlay -->
      <div
        v-if="getAvailableStock(product) === 0"
        class="absolute inset-0 flex items-center justify-center bg-canvas/60"
      >
        <span class="text-label text-ink-muted">Sold Out</span>
      </div>
    </div>

    <!-- Info -->
    <div class="mt-3">
      <p class="text-sm">{{ product.name }}</p>
      <p v-if="product.tagline" class="text-sm text-ink-muted">{{ product.tagline }}</p>
      <div class="mt-1 flex items-center gap-2">
        <span class="text-sm font-medium">{{ formatPrice(product.price) }}</span>
        <span
          v-if="product.compareAtPrice"
          class="text-sm text-ink-faint line-through"
        >
          {{ formatPrice(product.compareAtPrice) }}
        </span>
      </div>
    </div>
  </NuxtLink>
</template>
