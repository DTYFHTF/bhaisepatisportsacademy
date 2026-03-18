<script setup lang="ts">
interface Props {
  open: boolean
  side?: 'right' | 'bottom'
}

withDefaults(defineProps<Props>(), {
  side: 'right',
})

defineEmits<{
  close: []
}>()
</script>

<template>
  <Teleport to="body">
    <Transition name="sheet">
      <div
        v-if="open"
        class="fixed inset-0 z-50"
      >
        <!-- Overlay -->
        <div
          class="absolute inset-0 bg-overlay"
          @click="$emit('close')"
        />
        <!-- Sheet -->
        <div
          v-motion="
            side === 'right'
              ? {
                  initial: { x: '100%' },
                  enter: { x: '0%', transition: { duration: 280 } },
                  leave: { x: '100%', transition: { duration: 180 } },
                }
              : {
                  initial: { y: '100%' },
                  enter: { y: '0%', transition: { duration: 280 } },
                  leave: { y: '100%', transition: { duration: 180 } },
                }
          "
          :class="[
            'absolute z-10 bg-canvas shadow-lg overflow-y-auto',
            side === 'right' ? 'right-0 top-0 h-full w-full max-w-[480px]' : 'bottom-0 left-0 w-full max-h-[85vh] rounded-t-xl',
          ]"
          role="dialog"
        >
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
