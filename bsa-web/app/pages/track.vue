<script setup lang="ts">
import type { Order } from '~/types/order'
import { ORDER_STATUS_LABELS } from '~/types/order'

useHead({ title: 'Track Order | Bhaisepati Sports Academy' })

const config = useRuntimeConfig()
const route = useRoute()

const phone = ref('')
const orderIdInput = ref('')
const otpSent = ref(false)
const loading = ref(false)
const error = ref<string | null>(null)
const order = ref<Order | null>(null)

const { sending, cooldown, error: otpError, sendOtp, verifyOtp } = useOTP()

// WhatsApp support link
const { settings } = useSettings()
const whatsappUrl = computed(() => {
  const num = (settings.value.whatsappNumber || settings.value.contactPhone || '').replace(/\D/g, '')
  if (!num) return null
  const orderId = order.value?.orderId || orderIdInput.value
  const userPhone = phone.value
  const msg = orderId
    ? `Hi Bhaisepati Sports Academy, I need help with my order ${orderId}.${userPhone ? ` Phone: ${userPhone}` : ''}`
    : `Hi Bhaisepati Sports Academy, I need help tracking my order.${userPhone ? ` Phone: ${userPhone}` : ''}`
  return `https://wa.me/977${num}?text=${encodeURIComponent(msg)}`
})

// Check for JWT token in query
const jwtToken = computed(() => route.query.jwt as string || route.query.token as string)

// Auto-load order from JWT
if (jwtToken.value) {
  loading.value = true
  $fetch<Order>(`${config.public.apiBase}/orders/track`, {
    headers: { Authorization: `Bearer ${jwtToken.value}` },
  })
    .then((data) => { order.value = data })
    .catch(() => { error.value = 'Invalid or expired tracking link.' })
    .finally(() => { loading.value = false })
}

async function handleSendOtp() {
  if (!phone.value) return
  await sendOtp(phone.value)
  if (!otpError.value) otpSent.value = true
}

async function handleVerifyOtp(code: string) {
  const token = await verifyOtp(phone.value, code)
  if (!token) return
  await lookupOrder(token)
}

async function handleDirectLookup() {
  await lookupOrder()
}

async function lookupOrder(token?: string) {
  loading.value = true
  error.value = null
  try {
    const headers: Record<string, string> = {}
    if (token) headers.Authorization = `Bearer ${token}`
    const body: Record<string, string> = {}
    if (phone.value) body.phone = phone.value
    if (orderIdInput.value) body.order_id = orderIdInput.value
    const data = await $fetch<Order>(`${config.public.apiBase}/orders/lookup`, {
      method: 'POST',
      body,
      headers,
    })
    order.value = data
  } catch (e: any) {
    error.value = e?.data?.message || 'Order not found.'
  } finally {
    loading.value = false
  }
}

// Poll for updates every 30s
let pollInterval: ReturnType<typeof setInterval>
onMounted(() => {
  pollInterval = setInterval(async () => {
    if (!order.value || !jwtToken.value) return
    try {
      const data = await $fetch<Order>(`${config.public.apiBase}/orders/track`, {
        headers: { Authorization: `Bearer ${jwtToken.value}` },
      })
      order.value = data
    } catch { /* silent */ }
  }, 30000)
})
onUnmounted(() => clearInterval(pollInterval))
</script>

<template>
  <div class="mx-auto max-w-2xl px-4 py-8 lg:px-8">
    <h1 class="text-heading-xl mb-8">Track Your Order</h1>

    <!-- Order found: show timeline -->
    <template v-if="order">
      <div class="mb-6">
        <p class="font-mono text-lg">{{ order.orderId }}</p>
        <UiAppBadge
          :label="ORDER_STATUS_LABELS[order.status]"
          :variant="order.status === 'DELIVERED' ? 'success' : order.status === 'CANCELLED' ? 'error' : 'default'"
        />
      </div>

      <TrackingOrderTimeline
        :history="order.statusHistory"
        :current-status="order.status"
        :courier="order.courier"
        :courier-tracking-id="order.courierTrackingId"
        :courier-tracking-url="order.courierTrackingUrl"
      />

      <!-- Delivery map (shown when GPS coordinates are present) -->
      <div v-if="order.latitude && order.longitude" class="mt-6">
        <TrackingDeliveryMap
          :lat="order.latitude"
          :lng="order.longitude"
          :status="order.status"
        />
      </div>

      <div class="mt-8">
        <TrackingOrderItems
          :items="order.items"
          :subtotal="order.subtotal"
          :delivery-fee="order.deliveryFee"
          :total="order.total"
          :payment-method="order.paymentMethod"
          :customer-name="order.customerName"
          :address="order.address"
          :city="order.city"
          :formatted-address="order.formattedAddress"
          :latitude="order.latitude"
          :longitude="order.longitude"
          :nearest-landmark="order.nearestLandmark"
        />
      </div>

      <!-- Exchange option (7 days post-delivery) -->
      <div v-if="order.status === 'DELIVERED' && !order.exchangeRequested" class="mt-8 border-t border-border pt-6">
        <TrackingExchangeForm @submit="(data) => { /* TODO: submit exchange */ }" />
      </div>

      <!-- Support footer -->
      <div class="mt-8 border-t border-border pt-6 flex flex-wrap items-center gap-4 text-sm text-ink-muted">
        <span>Need help?</span>
        <a
          v-if="whatsappUrl"
          :href="whatsappUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-1.5 text-[#25D366] hover:underline"
        >
          <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          Chat on WhatsApp
        </a>
        <a
          v-if="settings.contactPhone"
          :href="`tel:${settings.contactPhone}`"
          class="hover:underline"
        >
          Call {{ settings.contactPhone }}
        </a>
      </div>
    </template>

    <!-- Loading -->
    <div v-else-if="loading" class="py-12 text-center">
      <UiAppSpinner size="md" />
      <p class="mt-4 text-ink-muted">Looking up your order...</p>
    </div>

    <!-- Manual lookup form -->
    <template v-else-if="!jwtToken">
      <div class="space-y-6">
        <UiAppInput
          v-model="phone"
          label="Phone Number"
          placeholder="98XXXXXXXX"
          type="tel"
        />

        <UiAppInput
          v-model="orderIdInput"
          label="Order ID"
          placeholder="PP-2601-4821"
        />

        <p class="text-xs text-ink-muted -mt-3">Enter either your phone number, order ID, or both.</p>

        <!-- Direct lookup (OTP bypassed for testing) -->
        <UiAppButton
          :loading="loading"
          :disabled="(!phone || phone.length < 10) && !orderIdInput"
          class="w-full"
          @click="handleDirectLookup"
        >
          Track Order
        </UiAppButton>

        <template v-if="!otpSent">
          <UiAppButton
            :loading="sending"
            :disabled="!phone || phone.length < 10 || cooldown > 0"
            variant="ghost"
            class="w-full"
            @click="handleSendOtp"
          >
            {{ cooldown > 0 ? `Resend in ${cooldown}s` : 'Verify via OTP instead' }}
          </UiAppButton>
        </template>

        <template v-else>
          <p class="text-sm text-ink-muted">OTP sent to {{ phone.slice(0, 2) }}XXXXXX{{ phone.slice(-2) }}</p>
          <UiOTPInput @complete="handleVerifyOtp" />
        </template>

        <!-- Error with WhatsApp support -->
        <div v-if="error || otpError" class="rounded-lg border border-error/20 bg-error/5 p-4 text-center space-y-3" role="alert">
          <p class="text-sm text-error font-medium">{{ error || otpError }}</p>
          <p class="text-xs text-ink-muted">If you believe this is a mistake, reach out to us:</p>
          <div class="flex flex-wrap justify-center gap-3">
            <a
              v-if="whatsappUrl"
              :href="whatsappUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-2 rounded-lg bg-[#25D366] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#1da851]"
            >
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              Chat on WhatsApp
            </a>
            <a
              v-if="settings.contactPhone"
              :href="`tel:${settings.contactPhone}`"
              class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-sm font-medium text-ink transition hover:bg-surface"
            >
              Call {{ settings.contactPhone }}
            </a>
          </div>
        </div>
      </div>
    </template>

    <!-- JWT error -->
    <div v-else-if="error" class="py-12 text-center space-y-4">
      <p class="text-error">{{ error }}</p>
      <div class="space-y-2">
        <NuxtLink to="/track" class="inline-block text-sm underline">
          Try manual lookup
        </NuxtLink>
        <div>
          <a
            :href="whatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 text-sm text-ink-muted hover:text-ink transition"
          >
            or contact support on WhatsApp
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
