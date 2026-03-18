<script setup lang="ts">
useHead({ title: 'Page Not Found — Bhaisepati Sports Academy' })

definePageMeta({
  layout: 'default',
})
</script>

<template>
  <div class="mx-auto max-w-md px-4 py-24 text-center">
    <p class="text-6xl font-serif text-ink-faint">404</p>
    <h1 class="mt-4 text-heading-lg">This page is gone.</h1>
    <p class="mt-2 text-ink-muted">Like a limited jacket in your size.</p>
    <NuxtLink to="/">
      <UiAppButton variant="primary" class="mt-8">
        Back to Home
      </UiAppButton>
    </NuxtLink>
  </div>
</template>
