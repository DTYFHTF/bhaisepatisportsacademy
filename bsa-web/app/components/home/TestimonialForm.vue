<script setup lang="ts">
import { Send } from 'lucide-vue-next'

const config = useRuntimeConfig()

const name = ref('')
const role = ref('')
const quote = ref('')
const submitting = ref(false)
const submitted = ref(false)
const error = ref('')

async function submit() {
  if (!name.value.trim() || quote.value.trim().length < 20) return
  submitting.value = true
  error.value = ''
  try {
    await $fetch(`${config.public.apiBase}/testimonials`, {
      method: 'POST',
      body: { name: name.value, role: role.value || undefined, quote: quote.value },
    })
    submitted.value = true
  }
  catch {
    error.value = 'Something went wrong. Please try again.'
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="rounded-2xl border border-border bg-surface p-6">
    <h3 class="font-display text-lg uppercase tracking-wider text-ink mb-1">Share Your Experience</h3>
    <p class="text-xs text-ink-muted mb-5">Your review will be shown after approval.</p>

    <div v-if="submitted" class="flex items-center gap-3 rounded-xl bg-accent/10 border border-accent/20 px-4 py-3" role="status">
      <span class="h-2 w-2 rounded-full bg-accent" />
      <p class="text-sm font-medium text-accent">Thank you! Your testimonial is pending review.</p>
    </div>

    <form v-else class="space-y-4" @submit.prevent="submit">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label for="t-name" class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-1.5 block">Name *</label>
          <input
            id="t-name"
            v-model="name"
            type="text"
            required
            maxlength="100"
            placeholder="Your name"
            class="w-full rounded-lg border border-border bg-canvas px-3 py-2.5 text-sm text-ink focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
          />
        </div>
        <div>
          <label for="t-role" class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-1.5 block">Role</label>
          <input
            id="t-role"
            v-model="role"
            type="text"
            maxlength="100"
            placeholder="e.g. Badminton Player"
            class="w-full rounded-lg border border-border bg-canvas px-3 py-2.5 text-sm text-ink focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
          />
        </div>
      </div>
      <div>
        <label for="t-quote" class="text-xs font-medium uppercase tracking-wider text-ink-muted mb-1.5 block">
          Your Review * <span class="normal-case font-normal">(min 20 chars)</span>
        </label>
        <textarea
          id="t-quote"
          v-model="quote"
          required
          rows="3"
          maxlength="500"
          placeholder="Tell us about your experience at BSA..."
          :aria-invalid="!!error || undefined"
          :aria-describedby="error ? 't-error' : undefined"
          class="w-full rounded-lg border border-border bg-canvas px-3 py-2.5 text-sm text-ink focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent resize-none"
        />
      </div>
      <p v-if="error" id="t-error" class="text-xs text-error" role="alert">{{ error }}</p>
      <UiAppButton
        type="submit"
        variant="primary"
        size="sm"
        :loading="submitting"
        :disabled="!name.trim() || quote.trim().length < 20"
      >
        <Send class="h-3.5 w-3.5 mr-1.5" />
        Submit Review
      </UiAppButton>
    </form>
  </div>
</template>
