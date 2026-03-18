<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import type { ProductImage } from '~/types/product'
import { getCloudinaryUrl, getCloudinarySrcSet } from '~/utils/formatters'

interface Props {
  images: ProductImage[]
  productName: string
}

const props = defineProps<Props>()
const activeIndex = ref(0)

function next() {
  activeIndex.value = (activeIndex.value + 1) % props.images.length
}

function prev() {
  activeIndex.value = (activeIndex.value - 1 + props.images.length) % props.images.length
}

function goTo(index: number) {
  activeIndex.value = index
}

const activeImage = computed(() => props.images[activeIndex.value])

function imgSrc(image: ProductImage, width: number): string {
  return image.cloudinaryId ? getCloudinaryUrl(image.cloudinaryId, width) : image.url
}

function imgSrcset(image: ProductImage): string | undefined {
  return image.cloudinaryId ? getCloudinarySrcSet(image.cloudinaryId) : undefined
}
</script>

<template>
  <div class="flex flex-col gap-3 lg:flex-row lg:gap-4">
    <!-- Thumbnails (desktop) -->
    <div class="hidden lg:flex flex-col gap-2 w-20">
      <button
        v-for="(image, index) in images"
        :key="image.id"
        :class="[
          'aspect-[3/4] overflow-hidden border transition-colors',
          index === activeIndex ? 'border-ink' : 'border-transparent hover:border-border',
        ]"
        @click="goTo(index)"
      >
        <img
          :src="imgSrc(image, 200)"
          :alt="image.altText || `${productName} view ${index + 1}`"
          class="h-full w-full object-cover"
          loading="lazy"
        />
      </button>
    </div>

    <!-- Main image -->
    <div class="relative flex-1 aspect-[4/5] overflow-hidden bg-surface group cursor-zoom-in">
      <img
        v-if="activeImage"
        :src="imgSrc(activeImage, 1200)"
        :srcset="imgSrcset(activeImage)"
        sizes="(max-width: 1024px) 100vw, 50vw"
        :alt="activeImage.altText || productName"
        class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110"
      />

      <!-- Mobile navigation arrows -->
      <div class="lg:hidden">
        <button
          v-if="images.length > 1"
          class="absolute left-2 top-1/2 -translate-y-1/2 bg-canvas/80 p-2"
          aria-label="Previous image"
          @click="prev"
        >
          <ChevronLeft class="h-4 w-4" />
        </button>
        <button
          v-if="images.length > 1"
          class="absolute right-2 top-1/2 -translate-y-1/2 bg-canvas/80 p-2"
          aria-label="Next image"
          @click="next"
        >
          <ChevronRight class="h-4 w-4" />
        </button>
      </div>

      <!-- Dot pagination (mobile) -->
      <div v-if="images.length > 1" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 lg:hidden">
        <button
          v-for="(_, index) in images"
          :key="index"
          :class="[
            'h-1.5 w-1.5 rounded-full transition-colors',
            index === activeIndex ? 'bg-ink' : 'bg-ink/30',
          ]"
          :aria-label="`View image ${index + 1}`"
          @click="goTo(index)"
        />
      </div>
    </div>
  </div>
</template>
