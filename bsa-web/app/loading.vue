<template>
  <div class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-[#0A0A0F]">
    <!-- Animated shuttlecock -->
    <div class="shuttle-bounce mb-6">
      <svg width="48" height="48" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Cork base -->
        <circle cx="50" cy="72" r="14" fill="#FFB800" />
        <circle cx="50" cy="72" r="10" fill="#E5A600" />
        <!-- Feathers -->
        <path d="M50 58 L38 18 C42 28 46 38 50 42 C54 38 58 28 62 18 Z" fill="#FFB800" opacity="0.7" />
        <path d="M50 58 L30 25 C36 32 42 38 50 42 C44 38 38 32 32 25 Z" fill="#FFB800" opacity="0.5" />
        <path d="M50 58 L70 25 C64 32 58 38 50 42 C56 38 62 32 68 25 Z" fill="#FFB800" opacity="0.5" />
        <path d="M50 58 L24 32 C32 36 40 40 50 42" fill="#FFB800" opacity="0.3" />
        <path d="M50 58 L76 32 C68 36 60 40 50 42" fill="#FFB800" opacity="0.3" />
      </svg>
    </div>

    <!-- Brand -->
    <span class="text-xl font-bold uppercase tracking-[0.15em] text-[#FFB800] loading-pulse">BSA</span>
    <span class="text-xs uppercase tracking-[0.2em] text-[#ffffff40] mt-1">Sports Academy</span>
  </div>
</template>

<style scoped>
.shuttle-bounce {
  animation: shuttle 1.2s ease-in-out infinite;
}

@keyframes shuttle {
  0%, 100% {
    transform: translateY(0) rotate(0deg);
  }
  30% {
    transform: translateY(-16px) rotate(-5deg);
  }
  60% {
    transform: translateY(-8px) rotate(3deg);
  }
}

.loading-pulse {
  animation: pulse 1.4s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.4; }
}

@media (prefers-reduced-motion: reduce) {
  .shuttle-bounce,
  .loading-pulse { animation: none; }
}
</style>
