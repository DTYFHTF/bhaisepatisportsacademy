<script setup lang="ts">
import { SERVICE_CATEGORIES, SERVICE_CATEGORY_LABELS } from '~/utils/constants'
import type { Service, ServiceCategory } from '~/types/service'

useSeoMeta({
  title: 'Services — Bhaisepati Sports Academy',
  description: 'Professional waxing, facial, and brow services in Kathmandu. Rica, honey, chocolate, and sugar wax options available.',
})

const config = useRuntimeConfig()
const activeCategory = ref<ServiceCategory | null>(null)

const { data: services, status } = await useFetch<Service[]>(`${config.public.apiBase}/services`, {
  default: () => [],
})

const filteredServices = computed(() => {
  const list = services.value ?? []
  if (!activeCategory.value) return list
  return list.filter((s) => s.category === activeCategory.value)
})
</script>

<template>
  <div>
    <!-- Header -->
    <section class="bg-peach-50 border-b border-peach-200">
      <div class="section-container py-12 sm:py-16">
        <p class="text-label text-accent mb-2">What We Do</p>
        <h1 class="text-display-lg text-ink">Our Services</h1>
        <p class="mt-3 text-ink-muted max-w-lg">
          Professional waxing and beauty treatments with premium products. Choose your service and book in under a minute.
        </p>
      </div>
    </section>

    <!-- Filters -->
    <section class="border-b border-border sticky top-16 z-30 bg-canvas/95 backdrop-blur-sm">
      <div class="section-container py-3">
        <div class="flex gap-2 overflow-x-auto no-scrollbar">
          <button
            class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
            :class="!activeCategory ? 'bg-accent text-white' : 'bg-surface text-ink-muted hover:text-ink'"
            @click="activeCategory = null"
          >
            All
          </button>
          <button
            v-for="cat in SERVICE_CATEGORIES"
            :key="cat"
            class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
            :class="activeCategory === cat ? 'bg-accent text-white' : 'bg-surface text-ink-muted hover:text-ink'"
            @click="activeCategory = cat"
          >
            {{ SERVICE_CATEGORY_LABELS[cat] }}
          </button>
        </div>
      </div>
    </section>

    <!-- Services grid -->
    <section class="section-padding">
      <div class="section-container">
        <div v-if="status === 'pending'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="i in 6" :key="i" class="h-52 rounded-xl bg-surface animate-pulse" />
        </div>

        <ServiceGrid
          v-else
          :services="filteredServices"
          :columns="3"
        />

        <div v-if="status !== 'pending' && filteredServices.length === 0" class="text-center py-12">
          <p class="text-ink-muted">No services in this category yet.</p>
        </div>
      </div>
    </section>
  </div>
</template>
