import type { ServiceDef } from '~/utils/constants'
import { SERVICES } from '~/utils/constants'

export interface QuizAnswers {
  skinType: 'normal' | 'sensitive' | 'oily' | 'dry'
  area: 'face' | 'body' | 'both'
  painTolerance: 'low' | 'medium' | 'high'
  priority: 'speed' | 'comfort' | 'precision'
}

export interface GlowResult {
  recommended: ServiceDef[]
  preferredWax: string
  tip: string
}

const WAX_MATRIX: Record<string, Record<string, string>> = {
  sensitive: { low: 'Rica', medium: 'Rica', high: 'Rica' },
  dry: { low: 'Rica', medium: 'Honey', high: 'Chocolate' },
  normal: { low: 'Honey', medium: 'Chocolate', high: 'Chocolate' },
  oily: { low: 'Honey', medium: 'Honey', high: 'Chocolate' },
}

const TIPS: Record<string, string> = {
  sensitive: 'Exfoliate gently 24 hours before. Avoid hot showers right after.',
  dry: 'Moisturise well in the days leading up to your appointment.',
  oily: 'A light cleanse before your visit gives best results.',
  normal: 'You\'re good to go! Just avoid sun exposure right after.',
}

export function useGlowQuiz() {
  const answers = ref<Partial<QuizAnswers>>({})
  const step = ref(0)
  const result = ref<GlowResult | null>(null)

  function setAnswer<K extends keyof QuizAnswers>(key: K, value: QuizAnswers[K]) {
    answers.value[key] = value
    if (step.value < 3) {
      step.value++
    }
  }

  function calculate() {
    const a = answers.value as QuizAnswers
    const preferredWax = WAX_MATRIX[a.skinType]?.[a.painTolerance] ?? 'Rica'

    let recommended = SERVICES.filter((s) => s.waxTypes.includes(preferredWax))

    if (a.area === 'face') {
      recommended = recommended.filter((s) =>
        s.category === 'FACIAL' || s.category === 'BROW' ||
        s.name.toLowerCase().includes('face') || s.name.toLowerCase().includes('upper lip')
      )
    } else if (a.area === 'body') {
      recommended = recommended.filter((s) =>
        s.category === 'WAXING' || s.category === 'BODY_CARE'
      )
    }

    if (a.priority === 'speed') {
      recommended.sort((a, b) => a.duration - b.duration)
    } else if (a.priority === 'comfort') {
      // Rica first for comfort
      recommended.sort((a, b) => {
        const aHasRica = a.waxTypes.includes('Rica') ? 0 : 1
        const bHasRica = b.waxTypes.includes('Rica') ? 0 : 1
        return aHasRica - bHasRica
      })
    }

    recommended = recommended.slice(0, 4)

    const tip = TIPS[a.skinType] ?? TIPS.normal

    result.value = { recommended, preferredWax, tip }
  }

  function reset() {
    answers.value = {}
    step.value = 0
    result.value = null
  }

  return { answers, step, result, setAnswer, calculate, reset }
}
