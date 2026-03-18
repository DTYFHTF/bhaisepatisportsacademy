import { ref, computed } from 'vue'

interface SizingInput {
  heightCm: number
  weightKg: number
  chestCm?: number
  waistCm?: number
  fitPreference: 'slim' | 'regular' | 'relaxed'
  category: 'JACKET' | 'TOP' | 'BOTTOM'
}

interface SizingResult {
  size: string
  confidence: 'high' | 'medium' | 'low'
  explanation: string
}

// Size charts (chest cm ranges)
const SIZE_CHARTS = {
  JACKET: [
    { size: 'XS', minChest: 84, maxChest: 88 },
    { size: 'S', minChest: 88, maxChest: 92 },
    { size: 'M', minChest: 92, maxChest: 96 },
    { size: 'L', minChest: 96, maxChest: 100 },
    { size: 'XL', minChest: 100, maxChest: 106 },
    { size: 'XXL', minChest: 106, maxChest: 114 },
  ],
  TOP: [
    { size: 'XS', minChest: 82, maxChest: 86 },
    { size: 'S', minChest: 86, maxChest: 90 },
    { size: 'M', minChest: 90, maxChest: 94 },
    { size: 'L', minChest: 94, maxChest: 100 },
    { size: 'XL', minChest: 100, maxChest: 106 },
    { size: 'XXL', minChest: 106, maxChest: 112 },
  ],
  BOTTOM: [
    { size: 'XS', minChest: 66, maxChest: 70 },
    { size: 'S', minChest: 70, maxChest: 76 },
    { size: 'M', minChest: 76, maxChest: 82 },
    { size: 'L', minChest: 82, maxChest: 88 },
    { size: 'XL', minChest: 88, maxChest: 96 },
    { size: 'XXL', minChest: 96, maxChest: 104 },
  ],
} as const

const STORAGE_KEY = 'pp_sizing'

export function useSizing() {
  const result = ref<SizingResult | null>(null)
  const saved = ref<SizingInput | null>(null)

  function load() {
    if (typeof window === 'undefined') return
    try {
      const raw = localStorage.getItem(STORAGE_KEY)
      if (raw) saved.value = JSON.parse(raw)
    } catch {
      saved.value = null
    }
  }

  function deriveChestFromHeightWeight(height: number, weight: number): number {
    // Approximate chest circumference from height and weight
    return Math.round(weight * 0.55 + height * 0.15 + 20)
  }

  function recommend(input: SizingInput): SizingResult {
    const chart = SIZE_CHARTS[input.category]
    let chest: number
    let confidence: 'high' | 'medium' | 'low'

    if (input.category === 'BOTTOM' && input.waistCm) {
      chest = input.waistCm
      confidence = 'high'
    } else if (input.chestCm) {
      chest = input.chestCm
      confidence = 'high'
    } else if (input.heightCm && input.weightKg) {
      chest = deriveChestFromHeightWeight(input.heightCm, input.weightKg)
      confidence = 'medium'
    } else {
      chest = deriveChestFromHeightWeight(input.heightCm, 65)
      confidence = 'low'
    }

    // Find base size
    let baseIdx = chart.findIndex((s) => chest >= s.minChest && chest < s.maxChest)
    if (baseIdx === -1) {
      baseIdx = chest < chart[0].minChest ? 0 : chart.length - 1
    }

    // Apply fit preference offset
    let finalIdx = baseIdx
    if (input.fitPreference === 'slim') finalIdx = Math.max(0, baseIdx - 1)
    if (input.fitPreference === 'relaxed') finalIdx = Math.min(chart.length - 1, baseIdx + 1)

    const size = chart[finalIdx].size
    const measurementType = input.chestCm ? 'your chest measurement' : 'your height and weight'

    const explanation =
      confidence === 'high'
        ? `Based on ${measurementType}, ${size} gives you a ${input.fitPreference} fit.`
        : confidence === 'medium'
          ? `Estimated from your height and weight. ${size} should work for a ${input.fitPreference} fit. Providing chest measurement improves accuracy.`
          : `Based on limited data. We recommend ${size} but trying on is best.`

    const sizing: SizingResult = { size, confidence, explanation }
    result.value = sizing

    // Save to localStorage
    if (typeof window !== 'undefined') {
      saved.value = input
      localStorage.setItem(STORAGE_KEY, JSON.stringify(input))
    }

    return sizing
  }

  function getSavedRecommendation(category: 'JACKET' | 'TOP' | 'BOTTOM'): SizingResult | null {
    load()
    if (!saved.value) return null
    return recommend({ ...saved.value, category })
  }

  function clear() {
    result.value = null
    saved.value = null
    if (typeof window !== 'undefined') {
      localStorage.removeItem(STORAGE_KEY)
    }
  }

  return { result, saved, recommend, getSavedRecommendation, clear, load }
}
