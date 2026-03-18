<script setup lang="ts">
import { useSizing } from '~/composables/useSizing'

interface Props {
  category?: 'JACKET' | 'TOP' | 'BOTTOM'
}

const props = withDefaults(defineProps<Props>(), {
  category: 'TOP',
})

const emit = defineEmits<{
  recommend: [size: string]
  close: []
}>()

const { result, recommend } = useSizing()

const form = reactive({
  heightCm: '',
  weightKg: '',
  chestCm: '',
  waistCm: '',
  fitPreference: 'regular' as 'slim' | 'regular' | 'relaxed',
})

const step = ref<'input' | 'result'>('input')

function handleSubmit() {
  const input = {
    heightCm: Number(form.heightCm),
    weightKg: Number(form.weightKg),
    chestCm: form.chestCm ? Number(form.chestCm) : undefined,
    waistCm: form.waistCm ? Number(form.waistCm) : undefined,
    fitPreference: form.fitPreference,
    category: props.category,
  }
  recommend(input)
  step.value = 'result'
}

function applySize() {
  if (result.value) {
    emit('recommend', result.value.size)
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Input form -->
    <template v-if="step === 'input'">
      <h3 class="text-heading-md">Find Your Size</h3>
      <p class="text-sm text-ink-muted">Enter your measurements for a personalized recommendation.</p>

      <div class="grid grid-cols-2 gap-4">
        <UiAppInput
          v-model="form.heightCm"
          label="Height (cm)"
          type="number"
          placeholder="170"
          required
        />
        <UiAppInput
          v-model="form.weightKg"
          label="Weight (kg)"
          type="number"
          placeholder="70"
        />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <UiAppInput
          v-model="form.chestCm"
          label="Chest (cm)"
          type="number"
          placeholder="Optional"
        />
        <UiAppInput
          v-model="form.waistCm"
          label="Waist (cm)"
          type="number"
          placeholder="Optional"
        />
      </div>

      <div>
        <span class="text-label text-ink-muted mb-2 block">Fit Preference</span>
        <div class="flex gap-2">
          <button
            v-for="fit in ['slim', 'regular', 'relaxed'] as const"
            :key="fit"
            :class="[
              'flex-1 border px-3 py-2 text-sm capitalize transition-colors',
              form.fitPreference === fit ? 'border-ink bg-ink text-canvas' : 'border-border hover:border-ink',
            ]"
            @click="form.fitPreference = fit"
          >
            {{ fit }}
          </button>
        </div>
      </div>

      <UiAppButton
        class="w-full"
        :disabled="!form.heightCm"
        @click="handleSubmit"
      >
        Get Recommendation
      </UiAppButton>
    </template>

    <!-- Result -->
    <template v-if="step === 'result' && result">
      <div class="text-center space-y-4">
        <p class="text-4xl font-serif font-light">{{ result.size }}</p>
        <div class="flex justify-center">
          <UiAppBadge
            :label="result.confidence === 'high' ? 'High confidence' : result.confidence === 'medium' ? 'Medium confidence' : 'Low confidence'"
            :variant="result.confidence === 'high' ? 'success' : result.confidence === 'medium' ? 'warning' : 'default'"
          />
        </div>
        <p class="text-sm text-ink-muted">{{ result.explanation }}</p>
      </div>

      <div class="flex gap-3">
        <UiAppButton variant="ghost" @click="step = 'input'">
          Try Again
        </UiAppButton>
        <UiAppButton class="flex-1" @click="applySize">
          Select {{ result.size }}
        </UiAppButton>
      </div>
    </template>
  </div>
</template>
