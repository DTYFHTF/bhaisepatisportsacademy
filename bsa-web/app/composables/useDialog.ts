import type { Ref } from 'vue'

/**
 * Shared dialog behavior: Esc closes, and focus moves into the dialog on
 * open and back to the previously focused element on close.
 */
export function useDialogBehavior(
  open: Ref<boolean>,
  close: () => void,
  panel: Ref<HTMLElement | null>,
) {
  let lastFocused: HTMLElement | null = null

  function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') close()
  }

  watch(open, (isOpen) => {
    if (!import.meta.client) return
    if (isOpen) {
      lastFocused = document.activeElement as HTMLElement | null
      document.addEventListener('keydown', onKeydown)
      nextTick(() => panel.value?.focus())
    }
    else {
      document.removeEventListener('keydown', onKeydown)
      lastFocused?.focus()
      lastFocused = null
    }
  })

  onUnmounted(() => document.removeEventListener('keydown', onKeydown))
}
