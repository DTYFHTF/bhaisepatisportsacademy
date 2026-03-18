<script setup lang="ts">
import type { OrderItem } from '~/types/order'
import { formatPrice } from '~/utils/formatters'

import { MapPin } from 'lucide-vue-next'

interface Props {
  items: OrderItem[]
  subtotal: number
  deliveryFee: number
  total: number
  paymentMethod: string
  customerName: string
  address: string
  city: string
  formattedAddress?: string | null
  latitude?: number | null
  longitude?: number | null
  nearestLandmark?: string | null
}

defineProps<Props>()
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-label">Items in this Order</h3>

    <div class="space-y-3">
      <div
        v-for="item in items"
        :key="item.id"
        class="flex gap-3"
      >
        <div class="h-16 w-12 flex-shrink-0 bg-surface">
          <img
            v-if="item.product?.images?.[0]?.url"
            :src="item.product.images[0].url"
            :alt="item.product?.name || 'Product'"
            class="h-full w-full object-cover"
          />
        </div>
        <div class="flex-1 text-sm">
          <p>{{ item.product?.name }} · {{ item.product?.colorName }} · {{ item.variant?.size }}</p>
          <p class="text-ink-muted">Qty: {{ item.quantity }}</p>
        </div>
        <p class="text-sm">{{ formatPrice(item.unitPrice * item.quantity) }}</p>
      </div>
    </div>

    <div class="border-t border-border pt-3 space-y-1 text-sm">
      <div class="flex justify-between text-ink-muted">
        <span>Subtotal</span>
        <span>{{ formatPrice(subtotal) }}</span>
      </div>
      <div class="flex justify-between text-ink-muted">
        <span>Delivery</span>
        <span>{{ deliveryFee === 0 ? 'Free' : formatPrice(deliveryFee) }}</span>
      </div>
      <div class="flex justify-between font-medium pt-1">
        <span>Total</span>
        <span>{{ formatPrice(total) }}</span>
      </div>
      <p class="text-ink-muted">Payment: {{ paymentMethod }} ✓</p>
    </div>

    <div class="border-t border-border pt-3 text-sm text-ink-muted space-y-0.5">
      <p class="font-medium text-ink">Deliver to: {{ customerName }}</p>
      <template v-if="formattedAddress">
        <a
          v-if="latitude && longitude"
          :href="`https://maps.google.com/?q=${latitude},${longitude}`"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-1 text-accent hover:underline"
        >
          <MapPin class="h-3 w-3" />
          {{ formattedAddress }}
        </a>
        <p v-else>
          <MapPin class="mr-0.5 inline h-3 w-3" />
          {{ formattedAddress }}
        </p>
      </template>
      <p v-else>{{ address }}, {{ city }}</p>
      <p v-if="nearestLandmark" class="text-xs">Near {{ nearestLandmark }}</p>
    </div>
  </div>
</template>
