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
