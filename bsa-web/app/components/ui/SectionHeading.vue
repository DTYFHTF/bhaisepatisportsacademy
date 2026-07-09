<script setup lang="ts">
withDefaults(defineProps<{
  /** Small uppercase accent line above the heading */
  eyebrow?: string
  title: string
  subtitle?: string
  align?: 'center' | 'left'
  /** Set true on dark section backgrounds */
  inverse?: boolean
}>(), {
  align: 'center',
  inverse: false,
})
</script>

<template>
  <div :class="align === 'center' ? 'text-center' : 'text-left'">
    <p v-if="eyebrow" class="text-xs font-medium uppercase tracking-[0.2em] text-accent mb-2">{{ eyebrow }}</p>
    <h2 class="font-display text-3xl sm:text-4xl uppercase tracking-tight" :class="inverse ? 'text-white' : 'text-ink'">
      {{ title }}
    </h2>
    <p
      v-if="subtitle"
      class="mt-3 text-sm max-w-sm"
      :class="[inverse ? 'text-white/50' : 'text-ink-muted', align === 'center' ? 'mx-auto' : '']"
    >
      {{ subtitle }}
    </p>
  </div>
</template>
