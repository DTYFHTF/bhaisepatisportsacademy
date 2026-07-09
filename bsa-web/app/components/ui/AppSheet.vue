<script setup lang="ts">
const props = withDefaults(defineProps<{
  open: boolean
  side?: 'right' | 'bottom'
}>(), {
  side: 'right',
})

const emit = defineEmits<{
  close: []
}>()

const panel = ref<HTMLElement | null>(null)
useDialogBehavior(toRef(props, 'open'), () => emit('close'), panel)
</script>

<template>
  <Teleport to="body">
    <Transition :name="side === 'right' ? 'sheet-right' : 'sheet-bottom'">
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
          ref="panel"
          :class="[
            'sheet-panel absolute z-10 bg-canvas shadow-lg overflow-y-auto',
            side === 'right' ? 'right-0 top-0 h-full w-full max-w-[480px]' : 'bottom-0 left-0 w-full max-h-[85vh] rounded-t-xl',
          ]"
          role="dialog"
          aria-modal="true"
          tabindex="-1"
        >
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
