<script setup lang="ts">
import type { Product } from '~/types/product'

useHead({ title: 'Wardrobe Builder — Bhaisepati Sports Academy' })

const config = useRuntimeConfig()
const { data: products } = await useAsyncData<Product[]>(
  'wardrobe-products',
  () => $fetch<Product[]>(`${config.public.apiBase}/products?limit=50`),
  { default: () => [] as Product[] },
)
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
    <div class="mb-8 max-w-xl">
      <h1 class="text-heading-xl">Wardrobe Builder</h1>
      <p class="mt-2 text-ink-muted">
        Pick an anchor piece, and we'll suggest what goes with it.
        No algorithms — just colour theory and fabric compatibility.
      </p>
    </div>

    <AiWardrobeBuilder :products="products ?? []" />
  </div>
</template>
