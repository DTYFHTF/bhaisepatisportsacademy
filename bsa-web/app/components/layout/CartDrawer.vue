<script setup lang="ts">
import { X, Minus, Plus, ShoppingBag } from 'lucide-vue-next'
import { formatPrice } from '~/utils/formatters'

const cart = useCartStore()
</script>

<template>
  <UiAppSheet :open="cart.isOpen" side="right" @close="cart.closeDrawer()">
    <div class="flex h-full flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-border px-4 py-4">
        <h2 class="text-heading-md">Cart</h2>
        <button
          class="p-1 text-ink-muted hover:text-ink"
          aria-label="Close cart"
          @click="cart.closeDrawer()"
        >
          <X class="h-5 w-5" />
        </button>
      </div>

      <!-- Empty state -->
      <div
        v-if="cart.isEmpty"
        class="flex flex-1 flex-col items-center justify-center px-4"
      >
        <ShoppingBag class="h-12 w-12 text-ink-faint mb-4" />
        <p class="text-ink-muted">Nothing here yet.</p>
        <NuxtLink
          to="/shop"
          class="mt-4 text-sm text-ink underline underline-offset-4"
          @click="cart.closeDrawer()"
        >
          Browse products
        </NuxtLink>
      </div>

      <!-- Items -->
      <div v-else class="flex-1 overflow-y-auto px-4 py-4">
        <div
          v-for="item in cart.items"
          :key="item.variantId"
          class="flex gap-4 py-4 border-b border-border last:border-0"
        >
          <!-- Image -->
          <div class="h-20 w-16 flex-shrink-0 bg-surface">
            <img
              v-if="item.image"
              :src="item.image"
              :alt="item.name"
              class="h-full w-full object-cover"
            />
          </div>

          <!-- Details -->
          <div class="flex flex-1 flex-col">
            <div class="flex justify-between">
              <div>
                <p class="text-sm font-medium">{{ item.name }}</p>
                <p class="text-xs text-ink-muted">{{ item.variantLabel }}</p>
              </div>
              <button
                class="p-1 text-ink-faint hover:text-ink"
                :aria-label="`Remove ${item.name}`"
                @click="cart.removeItem(item.variantId)"
              >
                <X class="h-4 w-4" />
              </button>
            </div>

            <div class="mt-auto flex items-center justify-between pt-2">
              <!-- Quantity -->
              <div class="flex items-center gap-2">
                <button
                  class="h-7 w-7 border border-border flex items-center justify-center hover:border-ink"
                  :aria-label="`Decrease quantity of ${item.name}`"
                  @click="cart.updateQuantity(item.variantId, item.quantity - 1)"
                >
                  <Minus class="h-3 w-3" />
                </button>
                <span class="w-6 text-center text-sm">{{ item.quantity }}</span>
                <button
                  class="h-7 w-7 border border-border flex items-center justify-center hover:border-ink"
                  :aria-label="`Increase quantity of ${item.name}`"
                  @click="cart.updateQuantity(item.variantId, item.quantity + 1)"
                >
                  <Plus class="h-3 w-3" />
                </button>
              </div>

              <p class="text-sm font-medium">{{ formatPrice(item.price * item.quantity) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div v-if="!cart.isEmpty" class="border-t border-border px-4 py-4">
        <div class="flex items-center justify-between mb-4">
          <span class="text-sm text-ink-muted">Subtotal</span>
          <span class="text-base font-medium">{{ formatPrice(cart.total) }}</span>
        </div>
        <NuxtLink to="/checkout" @click="cart.closeDrawer()">
          <UiAppButton variant="primary" size="lg" class="w-full">
            Checkout
          </UiAppButton>
        </NuxtLink>
      </div>
    </div>
  </UiAppSheet>
</template>
