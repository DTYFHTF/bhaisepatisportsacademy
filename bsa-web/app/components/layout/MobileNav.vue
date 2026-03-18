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
      class="lg:hidden border-t border-border bg-canvas px-4 py-6"
      aria-label="Mobile navigation"
    >
      <div class="flex flex-col gap-4">
        <!-- Brand mark at top of mobile menu -->
        <div class="pb-2 mb-1 border-b border-border">
          <span class="font-serif text-lg font-medium text-ink">Bhaisepati Sports Academy</span>
        </div>
        <NuxtLink
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="text-lg font-light text-ink-muted transition-colors hover:text-ink"
          active-class="text-ink"
          @click="$emit('close')"
        >
          {{ link.label }}
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
