<script setup lang="ts">
import { Check, PartyPopper } from 'lucide-vue-next'

const route = useRoute()
const cart = useCartStore()
const checkout = useCheckoutStore()
const orderId = computed(() => route.query.id as string)
const paymentMethod = computed(() => (route.query.payment as string) || 'COD')

// For gateway payments (eSewa/Khalti), the cart is cleared here after redirect back
onMounted(() => {
  const pm = paymentMethod.value
  if (pm === 'ESEWA' || pm === 'KHALTI') {
    cart.clearCart()
    checkout.reset()
  }
})

const isCod = computed(() => paymentMethod.value === 'COD')
const isPaid = computed(() => paymentMethod.value === 'ESEWA')

useHead({ title: 'Order Confirmed | Bhaisepati Sports Academy' })
</script>

<template>
  <div class="mx-auto max-w-md px-4 py-16 text-center">
    <div class="mb-6 flex justify-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-full bg-success/10">
        <Check class="h-8 w-8 text-success" />
      </div>
    </div>

    <h1 class="text-heading-xl">Order Placed</h1>
    <p v-if="orderId" class="mt-2 font-mono text-lg text-ink-muted">{{ orderId }}</p>

    <!-- Payment-specific message -->
    <div v-if="isPaid" class="mt-4">
      <span class="inline-flex items-center gap-1.5 rounded-full bg-success/10 px-3 py-1 text-sm font-medium text-success">
        <Check class="h-3.5 w-3.5" /> Payment Confirmed
      </span>
      <p class="mt-3 text-ink-muted">Your eSewa payment went through. We're on it!</p>
    </div>
    <div v-else class="mt-4">
      <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-sm font-medium text-amber-700">
        💵 Pay on Delivery
      </span>
      <p class="mt-3 text-ink-muted">Keep the cash ready. Our rider is counting on you!</p>
    </div>

    <p class="mt-3 text-sm text-ink-muted">Confirmation SMS sent to your phone.</p>
    <p class="mt-1 text-xs text-ink-faint">Didn't receive it? Don't worry | your order is safe. Use the order ID above to track anytime.</p>

    <div class="mt-8 flex flex-col gap-3">
      <NuxtLink :to="orderId ? `/track?order=${orderId}` : '/track'">
        <UiAppButton variant="primary" class="w-full">
          Track Your Order
        </UiAppButton>
      </NuxtLink>
      <NuxtLink to="/shop">
        <UiAppButton variant="ghost" class="w-full">
          Continue Shopping
        </UiAppButton>
      </NuxtLink>
    </div>
  </div>
</template>
