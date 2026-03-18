<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next'

interface Section {
  title: string
  content: string
}

interface Props {
  sections: Section[]
}

defineProps<Props>()

const openIndex = ref<number | null>(null)

function toggle(index: number) {
  openIndex.value = openIndex.value === index ? null : index
}
</script>

<template>
  <div class="divide-y divide-border border-t border-border">
    <div v-for="(section, index) in sections" :key="index">
      <button
        class="flex w-full items-center justify-between py-4 text-left"
        :aria-expanded="openIndex === index"
        @click="toggle(index)"
      >
        <span class="text-sm font-medium">{{ section.title }}</span>
        <ChevronDown
          :class="[
            'h-4 w-4 text-ink-muted transition-transform duration-base',
            openIndex === index && 'rotate-180',
          ]"
        />
      </button>
      <Transition name="accordion">
        <div v-if="openIndex === index" class="pb-4">
          <div class="text-sm text-ink-muted leading-relaxed" v-html="section.content" />
        </div>
      </Transition>
    </div>
  </div>
</template>

<style scoped>
.accordion-enter-active {
  transition: all 200ms ease-out;
}
.accordion-leave-active {
  transition: all 150ms ease-in;
}
.accordion-enter-from,
.accordion-leave-to {
  opacity: 0;
  max-height: 0;
}
.accordion-enter-to,
.accordion-leave-from {
  max-height: 500px;
}
</style>
