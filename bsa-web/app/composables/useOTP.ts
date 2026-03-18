import { ref } from 'vue'

export function useOTP() {
  const config = useRuntimeConfig()
  const sending = ref(false)
  const verifying = ref(false)
  const cooldown = ref(0)
  const resendCount = ref(0)
  const error = ref<string | null>(null)
  const devOtp = ref<string | null>(null)

  let cooldownTimer: ReturnType<typeof setInterval> | null = null

  async function sendOtp(phone: string) {
    if (cooldown.value > 0) return
    sending.value = true
    error.value = null
    devOtp.value = null

    try {
      const response = await $fetch<{ message: string; devOtp?: string }>(`${config.public.apiBase}/otp/send`, {
        method: 'POST',
        body: { phone },
      })
      resendCount.value++
      if (response.devOtp) {
        devOtp.value = response.devOtp
      }
      startCooldown()
    } catch (e: any) {
      error.value = e?.data?.message || 'Failed to send OTP. Please try again.'
    } finally {
      sending.value = false
    }
  }

  async function verifyOtp(phone: string, code: string): Promise<string | null> {
    verifying.value = true
    error.value = null

    try {
      const response = await $fetch<{ token: string }>(`${config.public.apiBase}/otp/verify`, {
        method: 'POST',
        body: { phone, code },
      })
      return response.token
    } catch (e: any) {
      error.value = e?.data?.message || 'Invalid or expired code.'
      return null
    } finally {
      verifying.value = false
    }
  }

  function startCooldown() {
    cooldown.value = 60
    cooldownTimer = setInterval(() => {
      cooldown.value--
      if (cooldown.value <= 0 && cooldownTimer) {
        clearInterval(cooldownTimer)
        cooldownTimer = null
      }
    }, 1000)
  }

  return { sending, verifying, cooldown, resendCount, error, devOtp, sendOtp, verifyOtp }
}
