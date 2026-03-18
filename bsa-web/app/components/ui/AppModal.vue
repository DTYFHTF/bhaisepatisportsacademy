<script setup lang="ts">
interface Props {
  open: boolean
  title?: string
}

defineProps<Props>()
defineEmits<{
  close: []
}>()
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center"
      >
        <!-- Overlay -->
        <div
          class="absolute inset-0 bg-overlay"
          @click="$emit('close')"
        />
        <!-- Content -->
        <div
          v-motion="{
            initial: { opacity: 0, scale: 0.96 },
            enter: { opacity: 1, scale: 1, transition: { duration: 200 } },
          }"
          class="relative z-10 mx-4 w-full max-w-lg bg-canvas p-6 shadow-lg"
          role="dialog"
          :aria-label="title"
        >
          <div v-if="title" class="mb-4 flex items-center justify-between">
            <h2 class="text-heading-md">{{ title }}</h2>
            <button
              class="p-1 text-ink-muted hover:text-ink"
              aria-label="Close"
              @click="$emit('close')"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
