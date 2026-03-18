<script setup lang="ts">
useHead({ title: 'Size Guide — Bhaisepati Sports Academy' })

const showSizeFinder = ref(false)

const sizeCharts = {
  tops: {
    label: 'Tops & Jackets',
    headers: ['Size', 'Chest (in)', 'Length (in)', 'Shoulder (in)'],
    rows: [
      ['S', '36–38', '26', '16.5'],
      ['M', '39–41', '27', '17'],
      ['L', '42–44', '28', '17.5'],
      ['XL', '45–47', '29', '18'],
    ],
  },
  bottoms: {
    label: 'Bottoms',
    headers: ['Size', 'Waist (in)', 'Length (in)', 'Hip (in)'],
    rows: [
      ['S', '28–30', '40', '36–38'],
      ['M', '31–33', '41', '39–41'],
      ['L', '34–36', '42', '42–44'],
      ['XL', '37–39', '43', '45–47'],
    ],
  },
}
</script>

<template>
  <div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-heading-xl">Size Guide</h1>
      <UiAppButton variant="secondary" size="sm" @click="showSizeFinder = true">
        Try AI Size Finder
      </UiAppButton>
    </div>

    <p class="text-ink-muted mb-12">
      Our sizing follows a relaxed fit. When in between sizes, we recommend sizing up for an
      easy, layered look or sizing down for a more fitted silhouette.
    </p>

    <div v-for="(chart, key) in sizeCharts" :key="key" class="mb-12">
      <h2 class="text-heading-md mb-4">{{ chart.label }}</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-border">
              <th
                v-for="header in chart.headers"
                :key="header"
                class="py-3 pr-6 text-left text-label text-ink-muted"
              >
                {{ header }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in chart.rows" :key="row[0]" class="border-b border-border/50">
              <td
                v-for="(cell, i) in row"
                :key="i"
                :class="['py-3 pr-6', i === 0 ? 'font-medium' : 'text-ink-muted']"
              >
                {{ cell }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="border-t border-border pt-8">
      <h2 class="text-heading-md mb-4">How to Measure</h2>
      <ul class="space-y-3 text-sm text-ink-muted">
        <li><strong class="text-ink">Chest:</strong> Measure around the fullest part, arms relaxed at sides.</li>
        <li><strong class="text-ink">Waist:</strong> Measure around your natural waistline.</li>
        <li><strong class="text-ink">Hip:</strong> Measure around the widest part of your hips.</li>
        <li><strong class="text-ink">Length:</strong> Measure from the highest point of the shoulder to the hem.</li>
      </ul>
    </div>

    <UiAppModal :open="showSizeFinder" title="AI Size Finder" @close="showSizeFinder = false">
      <AiSizeFinder category="TOP" @close="showSizeFinder = false" />
    </UiAppModal>
  </div>
</template>
