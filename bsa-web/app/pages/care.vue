<script setup lang="ts">
useHead({ title: 'Aftercare Guide — Bhaisepati Sports Academy' })

const careInstructions = [
  {
    fabric: 'Cotton & Cotton Blends',
    steps: [
      'Machine wash cold (30°C) or hand wash',
      'Use mild detergent, skip fabric softener',
      'Hang dry in shade — avoid direct sunlight',
      'Iron on medium heat while slightly damp',
    ],
  },
  {
    fabric: 'Wool & Wool Blends',
    steps: [
      'Hand wash in cold water only',
      'Lay flat to dry on a clean towel',
      'Never wring or twist',
      'Steam or iron on low heat with a pressing cloth',
    ],
  },
  {
    fabric: 'Linen',
    steps: [
      'Machine wash cold on gentle cycle',
      'Tumble dry low or hang dry',
      'Wrinkles are natural — embrace them',
      'Iron on high heat if needed',
    ],
  },
  {
    fabric: 'Denim & Twill',
    steps: [
      'Wash inside out in cold water',
      'Wash less often — spot clean when possible',
      'Hang dry to maintain shape',
      'No bleach, ever',
    ],
  },
]
</script>

<template>
  <div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <h1 class="text-heading-xl mb-4">Care Instructions</h1>
    <p class="text-ink-muted mb-12">Taking care of your clothes means they take care of you longer.</p>

    <div class="space-y-10">
      <div v-for="item in careInstructions" :key="item.fabric" class="border-t border-border pt-6">
        <h2 class="text-heading-md mb-4">{{ item.fabric }}</h2>
        <ol class="list-decimal list-inside space-y-2 text-sm text-ink-muted">
          <li v-for="step in item.steps" :key="step">{{ step }}</li>
        </ol>
      </div>
    </div>

    <div class="mt-16 border-t border-border pt-8">
      <h2 class="text-heading-md mb-4">General Tips</h2>
      <ul class="list-disc list-inside space-y-2 text-sm text-ink-muted">
        <li>Always check the care label inside your garment.</li>
        <li>Store folded, not hung, for knits and wool.</li>
        <li>Cedar blocks or lavender sachets keep moths away naturally.</li>
        <li>Repair small tears early — a stitch in time saves nine.</li>
      </ul>
    </div>
  </div>
</template>
