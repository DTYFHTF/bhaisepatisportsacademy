<script setup lang="ts">
import { Package } from 'lucide-vue-next'

const { combos } = useTreatmentCombos()
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center gap-2">
      <Package class="h-5 w-5 text-accent" />
      <h2 class="text-heading-md">Treatment Combos</h2>
    </div>
    <p class="text-ink-muted max-w-xl">
      Save time and money with our curated combinations. Each combo is designed for the best results in one session.
    </p>

    <div class="grid gap-4 sm:grid-cols-2">
      <AiComboCard
        v-for="combo in combos"
        :key="combo.name"
        :combo="combo"
      />
    </div>
  </div>
</template>
