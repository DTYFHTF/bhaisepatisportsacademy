<script setup lang="ts">
interface Props {
  open: boolean
  links: { label: string; to: string }[]
}

defineProps<Props>()
defineEmits<{
  close: []
}>()
</script>

<template>
  <Transition name="mobile-nav">
    <nav
      v-if="open"
      class="lg:hidden border-t border-border bg-surface px-4 py-6"
      aria-label="Mobile navigation"
    >
      <div class="flex flex-col gap-4">
        <div class="pb-2 mb-1 border-b border-border">
          <span class="font-display text-lg uppercase tracking-wider text-accent">BSA</span>
          <span class="text-sm text-ink-muted ml-2">Sports Academy</span>
        </div>
        <NuxtLink
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="text-lg font-medium uppercase tracking-wider text-ink-muted transition-colors hover:text-accent"
          active-class="!text-accent"
          @click="$emit('close')"
        >
          {{ link.label }}
        </NuxtLink>
        <NuxtLink
          to="/book"
          class="mt-2 inline-flex items-center justify-center rounded-lg bg-accent px-6 py-3 text-sm font-bold uppercase tracking-wider text-canvas"
          @click="$emit('close')"
        >
          Book a Court
        </NuxtLink>
      </div>
    </nav>
  </Transition>
</template>

<style scoped>
.mobile-nav-enter-active {
  transition: all 200ms ease-out;
}
.mobile-nav-leave-active {
  transition: all 150ms ease-in;
}
.mobile-nav-enter-from,
.mobile-nav-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
