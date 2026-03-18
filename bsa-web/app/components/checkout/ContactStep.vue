<script setup lang="ts">
const checkout = useCheckoutStore()
const { sending, cooldown, resendCount, error: otpSendError, devOtp, sendOtp, verifyOtp } = useOTP()

const phone = ref('')
const email = ref(checkout.email || '')
const otpError = ref<string | null>(null)
const sendError = ref<string | null>(null)
const phoneError = ref<string | null>(null)

async function handleSendOtp() {
  if (!phone.value || phone.value.length < 10) return
  phoneError.value = null
  sendError.value = null
  checkout.setPhone(phone.value)
  await sendOtp(phone.value)
  if (otpSendError.value) {
    sendError.value = otpSendError.value
  }
}

async function handleVerifyOtp(code: string) {
  otpError.value = null
  const token = await verifyOtp(phone.value, code)
  if (token) {
    checkout.setEmail(email.value)
    checkout.verifyOtp(token)
  } else {
    otpError.value = otpSendError.value
  }
}

function handleSkip() {
  if (!phone.value || phone.value.length < 10) {
    phoneError.value = 'Please enter your phone number — we need it for delivery updates.'
    return
  }
  checkout.setPhone(phone.value)
  checkout.setEmail(email.value)
  checkout.skipOtp()
}
</script>

<template>
  <div class="space-y-6">
    <h2 class="text-heading-lg">Contact</h2>

    <!-- Phone input -->
    <div>
      <UiAppInput
        v-model="phone"
        label="Phone Number"
        placeholder="98XXXXXXXX"
        type="tel"
        required
        :disabled="sending"
      />
      <p v-if="phoneError" class="mt-1 text-sm text-error">{{ phoneError }}</p>
      <UiAppButton
        class="mt-3 w-full"
        :loading="sending"
        :disabled="!phone || phone.length < 10 || cooldown > 0"
        @click="handleSendOtp"
      >
        {{ cooldown > 0 ? `Resend in ${cooldown}s` : resendCount > 0 ? 'Resend OTP' : 'Send OTP' }}
      </UiAppButton>
      <p class="mt-2 text-xs text-accent">
        ✓ Verified buyers reach checkout 3× faster and have fewer delivery issues.
      </p>
      <p v-if="sendError" class="mt-2 text-sm text-error">{{ sendError }}</p>
    </div>

    <!-- OTP input (shown after sending) -->
    <div v-if="resendCount > 0">
      <p class="mb-3 text-sm text-ink-muted">
        OTP sent to {{ phone.slice(0, 2) }}XXXXXX{{ phone.slice(-2) }}
      </p>
      <p v-if="devOtp" class="mb-3 text-xs font-mono text-amber-600 bg-amber-50 px-3 py-1.5 rounded">
        Dev mode — use code: {{ devOtp }}
      </p>
      <UiOTPInput
        :error="otpError ?? undefined"
        @complete="handleVerifyOtp"
      />
    </div>

    <!-- Skip option -->
    <div class="border-t border-border pt-4 space-y-4">
      <div>
        <p class="text-xs text-ink-muted mb-2">Would you like updates on email too?</p>
        <UiAppInput
          v-model="email"
          label="Email (optional)"
          type="email"
          placeholder="you@example.com"
        />
      </div>
      <div class="text-center">
        <button
          class="text-sm text-ink-faint hover:text-ink-muted transition-colors"
          @click="handleSkip"
        >
          Skip for now &rarr; order without verifying
        </button>
      </div>
    </div>
  </div>
</template>
