<script setup lang="ts">
import type { Product } from '~/types/product'

// Color compatibility rules (hardcoded, per docs)
const colorCompatibility: Record<string, string[]> = {
  olive: ['sand', 'charcoal', 'cream', 'black'],
  black: ['sand', 'olive', 'cream', 'white'],
  charcoal: ['sand', 'olive', 'cream', 'white'],
  sand: ['olive', 'charcoal', 'black', 'brown'],
  brown: ['sand', 'cream', 'olive', 'black'],
  cream: ['charcoal', 'brown', 'olive', 'black'],
}

interface Look {
  id: string
  products: Product[]
  score: number
}

interface Props {
  products: Product[]
  anchor?: Product
}

const props = defineProps<Props>()
const emit = defineEmits<{
  addToCart: [products: Product[]]
}>()

const selectedAnchor = ref<Product | null>(props.anchor || null)
const generatedLooks = ref<Look[]>([])

function isColorCompatible(color1: string, color2: string): boolean {
  const c1 = color1.toLowerCase()
  const c2 = color2.toLowerCase()
  return colorCompatibility[c1]?.includes(c2) || colorCompatibility[c2]?.includes(c1) || false
}

function generateLooks(anchor: Product): Look[] {
  const others = props.products.filter((p) => p.id !== anchor.id && p.isActive)

  // Group by role
  const byRole: Record<string, Product[]> = {}
  for (const p of others) {
    const role = p.wardrobeRole || p.category.toLowerCase()
    if (!byRole[role]) byRole[role] = []
    byRole[role].push(p)
  }

  const looks: Look[] = []
  const anchorRole = anchor.wardrobeRole || anchor.category.toLowerCase()

  // Try combinations: need 1 top + 1 bottom minimum
  const tops = [...(byRole['top'] || []), ...(byRole['midlayer'] || [])]
  const bottoms = byRole['bottom'] || []
  const outerwear = byRole['outerwear'] || []

  const candidates = anchorRole === 'bottom' ? tops : bottoms

  for (const companion of candidates) {
    if (!isColorCompatible(anchor.colorName, companion.colorName)) continue

    const lookProducts = [anchor, companion]
    let score = 1

    // Optionally add outerwear
    if (anchorRole !== 'outerwear') {
      const jacket = outerwear.find(
        (o) =>
          isColorCompatible(o.colorName, anchor.colorName) &&
          isColorCompatible(o.colorName, companion.colorName),
      )
      if (jacket) {
        lookProducts.push(jacket)
        score += 0.5
      }
    }

    looks.push({
      id: lookProducts.map((p) => p.id).sort().join('-'),
      products: lookProducts,
      score,
    })
  }

  return looks.sort((a, b) => b.score - a.score).slice(0, 3)
}

function selectAnchor(product: Product) {
  selectedAnchor.value = product
  generatedLooks.value = generateLooks(product)
}

function addLookToCart(look: Look) {
  emit('addToCart', look.products)
}

// Auto-generate if anchor provided
if (props.anchor) {
  generatedLooks.value = generateLooks(props.anchor)
}
</script>

<template>
  <div class="space-y-6">
    <h2 class="text-heading-lg">Build Your Wardrobe</h2>
    <p class="text-sm text-ink-muted">Select a foundation piece. We'll suggest combinations that work.</p>

    <!-- Anchor selector -->
    <div v-if="!selectedAnchor" class="grid grid-cols-3 gap-3">
      <button
        v-for="product in products.filter((p) => p.isActive).slice(0, 9)"
        :key="product.id"
        class="text-left"
        @click="selectAnchor(product)"
      >
        <div class="aspect-[3/4] bg-surface overflow-hidden">
          <img
            v-if="product.images[0]"
            :src="product.images[0].url"
            :alt="product.name"
            class="h-full w-full object-cover hover:scale-[1.02] transition-transform"
          />
        </div>
        <p class="mt-1 text-xs">{{ product.name }}</p>
        <p class="text-xs text-ink-muted">{{ product.colorName }}</p>
      </button>
    </div>

    <!-- Generated looks -->
    <template v-if="selectedAnchor && generatedLooks.length > 0">
      <div class="flex items-center justify-between">
        <p class="text-sm text-ink-muted">
          Looks with <strong>{{ selectedAnchor.name }}</strong> in {{ selectedAnchor.colorName }}
        </p>
        <button
          class="text-xs text-ink-muted underline"
          @click="selectedAnchor = null; generatedLooks = []"
        >
          Change
        </button>
      </div>

      <div
        v-for="look in generatedLooks"
        :key="look.id"
        class="border border-border p-4 space-y-3"
      >
        <div class="flex gap-3">
          <div
            v-for="product in look.products"
            :key="product.id"
            class="flex-1"
          >
            <div class="aspect-[3/4] bg-surface overflow-hidden">
              <img
                v-if="product.images[0]"
                :src="product.images[0].url"
                :alt="product.name"
                class="h-full w-full object-cover"
              />
            </div>
            <p class="mt-1 text-xs">{{ product.name }}</p>
            <p class="text-xs text-ink-muted">{{ formatPrice(product.price) }}</p>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <p class="text-sm font-medium">
            {{ formatPrice(look.products.reduce((sum, p) => sum + p.price, 0)) }}
          </p>
          <UiAppButton size="sm" @click="addLookToCart(look)">
            Add All to Cart
          </UiAppButton>
        </div>
      </div>
    </template>

    <!-- No looks found -->
    <p
      v-if="selectedAnchor && generatedLooks.length === 0"
      class="text-sm text-ink-muted text-center py-8"
    >
      No matching combinations found. Try a different piece.
    </p>
  </div>
</template>
