<template>
  <div class="min-h-screen flex flex-col">
    <NuxtLoadingIndicator color="#E8967A" :height="2" />
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
