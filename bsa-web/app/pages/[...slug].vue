<script setup lang="ts">
useHead({ title: 'Page Not Found | Bhaisepati Sports Academy' })

definePageMeta({
  layout: 'default',
})
</script>

<template>
  <div class="mx-auto max-w-md px-4 py-24 text-center">
    <p class="font-display text-7xl text-accent">404</p>
    <h1 class="mt-4 font-display text-2xl uppercase tracking-wider text-ink">Out of Bounds</h1>
    <p class="mt-2 text-ink-muted">This page doesn't exist. Let's get you back on court.</p>
    <NuxtLink to="/">
      <UiAppButton variant="primary" class="mt-8">
        Back to Home
      </UiAppButton>
    </NuxtLink>
  </div>
</template>
