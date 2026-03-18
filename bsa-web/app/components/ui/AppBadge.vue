<script setup lang="ts">
interface Props {
  label: string
  variant?: 'default' | 'success' | 'warning' | 'error'
}

withDefaults(defineProps<Props>(), {
  variant: 'default',
})

const variantClasses = {
  default: 'border-ink text-ink',
  success: 'border-success text-success',
  warning: 'border-warning text-warning',
  error: 'border-error text-error',
}
</script>

<template>
  <span
    :class="[
      'inline-block border px-2 py-0.5 text-label',
      variantClasses[variant],
    ]"
  >
    {{ label }}
  </span>
</template>
