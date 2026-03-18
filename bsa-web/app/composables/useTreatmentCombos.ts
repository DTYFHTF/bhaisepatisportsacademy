import type { ServiceDef } from '~/utils/constants'
import { SERVICES } from '~/utils/constants'

export interface TreatmentCombo {
  name: string
  serviceIds: string[]
  savings: number // paisa
  tagline: string
}

const COMBO_MAP: TreatmentCombo[] = [
  {
    name: 'Full Body Glow',
    serviceIds: ['full-arms', 'full-legs', 'underarms'],
    savings: 30000,
    tagline: 'Head-to-toe smoothness in one session.',
  },
  {
    name: 'Facial Refresh',
    serviceIds: ['upper-lip', 'threading-eyebrows', 'facial-classic'],
    savings: 20000,
    tagline: 'Clean brows, smooth lip, glowing skin.',
  },
  {
    name: 'Party Ready',
    serviceIds: ['full-arms', 'underarms', 'upper-lip'],
    savings: 15000,
    tagline: 'Quick confidence boost before your event.',
  },
  {
    name: 'Complete Care',
    serviceIds: ['full-arms', 'full-legs', 'underarms', 'upper-lip', 'bikini-line'],
    savings: 50000,
    tagline: 'Our most comprehensive service bundle.',
  },
]

function resolveServices(ids: string[]): ServiceDef[] {
  return ids.map((id) => SERVICES.find((s) => s.id === id)).filter(Boolean) as ServiceDef[]
}

export function useTreatmentCombos() {
  const combos = computed(() =>
    COMBO_MAP.map((combo) => {
      const services = resolveServices(combo.serviceIds)
      const originalTotal = services.reduce((sum, s) => sum + s.price, 0)
      const totalDuration = services.reduce((sum, s) => sum + s.duration, 0)
      return {
        ...combo,
        services,
        originalTotal,
        comboPrice: originalTotal - combo.savings,
        totalDuration,
      }
    }).filter((c) => c.services.length === c.serviceIds.length)
  )

  return { combos }
}
