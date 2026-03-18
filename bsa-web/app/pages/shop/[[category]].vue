<script setup lang="ts">
import type { Product } from '~/types/product'
import { CATEGORY_LABELS, CATEGORY_SLUGS, CATEGORY_SLUG_BY_KEY } from '~/utils/constants'

const config = useRuntimeConfig()
const route = useRoute()

// Route param is the URL slug (e.g. 'jackets') — map it to the DB enum key
const category = computed(() => (route.params.category as string) || null)
const categoryKey = computed(() =>
  category.value ? (CATEGORY_SLUGS[category.value.toLowerCase()] ?? null) : null
)

const pageTitle = computed(() =>
  categoryKey.value ? (CATEGORY_LABELS[categoryKey.value] ?? category.value ?? 'All Products') : 'All Products'
)

useHead({
  title: `${pageTitle.value} — Bhaisepati Sports Academy`,
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

    <div v-else class="py-16 text-center">
      <p class="text-ink-muted">No products found.</p>
      <NuxtLink to="/shop" class="mt-4 inline-block text-sm underline underline-offset-4">
        View all products
      </NuxtLink>
    </div>
  </div>
</template>
