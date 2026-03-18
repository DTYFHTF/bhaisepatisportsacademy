<script setup lang="ts">
import type { Product } from '~/types/product'

interface Props {
  products: Product[]
  columns?: 2 | 3 | 4
}

withDefaults(defineProps<Props>(), {
  columns: 3,
})

const gridClasses = {
  2: 'grid-cols-2',
  3: 'grid-cols-2 lg:grid-cols-3',
  4: 'grid-cols-2 md:grid-cols-3 xl:grid-cols-4',
}
</script>

<template>
  <div :class="['grid gap-4 md:gap-6', gridClasses[columns]]">
    <ProductCard
      v-for="product in products"
      :key="product.id"
      :product="product"
    />
  </div>
</template>
