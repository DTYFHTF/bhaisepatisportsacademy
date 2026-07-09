<script setup lang="ts">
import { BRAND } from '~/utils/constants'

const config = useRuntimeConfig()

// Sitewide social/OG defaults; pages set their own title/description/og
// via usePageSeo().
useSeoMeta({
  ogSiteName: BRAND.name,
  ogType: 'website',
  ogLocale: 'en_US',
})

// Local-business structured data: who/where/when, on every page.
useHead({
  script: [{
    type: 'application/ld+json',
    innerHTML: JSON.stringify({
      '@context': 'https://schema.org',
      '@type': 'SportsActivityLocation',
      'name': BRAND.name,
      'url': config.public.siteUrl,
      'telephone': `+977${BRAND.phone}`,
      'email': BRAND.email,
      'address': {
        '@type': 'PostalAddress',
        'addressLocality': 'Bhaisepati, Lalitpur',
        'addressCountry': 'NP',
      },
      'geo': {
        '@type': 'GeoCoordinates',
        'latitude': config.public.storeLat,
        'longitude': config.public.storeLng,
      },
      'openingHoursSpecification': [{
        '@type': 'OpeningHoursSpecification',
        'dayOfWeek': ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
        'opens': '06:00',
        'closes': '21:00',
      }],
      'sameAs': [BRAND.instagram],
    }),
  }],
})
</script>

<template>
  <div class="min-h-screen flex flex-col bg-canvas text-ink">
    <NuxtLoadingIndicator color="#E8001E" :height="2" />
    <NuxtRouteAnnouncer />
    <LayoutAppHeader />
    <main class="flex-1">
      <NuxtPage />
    </main>
    <LayoutAppFooter />
    <!-- Interactive-only overlays driven by persisted client state -->
    <ClientOnly>
      <LayoutCartDrawer />
      <LayoutBookingDrawer />
      <UiAppToast />
    </ClientOnly>
  </div>
</template>
