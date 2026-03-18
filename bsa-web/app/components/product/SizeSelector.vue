<script setup lang="ts">
import type { ProductVariant } from '~/types/product'
import { SIZES } from '~/utils/constants'

interface Props {
  variants: ProductVariant[]
  modelValue: string | null
  suggestedSize?: string | null
}

defineProps<Props>()
defineEmits<{
  'update:modelValue': [size: string]
}>()

function getVariant(variants: ProductVariant[], size: string): ProductVariant | undefined {
  return variants.find((v) => v.size === size)
}

function isAvailable(variant: ProductVariant | undefined): boolean {
  if (!variant) return false
  return variant.stock - variant.reservedStock > 0
}
</script>

<template>
  <div>
    <div class="mb-2 flex items-center justify-between">
      <span class="text-label text-ink-muted">Size</span>
      <NuxtLink to="/sizing" class="text-xs text-ink-muted underline underline-offset-2">
        Size guide
      </NuxtLink>
    </div>
    <div class="flex flex-wrap gap-2">
      <button
        v-for="size in SIZES"
        :key="size"
        :disabled="!isAvailable(getVariant(variants, size))"
        :class="[
          'relative min-w-[44px] border px-3 py-2 text-sm transition-colors',
          modelValue === size
            ? 'border-ink bg-ink text-canvas'
            : isAvailable(getVariant(variants, size))
              ? 'border-border text-ink hover:border-ink'
              : 'border-border text-ink-faint line-through cursor-not-allowed',
        ]"
        @click="isAvailable(getVariant(variants, size)) && $emit('update:modelValue', size)"
      >
        {{ size }}
        <!-- AI suggested dot -->
        <span
          v-if="suggestedSize === size && isAvailable(getVariant(variants, size))"
          class="absolute -top-1 -right-1 h-2 w-2 rounded-full bg-accent"
          title="Recommended for you"
        />
      </button>
    </div>
    <!-- AI suggestion note -->
    <p
      v-if="suggestedSize"
      class="mt-2 text-xs text-ink-muted"
    >
      ✦ Based on your measurements: {{ suggestedSize }}
    </p>
  </div>
</template>
