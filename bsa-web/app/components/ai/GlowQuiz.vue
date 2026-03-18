<script setup lang="ts">
import { Sparkles, RotateCcw } from 'lucide-vue-next'

const { answers, step, result, setAnswer, calculate, reset } = useGlowQuiz()

const steps = [
  {
    question: 'What\'s your skin type?',
    key: 'skinType' as const,
    options: [
      { value: 'normal', label: 'Normal', desc: 'Not too oily, not too dry' },
      { value: 'sensitive', label: 'Sensitive', desc: 'Reacts easily, redness prone' },
      { value: 'oily', label: 'Oily', desc: 'Shiny, especially T-zone' },
      { value: 'dry', label: 'Dry', desc: 'Tight, sometimes flaky' },
    ],
  },
  {
    question: 'Which areas are you focusing on?',
    key: 'area' as const,
    options: [
      { value: 'face', label: 'Face', desc: 'Brows, upper lip, facial' },
      { value: 'body', label: 'Body', desc: 'Arms, legs, underarms' },
      { value: 'both', label: 'Both', desc: 'Full treatment' },
    ],
  },
  {
    question: 'How would you rate your pain tolerance?',
    key: 'painTolerance' as const,
    options: [
      { value: 'low', label: 'Low', desc: 'I prefer the gentlest option' },
      { value: 'medium', label: 'Medium', desc: 'I can handle some discomfort' },
      { value: 'high', label: 'High', desc: 'Doesn\'t bother me much' },
    ],
  },
  {
    question: 'What\'s most important to you?',
    key: 'priority' as const,
    options: [
      { value: 'speed', label: 'Speed', desc: 'Quick and efficient' },
      { value: 'comfort', label: 'Comfort', desc: 'Gentle experience first' },
      { value: 'precision', label: 'Precision', desc: 'Thorough, detailed results' },
    ],
  },
]

function handleSelect(key: string, value: string) {
  setAnswer(key as any, value as any)
  if (step.value === 3) {
    nextTick(() => calculate())
  }
}
</script>

<template>
  <div class="rounded-2xl border border-peach-200 bg-peach-50/50 p-6 sm:p-8">
    <div class="flex items-center gap-2 mb-6">
      <Sparkles class="h-5 w-5 text-accent" />
      <h2 class="text-heading-md">Glow Guide Quiz</h2>
    </div>

    <!-- Quiz steps -->
    <div v-if="!result">
      <!-- Progress -->
      <div class="mb-6 flex gap-1.5">
        <div
          v-for="i in 4"
          :key="i"
          class="h-1 flex-1 rounded-full transition-colors"
          :class="i <= step + 1 ? 'bg-accent' : 'bg-peach-200'"
        />
      </div>

      <Transition name="fade" mode="out-in">
        <div :key="step">
          <p class="text-lg font-medium text-ink mb-4">{{ steps[step].question }}</p>
          <div class="grid gap-3 sm:grid-cols-2">
            <button
              v-for="opt in steps[step].options"
              :key="opt.value"
              class="rounded-xl border-2 p-4 text-left transition-all"
              :class="
                answers[steps[step].key] === opt.value
                  ? 'border-accent bg-white shadow-sm'
                  : 'border-peach-200 hover:border-peach-300'
              "
              @click="handleSelect(steps[step].key, opt.value)"
            >
              <p class="font-medium text-ink">{{ opt.label }}</p>
              <p class="mt-0.5 text-sm text-ink-muted">{{ opt.desc }}</p>
            </button>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Results -->
    <div v-else class="space-y-6">
      <div class="rounded-xl bg-white p-5 border border-peach-200">
        <p class="text-label text-accent mb-2">Your Recommended Wax</p>
        <p class="text-xl font-medium text-ink">{{ result.preferredWax }}</p>
        <p class="mt-2 text-sm text-ink-muted">{{ result.tip }}</p>
      </div>

      <div>
        <p class="text-label text-ink-muted mb-3">Perfect Services for You</p>
        <div class="grid gap-3 sm:grid-cols-2">
          <ServiceCard
            v-for="service in result.recommended"
            :key="service.id"
            :service="service"
          />
        </div>
      </div>

      <button
        class="flex items-center gap-2 text-sm text-ink-muted hover:text-ink"
        @click="reset()"
      >
        <RotateCcw class="h-4 w-4" />
        Retake quiz
      </button>
    </div>
  </div>
</template>
