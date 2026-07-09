<script setup lang="ts">
const props = defineProps<{
  /** e.g. "200+" — leading digits animate, the rest renders as suffix */
  valueLabel: string
  label: string
}>()

const el = ref<HTMLElement | null>(null)
const target = computed(() => parseInt((props.valueLabel ?? '').replace(/\D/g, '')) || 0)
const suffix = computed(() => (props.valueLabel ?? '').replace(/^\d+/, ''))

const { count, attach } = useCountUp(el, target)
onMounted(attach)
</script>

<template>
  <div ref="el" class="text-center">
    <p class="font-display text-4xl sm:text-5xl text-accent tracking-tight">
      {{ count }}<span class="text-accent/60">{{ suffix }}</span>
    </p>
    <p class="mt-2 text-sm uppercase tracking-wider text-white/70">{{ label }}</p>
  </div>
</template>
