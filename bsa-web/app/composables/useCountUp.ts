import type { Ref } from 'vue'

/**
 * Animates a number from 0 to `target` (ease-out cubic, 2s) when `el`
 * enters the viewport. Runs once. Respects prefers-reduced-motion by
 * jumping straight to the target.
 */
export function useCountUp(el: Ref<HTMLElement | null>, target: Ref<number>) {
  const count = ref(0)
  let observer: IntersectionObserver | null = null
  let started = false

  function run() {
    if (started) return
    started = true

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      count.value = target.value
      return
    }

    const start = performance.now()
    const animate = (now: number) => {
      const progress = Math.min((now - start) / 2000, 1)
      const eased = 1 - Math.pow(1 - progress, 3)
      count.value = Math.floor(eased * target.value)
      if (progress < 1) requestAnimationFrame(animate)
    }
    requestAnimationFrame(animate)
  }

  function attach() {
    if (!el.value) return

    const rect = el.value.getBoundingClientRect()
    if (rect.top < window.innerHeight && rect.bottom > 0) {
      run()
      return
    }

    observer = new IntersectionObserver(([entry]) => {
      if (entry?.isIntersecting) {
        run()
        observer?.disconnect()
      }
    }, { threshold: 0 })
    observer.observe(el.value)
  }

  onUnmounted(() => observer?.disconnect())

  return { count, attach }
}
