<script setup lang="ts">
interface Props {
  class?: string
}

defineProps<Props>()
</script>

<template>
  <div :class="['animate-pulse bg-border rounded', $props.class]" />
</template>
