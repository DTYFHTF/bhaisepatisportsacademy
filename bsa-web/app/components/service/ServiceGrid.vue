<script setup lang="ts">
import type { Service } from '~/types/service'

interface Props {
  services: Service[]
  columns?: 2 | 3
}

withDefaults(defineProps<Props>(), {
  columns: 3,
})
</script>

<template>
  <div
    class="grid gap-4"
    :class="columns === 3 ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' : 'grid-cols-1 sm:grid-cols-2'"
  >
    <ServiceCard
      v-for="service in services"
      :key="service.id"
      :service="service"
    />
  </div>
</template>
