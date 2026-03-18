<script setup lang="ts">
import { formatPrice } from '~/utils/formatters'

const cart = useCartStore()
</script>

<template>
  <div class="border border-border p-4">
    <h3 class="text-label mb-4">Order Summary</h3>

    <div class="space-y-3">
      <div
        v-for="item in cart.items"
        :key="item.variantId"
        class="flex gap-3"
      >
        <div class="h-20 w-16 flex-shrink-0 bg-surface overflow-hidden">
          <img
            v-if="item.image"
            :src="item.image"
            :alt="item.name"
            class="h-full w-full object-cover"
          />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium truncate">{{ item.name }}</p>
          <p class="text-xs text-ink-muted">{{ item.colorName }} · {{ item.size }}</p>
          <p class="text-xs text-ink-muted">Qty: {{ item.quantity }}</p>
        </div>
        <p class="text-sm font-medium whitespace-nowrap">{{ formatPrice(item.price * item.quantity) }}</p>
      </div>
    </div>

    <div class="mt-4 border-t border-border pt-3 text-sm">
      <div class="flex justify-between text-ink-muted">
        <span>{{ cart.itemCount }} item{{ cart.itemCount !== 1 ? 's' : '' }}</span>
        <span>{{ formatPrice(cart.total) }}</span>
      </div>
    </div>
  </div>
</template>
