<template>
  <div class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-canvas">
    <span class="font-serif text-2xl text-ink loading-pulse">Bhaisepati Sports Academy</span>
  </div>
</template>

<style scoped>
.loading-pulse {
  animation: pulse 1.4s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 0.9; }
  50%       { opacity: 0.35; }
}

@media (prefers-reduced-motion: reduce) {
  .loading-pulse { animation: none; }
}
</style>
