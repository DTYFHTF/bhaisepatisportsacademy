// @vueuse/motion animation variants for Bhaisepati Sports Academy

export const fadeIn = {
  initial: { opacity: 0, y: 8 },
  enter: { opacity: 1, y: 0, transition: { duration: 200 } },
}

export const fadeInUp = {
  initial: { opacity: 0, y: 20 },
  enter: { opacity: 1, y: 0, transition: { duration: 280 } },
}

export const slideInRight = {
  initial: { x: '100%' },
  enter: { x: '0%', transition: { duration: 280, ease: 'easeOut' } },
  leave: { x: '100%', transition: { duration: 180, ease: 'easeIn' } },
}

export const slideInUp = {
  initial: { y: '100%' },
  enter: { y: '0%', transition: { duration: 280, ease: 'easeOut' } },
  leave: { y: '100%', transition: { duration: 180, ease: 'easeIn' } },
}

export const scaleIn = {
  initial: { opacity: 0, scale: 0.96 },
  enter: { opacity: 1, scale: 1, transition: { duration: 200 } },
}

export const staggerContainer = {
  initial: { opacity: 0 },
  enter: {
    opacity: 1,
    transition: {
      staggerChildren: 60,
      delayChildren: 100,
    },
  },
}

export const staggerItem = {
  initial: { opacity: 0, y: 12 },
  enter: { opacity: 1, y: 0, transition: { duration: 200 } },
}
