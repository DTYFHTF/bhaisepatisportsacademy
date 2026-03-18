<script setup lang="ts">
useSeoMeta({
  title: 'Glow Guide — Bhaisepati Sports Academy',
  description: 'Take our personalized skin quiz to find the perfect wax type and service for you. Plus curated treatment combos to save time and money.',
})
</script>

<template>
  <div>
    <!-- Header -->
    <section class="bg-peach-50 border-b border-peach-200">
      <div class="section-container py-12 sm:py-16">
        <p class="text-label text-accent mb-2">Personalised Recommendations</p>
        <h1 class="text-display-lg text-ink">Glow Guide</h1>
        <p class="mt-3 text-ink-muted max-w-lg">
          Not sure which service or wax type is right for you? Answer a few quick questions and we'll match you perfectly.
        </p>
      </div>
    </section>

    <!-- Quiz -->
    <section class="section-padding">
      <div class="section-container max-w-3xl">
        <AiGlowQuiz />
      </div>
    </section>

    <!-- Treatment combos -->
    <section class="section-padding bg-surface">
      <div class="section-container">
        <AiTreatmentBuilder />
      </div>
    </section>
  </div>
</template>
