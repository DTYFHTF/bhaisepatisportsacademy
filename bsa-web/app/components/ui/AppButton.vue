<script setup lang="ts">
interface Props {
  variant?: 'primary' | 'secondary' | 'ghost' | 'destructive'
  size?: 'sm' | 'md' | 'lg'
  loading?: boolean
  disabled?: boolean
  type?: 'button' | 'submit' | 'reset'
}

withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  loading: false,
  disabled: false,
  type: 'button',
})

defineEmits<{
  click: [e: MouseEvent]
}>()

const variantClasses = {
  primary: 'bg-accent text-white hover:bg-accent-hover active:scale-[0.97] rounded-lg',
  secondary: 'bg-transparent text-ink border border-ink hover:bg-ink hover:text-canvas active:scale-[0.97] rounded-lg',
  ghost: 'bg-transparent text-ink hover:bg-surface active:scale-[0.97] rounded-lg',
  destructive: 'bg-error text-canvas hover:bg-red-800 active:scale-[0.97] rounded-lg',
}

const sizeClasses = {
  sm: 'px-4 py-2 text-xs',
  md: 'px-6 py-3 text-xs',
  lg: 'px-8 py-4 text-sm',
}
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center font-medium uppercase tracking-[0.05em] transition-all duration-base',
      variantClasses[variant],
      sizeClasses[size],
      (disabled || loading) && 'opacity-40 cursor-not-allowed',
    ]"
    @click="$emit('click', $event)"
  >
    <svg
      v-if="loading"
      class="mr-2 h-4 w-4 animate-spin"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
    </svg>
    <slot />
  </button>
</template>
