<script setup lang="ts">
import { formatPrice, calculateDeliveryFee } from '~/utils/formatters'
import { COD_LIMIT } from '~/utils/constants'
import type { PaymentMethod } from '~/types/order'

const cart = useCartStore()
const checkout = useCheckoutStore()
const config = useRuntimeConfig()
const router = useRouter()

const loading = ref(false)
const error = ref<string | null>(null)

const deliveryFee = computed(() => calculateDeliveryFee(checkout.delivery.city, cart.total))
const orderTotal = computed(() => cart.total + deliveryFee.value)
const codDisabled = computed(() => orderTotal.value > COD_LIMIT)

const paymentMethods: { value: PaymentMethod; label: string; icon: string }[] = [
  { value: 'ESEWA', label: 'eSewa', icon: '💚' },
  { value: 'COD', label: 'Cash on Delivery', icon: '💵' },
]

async function placeOrder() {
  loading.value = true
  error.value = null

  try {
    const body = {
      phone: checkout.phone,
      name: checkout.delivery.fullName,
      address: checkout.delivery.address,
      city: checkout.delivery.city,
      delivery_note: checkout.delivery.deliveryNote || null,
      latitude: checkout.delivery.lat,
      longitude: checkout.delivery.lng,
      formatted_address: checkout.delivery.formattedAddress || null,
      nearest_landmark: checkout.delivery.landmark || null,
      email: checkout.email || null,
      payment_method: checkout.paymentMethod,
      items: cart.items.map((item) => ({
        product_id: item.productId,
        variant_id: item.variantId,
        quantity: item.quantity,
        price: item.price,
      })),
    }

    const response = await $fetch<{ orderId: string; paymentUrl?: string }>(`${config.public.apiBase}/checkout/place`, {
      method: 'POST',
      body,
      headers: checkout.token ? { Authorization: `Bearer ${checkout.token}` } : {},
    })

    if (response.paymentUrl) {
      window.location.href = response.paymentUrl
    } else {
      cart.clearCart()
      const pm = checkout.paymentMethod
      checkout.reset()
      router.push(`/order/confirmed?id=${response.orderId}&payment=${pm}`)
    }
  } catch (e: any) {
    const errData = e?.data
    if (errData?.error === 'VALIDATION_ERROR' && errData?.fields) {
      const firstField = Object.values(errData.fields)[0]
      error.value = (Array.isArray(firstField) ? firstField[0] : firstField) as string
    } else {
      error.value = errData?.message || 'Something went wrong. Your cart is safe. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <h2 class="text-heading-lg">Payment</h2>

    <!-- Order summary -->
    <div class="space-y-3 border-b border-border pb-4">
      <div
        v-for="item in cart.items"
        :key="item.variantId"
        class="flex justify-between text-sm"
      >
        <span>{{ item.name }} ({{ item.size }}) × {{ item.quantity }}</span>
        <span>{{ formatPrice(item.price * item.quantity) }}</span>
      </div>
      <div class="flex justify-between text-sm text-ink-muted">
        <span>Subtotal</span>
        <span>{{ formatPrice(cart.total) }}</span>
      </div>
      <div class="flex justify-between text-sm text-ink-muted">
        <span>Delivery</span>
        <span>{{ deliveryFee === 0 ? 'Free' : formatPrice(deliveryFee) }}</span>
      </div>
      <div class="flex justify-between text-base font-medium pt-2 border-t border-border">
        <span>Total</span>
        <span>{{ formatPrice(orderTotal) }}</span>
      </div>
    </div>

    <!-- Payment method -->
    <div class="space-y-3">
      <span class="text-label text-ink-muted">Payment Method</span>
      <div
        v-for="method in paymentMethods"
        :key="method.value"
        :class="[
          'flex items-center gap-3 border p-3 cursor-pointer transition-colors',
          checkout.paymentMethod === method.value ? 'border-ink' : 'border-border',
          method.value === 'COD' && codDisabled && 'opacity-50 cursor-not-allowed',
        ]"
        @click="!codDisabled || method.value !== 'COD' ? checkout.setPaymentMethod(method.value) : null"
      >
        <span>{{ method.icon }}</span>
        <span class="text-sm">{{ method.label }}</span>
        <span v-if="method.value === 'COD' && codDisabled" class="text-xs text-ink-muted ml-auto">
          Max NPR {{ (COD_LIMIT / 100).toLocaleString() }}
        </span>
      </div>
    </div>

    <!-- Error -->
    <p v-if="error" class="text-sm text-error" role="alert">{{ error }}</p>

    <!-- Actions -->
    <div class="flex gap-3">
      <UiAppButton variant="ghost" @click="checkout.goToStep(2)">
        Back
      </UiAppButton>
      <UiAppButton
        class="flex-1"
        :loading="loading"
        @click="placeOrder"
      >
        Place Order
      </UiAppButton>
    </div>
  </div>
</template>
