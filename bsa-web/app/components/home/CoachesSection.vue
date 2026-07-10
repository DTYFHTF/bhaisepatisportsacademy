<script setup lang="ts">
const config = useRuntimeConfig()

interface Coach {
  id: string
  slug: string
  name: string
  role: string
  bio: string | null
  credentials: string | null
  experienceYears: number | null
  specialties: string[]
  imageUrl: string | null
}

const { data: coaches } = await useFetch<Coach[]>(
  `${config.public.apiBase}/coaches`,
  { server: false },
)

// Initials for the fallback avatar (first letters of the first two words)
function initials(name: string): string {
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((w) => w[0]?.toUpperCase() ?? '')
    .join('')
}
</script>

<template>
  <section v-if="coaches && coaches.length" class="section-padding">
    <div class="section-container">
      <div v-scroll="'fade-up'" class="mb-12">
        <UiSectionHeading
          eyebrow="Meet the Team"
          title="Our Coaches"
          subtitle="Trained, certified, and here to help you improve — whatever your level."
        />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <article
          v-for="(coach, i) in coaches"
          :key="coach.id"
          v-scroll:[i*120]="'fade-up'"
          class="group rounded-2xl border border-border bg-canvas overflow-hidden hover:border-accent/30 hover:shadow-xl hover:shadow-accent/5 transition-all duration-500"
        >
          <!-- Photo / initials avatar -->
          <div class="relative aspect-[4/3] overflow-hidden bg-surface">
            <img
              v-if="coach.imageUrl"
              :src="coach.imageUrl"
              :alt="coach.name"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
            <div
              v-else
              class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-ink to-ink-muted"
              aria-hidden="true"
            >
              <span class="font-display text-5xl tracking-wider text-white/90">{{ initials(coach.name) }}</span>
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
            <span
              v-if="coach.experienceYears"
              class="absolute bottom-3 left-3 rounded-full bg-accent px-3 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-md"
            >
              {{ coach.experienceYears }}+ yrs
            </span>
          </div>

          <div class="p-6">
            <h3 class="font-display text-xl uppercase tracking-wider text-ink">{{ coach.name }}</h3>
            <p class="text-sm font-medium text-accent mb-1">{{ coach.role }}</p>
            <p v-if="coach.credentials" class="text-xs text-ink-muted mb-3">{{ coach.credentials }}</p>

            <p v-if="coach.bio" class="text-sm text-ink-muted leading-relaxed mb-4 line-clamp-4">{{ coach.bio }}</p>

            <ul v-if="coach.specialties.length" class="flex flex-wrap gap-2">
              <li
                v-for="s in coach.specialties"
                :key="s"
                class="rounded-full border border-border bg-surface px-3 py-1 text-xs font-medium text-ink-muted"
              >
                {{ s }}
              </li>
            </ul>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>
