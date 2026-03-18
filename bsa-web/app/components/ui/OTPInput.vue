<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'

interface Props {
  length?: number
  error?: string
}

const props = withDefaults(defineProps<Props>(), {
  length: 6,
})

const emit = defineEmits<{
  complete: [code: string]
}>()

const digits = ref<string[]>(Array(props.length).fill(''))
const inputs = ref<HTMLInputElement[]>([])

function handleInput(index: number, event: Event) {
  const input = event.target as HTMLInputElement
  const value = input.value.replace(/\D/g, '')

  if (value.length > 1) {
    // Handle paste
    const chars = value.split('').slice(0, props.length)
    chars.forEach((char, i) => {
      if (i < props.length) digits.value[i] = char
    })
    const focusIdx = Math.min(chars.length, props.length - 1)
    nextTick(() => inputs.value[focusIdx]?.focus())
  } else {
    digits.value[index] = value
    if (value && index < props.length - 1) {
      nextTick(() => inputs.value[index + 1]?.focus())
    }
  }

  const code = digits.value.join('')
  if (code.length === props.length) {
    setTimeout(() => emit('complete', code), 300)
  }
}

function handleKeydown(index: number, event: KeyboardEvent) {
  if (event.key === 'Backspace' && !digits.value[index] && index > 0) {
    digits.value[index - 1] = ''
    nextTick(() => inputs.value[index - 1]?.focus())
  }
}

function handlePaste(event: ClipboardEvent) {
  event.preventDefault()
  const text = event.clipboardData?.getData('text') || ''
  const chars = text.replace(/\D/g, '').split('').slice(0, props.length)
  chars.forEach((char, i) => {
    digits.value[i] = char
  })
  const focusIdx = Math.min(chars.length, props.length - 1)
  nextTick(() => inputs.value[focusIdx]?.focus())

  const code = digits.value.join('')
  if (code.length === props.length) {
    setTimeout(() => emit('complete', code), 300)
  }
}
</script>

<template>
  <div>
    <div class="flex gap-2">
      <input
        v-for="(_, index) in length"
        :key="index"
        :ref="(el) => { if (el) inputs[index] = el as HTMLInputElement }"
        v-model="digits[index]"
        type="text"
        inputmode="numeric"
        autocomplete="one-time-code"
        maxlength="1"
        :aria-label="`Digit ${index + 1}`"
        :class="[
          'h-12 w-10 border text-center text-lg font-mono transition-colors focus:outline-none focus:border-ink',
          error ? 'border-error' : 'border-border',
        ]"
        @input="handleInput(index, $event)"
        @keydown="handleKeydown(index, $event)"
        @paste="handlePaste"
      />
    </div>
    <p
      v-if="error"
      class="mt-2 text-sm text-error"
      role="alert"
    >
      {{ error }}
    </p>
  </div>
</template>
