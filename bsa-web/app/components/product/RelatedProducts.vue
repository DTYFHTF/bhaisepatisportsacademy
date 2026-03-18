<script setup lang="ts">
import type { Product } from '~/types/product'

interface Props {
  products: Product[]
  title?: string
}

withDefaults(defineProps<Props>(), {
  title: 'Complete the Look',
})
</script>

<template>
  <section v-if="products.length > 0">
    <h2 class="text-heading-lg mb-6">{{ title }}</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <ProductCard
        v-for="product in products"
        :key="product.id"
        :product="product"
      />
    </div>
  </section>
</template>
