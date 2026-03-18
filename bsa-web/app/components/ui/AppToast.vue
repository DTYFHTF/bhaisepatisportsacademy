<script setup lang="ts">
import { useToast } from '~/composables/useToast'

const { toasts } = useToast()

const typeClasses = {
  success: 'bg-success text-canvas',
  error: 'bg-error text-canvas',
  info: 'bg-ink text-canvas',
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed bottom-6 right-6 z-[60] flex flex-col gap-2">
      <TransitionGroup name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="['px-4 py-3 text-sm shadow-md', typeClasses[toast.type]]"
        >
          {{ toast.message }}
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active {
  transition: all 200ms ease-out;
}
.toast-leave-active {
  transition: all 150ms ease-in;
}
.toast-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(20px);
}
</style>
