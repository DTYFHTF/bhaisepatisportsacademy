<template>
  <div class="min-h-screen flex flex-col bg-canvas text-ink">
    <NuxtLoadingIndicator color="#FFB800" :height="2" />
    <NuxtRouteAnnouncer />
    <LayoutAppHeader />
    <main class="flex-1">
      <NuxtPage />
    </main>
    <LayoutAppFooter />
    <LayoutCartDrawer />
    <LayoutBookingDrawer />
    <UiAppToast />
  </div>
</template>
