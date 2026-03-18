<script setup lang="ts">
interface ColorOption {
  slug: string
  colorName: string
  hex?: string
}

interface Props {
  colors: ColorOption[]
  activeSlug: string
}

defineProps<Props>()
</script>

<template>
  <div>
    <span class="text-label text-ink-muted mb-2 block">Color</span>
    <div class="flex gap-3">
      <NuxtLink
        v-for="color in colors"
        :key="color.slug"
        :to="`/p/${color.slug}`"
        :title="color.colorName"
        :class="[
          'h-6 w-6 rounded-full border-2 transition-all',
          color.slug === activeSlug
            ? 'border-ink ring-2 ring-ink ring-offset-2 ring-offset-canvas'
            : 'border-border hover:border-ink-muted',
        ]"
        :style="color.hex ? { backgroundColor: color.hex } : {}"
      >
        <span class="sr-only">{{ color.colorName }}</span>
      </NuxtLink>
    </div>
  </div>
</template>
