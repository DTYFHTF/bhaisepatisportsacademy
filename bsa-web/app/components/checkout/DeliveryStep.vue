<script setup lang="ts">
import { calculateDeliveryFee, formatPrice } from '~/utils/formatters'
import type { DeliveryLocation } from '~/composables/useGoogleMaps'

const checkout = useCheckoutStore()
const cart = useCartStore()

const form = reactive({
  fullName: checkout.delivery.fullName,
  address: checkout.delivery.address,
  city: checkout.delivery.city,
  deliveryNote: checkout.delivery.deliveryNote,
})

const pickedLocation = ref<DeliveryLocation | null>(
  checkout.delivery.lat
    ? {
        lat: checkout.delivery.lat,
        lng: checkout.delivery.lng!,
        formattedAddress: checkout.delivery.formattedAddress || '',
        city: checkout.delivery.city,
        district: '',
        landmark: checkout.delivery.landmark || '',
      }
    : null,
)

const errors = ref<Record<string, string>>({})

const deliveryFee = computed(() => calculateDeliveryFee(form.city, cart.total))

function onLocationUpdate(loc: DeliveryLocation) {
  pickedLocation.value = loc
  // Auto-fill address and city from map selection
  if (loc.formattedAddress) form.address = loc.formattedAddress
  if (loc.city) form.city = loc.city
}

function validate(): boolean {
  errors.value = {}
  if (!form.fullName.trim()) errors.value.fullName = 'Name is required'
  if (!form.address.trim()) errors.value.address = 'Address is required'
  if (!form.city.trim()) errors.value.city = 'City is required'
  return Object.keys(errors.value).length === 0
}

function handleSubmit() {
  if (!validate()) return
  checkout.setDelivery({
    fullName: form.fullName.trim(),
    address: form.address.trim(),
    city: form.city.trim(),
    deliveryNote: form.deliveryNote.trim(),
    lat: pickedLocation.value?.lat || null,
    lng: pickedLocation.value?.lng || null,
    formattedAddress: pickedLocation.value?.formattedAddress || '',
    landmark: pickedLocation.value?.landmark || '',
  })
}
</script>

<template>
  <div class="space-y-6">
    <h2 class="text-heading-lg">Delivery</h2>

    <UiAppInput
      v-model="form.fullName"
      label="Full Name"
      placeholder="Your full name"
      required
      :error="errors.fullName"
    />

    <!-- Location picker with map + GPS (first so it can auto-fill address & city) -->
    <CheckoutLocationPicker
      :location="pickedLocation"
      @update:location="onLocationUpdate"
    />

    <UiAppInput
      v-model="form.address"
      label="Address"
      placeholder="Ward 4, Lazimpat"
      required
      :error="errors.address"
    />

    <UiAppInput
      v-model="form.city"
      label="City"
      placeholder="Kathmandu"
      required
      :error="errors.city"
    />

    <UiAppInput
      v-model="form.deliveryNote"
      label="Delivery Note (optional)"
      placeholder="Any special instructions"
    />

    <!-- Delivery fee display -->
    <div v-if="form.city" class="rounded bg-surface p-4 text-sm">
      <p v-if="deliveryFee === 0" class="text-success">Free delivery on orders above NPR 5,000</p>
      <p v-else class="text-ink-muted">
        Delivery to {{ form.city }}: {{ formatPrice(deliveryFee) }}
      </p>
    </div>

    <div class="flex gap-3">
      <UiAppButton variant="ghost" @click="checkout.goToStep(1)">
        Back
      </UiAppButton>
      <UiAppButton class="flex-1" @click="handleSubmit">
        Continue to Payment
      </UiAppButton>
    </div>
  </div>
</template>
