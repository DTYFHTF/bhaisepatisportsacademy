<script setup lang="ts">
useHead({ title: 'Checkout | Bhaisepati Sports Academy' })

const checkout = useCheckoutStore()
const cart = useCartStore()
const router = useRouter()

// Redirect to shop if cart is empty
if (cart.isEmpty) {
  router.replace('/shop')
}
</script>

<template>
  <div class="mx-auto max-w-5xl px-4 py-8 lg:px-8">
    <!-- Progress indicator -->
    <div class="mb-8 flex items-center gap-4">
      <button
        v-for="(label, step) in ['Contact', 'Delivery', 'Payment']"
        :key="step"
        :class="[
          'text-label transition-colors',
          checkout.step >= step + 1 ? 'text-ink' : 'text-ink-faint',
        ]"
        :disabled="checkout.step < step + 1"
        @click="checkout.step >= step + 1 && checkout.goToStep((step + 1) as 1 | 2 | 3)"
      >
        {{ step + 1 }}. {{ label }}
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Steps -->
      <div class="lg:col-span-2">
        <CheckoutContactStep v-if="checkout.step === 1" />
        <CheckoutDeliveryStep v-else-if="checkout.step === 2" />
        <CheckoutPaymentStep v-else-if="checkout.step === 3" />
      </div>

      <!-- Order summary sidebar -->
      <div class="hidden lg:block">
        <CheckoutOrderSummary />
      </div>
    </div>
  </div>
</template>
