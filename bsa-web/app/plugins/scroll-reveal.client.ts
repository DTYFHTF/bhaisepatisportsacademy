export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.directive('scroll', {
    mounted(el: HTMLElement, binding) {
      const animation = binding.value || 'fade-up'
      const delay = binding.arg ? Number(binding.arg) : 0

      el.setAttribute('data-scroll', animation)
      if (delay) el.style.transitionDelay = `${delay}ms`

      // If already in viewport at mount time, reveal immediately
      const rect = el.getBoundingClientRect()
      if (rect.top < window.innerHeight && rect.bottom > 0) {
        setTimeout(() => el.classList.add('is-visible'), delay || 0)
        return
      }

      const observer = new IntersectionObserver(
        ([entry]) => {
          if (entry.isIntersecting) {
            el.classList.add('is-visible')
            observer.unobserve(el)
          }
        },
        // threshold:0 fires on first pixel; positive rootMargin triggers 60px early
        { threshold: 0, rootMargin: '0px 0px 60px 0px' },
      )
      observer.observe(el)
    },
    unmounted(el: HTMLElement) {
      el.removeAttribute('data-scroll')
    },
  })
})
