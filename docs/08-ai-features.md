# 08 — AI Features

## Overview

BSA's AI features are purposefully subtle. They don't announce themselves. They're embedded into the natural shopping flow, making the experience faster and smarter without feeling "tech company."

Three AI pillars:
1. **Smart Sizing** — never buy the wrong size
2. **Wardrobe Builder** — suggest complete looks from BSA inventory
3. **Style Affinity** — learn taste, surface what's relevant

---

## Feature 1: Smart Sizing

### Problem
People abandon purchases because they're unsure of sizing. Especially for outerwear (jackets) where fit matters most.

### Solution
A conversational size finder that gives a confident recommendation.

### Phase 1: Rule-based Heuristic Engine

Implemented in `utils/sizing.ts` (Nuxt auto-imported utility) — no ML, no API calls.

**Input schema:**
```ts
interface SizingInput {
  heightCm: number
  weightKg: number
  chestCm?: number
  waistCm?: number
  fitPreference: 'slim' | 'regular' | 'relaxed'
  category: 'JACKET' | 'TOP' | 'BOTTOM'
}
```

**Size chart (jackets):**

| Size | Chest (cm) | Shoulder (cm) | Height range |
|---|---|---|---|
| XS | 84–88 | 41–43 | 155–165 |
| S | 88–92 | 43–45 | 160–170 |
| M | 92–96 | 45–47 | 168–176 |
| L | 96–100 | 47–49 | 173–181 |
| XL | 100–106 | 49–52 | 178–185 |
| XXL | 106–114 | 52–56 | 182–190 |

**Algorithm:**

```ts
function getSizeRecommendation(input: SizingInput): SizingResult {
  // 1. Derive chest measurement from height/weight if not provided
  const chest = input.chestCm ?? estimateChest(input.heightCm, input.weightKg)

  // 2. Look up base size from chest measurement
  const baseSize = lookupSizeByChest(chest, input.category)

  // 3. Apply fit preference offset
  const adjustedSize = applyFitOffset(baseSize, input.fitPreference)
  // slim: go down 1 if between sizes; relaxed: go up 1

  return {
    recommendedSize: adjustedSize,
    confidence: input.chestCm ? 'high' : 'medium',
    explanation: buildExplanation(adjustedSize, chest, input.fitPreference)
  }
}
```

**Confidence levels:**
- `high`: direct chest measurement provided
- `medium`: derived from height/weight
- `low`: only height provided

**Explanation examples:**
- "Your chest measurement (94cm) fits best in Medium. With your relaxed preference, Medium gives you comfortable ease."
- "Based on your 175cm / 72kg, we estimate a medium chest. Medium should work well."

### Phase 3: ML Enhancement

When order data accumulates (500+ orders with exchanges):
- Train a lightweight model on: `[height, weight, chestCm, fitPreference] → actual_kept_size`
- Deploy as serverless function
- Use only aggregated data; no personal data stored in model

### Persistence

User measurements saved to `localStorage`:
```json
{
  "sizing": {
    "heightCm": 175,
    "weightKg": 70,
    "fitPreference": "regular",
    "savedAt": "2025-01-22T10:30:00Z"
  }
}
```

Cleared after 90 days. Never sent to any server unless user is verified via OTP.

---

## Feature 2: AI Wardrobe Builder

### Concept
"The Essential Five" — five core pieces from BSA that mix into 36 distinct outfits. Like a capsule wardrobe engine.

### Product Taxonomy

Every product has a `wardrobeRole` tag:

| Role | Example Products |
|---|---|
| `outerwear` | Field Jacket, Fleece |
| `midlayer` | Sweatshirt, Knit |
| `top` | Essential Tee, Longsleeve Tee |
| `bottom` | Cargo Trousers, Track Pants |
| `accessory` | Tote, Cap |

### Color Harmony Rules

Hard-coded in `utils/wardrobeEngine.ts` (Nuxt auto-imported utility):

```ts
const colorCompatibility: Record<string, string[]> = {
  'olive':     ['sand', 'charcoal', 'cream', 'black'],
  'black':     ['sand', 'olive', 'cream', 'white', 'all'],
  'charcoal':  ['sand', 'olive', 'cream', 'white'],
  'sand':      ['olive', 'charcoal', 'black', 'brown'],
  'brown':     ['sand', 'cream', 'olive', 'black'],
  'cream':     ['charcoal', 'brown', 'olive', 'black'],
}
```

### Look Generation Algorithm

```ts
function generateLook(anchor: Product, inventory: Product[]): Look[] {
  const candidates = inventory.filter(p =>
    p.id !== anchor.id &&
    p.isActive &&
    isColorCompatible(anchor.colorName, p.colorName) &&
    isRoleCompatible(anchor.wardrobeRole, p.wardrobeRole)
  )

  // Build complete look combinations
  const looks = buildLookCombinations(anchor, candidates)

  // Score by: color harmony + variety of roles + in-stock availability
  return looks.sort((a, b) => b.score - a.score).slice(0, 3)
}
```

**Role compatibility rules:**
- A look needs at minimum: 1 top + 1 bottom
- A look can have 1 outerwear
- No two items of the same role (except tops can be paired with a midlayer)

### UI: Wardrobe Builder

```
┌─────────────────────────────────────────────┐
│  Build Your Look                            │
├─────────────────────────────────────────────┤
│                                             │
│  Start with: Field Jacket (Olive)           │
│  ┌──────────────────────────────────────┐   │
│  │ ┌────┐  ┌────┐  ┌────┐              │   │
│  │ │ JA │  │ TE │  │ TR │              │   │
│  │ │Oli │  │San │  │Cha │              │   │
│  │ └────┘  └────┘  └────┘              │   │
│  └──────────────────────────────────────┘   │
│  "A complete look for the season"           │
│                                             │
│  Total: NPR 11,900                          │
│  [  Add all to cart  ]  [ See other looks ] │
│                                             │
│  ─────────────────────────────────────────  │
│  Swap pieces:                               │
│  Bottom: [Cargo Trousers ▼] [Track Pants]  │
│                                             │
└─────────────────────────────────────────────┘
```

### Shareable Looks

Every generated look has a unique `/look/[hash]` URL:

```
/look/x7kPzR2m
```

Hash is: `sha256(product-ids-sorted + generation-timestamp).slice(0, 8)`

The look page shows:
- 3 product images side by side
- Brief style note (template-generated)
- "Shop this look" CTA
- Open Graph meta for rich Instagram/WhatsApp previews

---

## Feature 3: Style Affinity Engine

### Purpose
Surface the right products to the right visitor. As users browse, learn implicit preferences and rank shop page results accordingly.

### Phase 1: Session-based (No ML)

Track in `sessionStorage`:
```ts
interface BrowsingSession {
  viewedCategories: string[]     // ['JACKET', 'JACKET', 'TOP']
  viewedColors: string[]         // ['olive', 'black', 'olive']
  addedToCart: string[]          // product slugs
  timeOnProduct: Record<string, number>  // slug → seconds
}
```

On `/shop` page load:
- Calculate category affinity (most-viewed category gets weight boost)
- Calculate color affinity (most-viewed color gets weight boost)
- Re-sort product grid: affinity items first, then default (new arrivals)

**No server-side storage in Phase 1.** Entirely client-side.

### Phase 3: Persistent Recommendations

When user has verified phone:
- Send anonymized preference data to server
- Store as `UserPreference` (phone_hash + vector of weights)
- Homepage "For You" section uses stored preferences

### Phase 5: Style Explanations (Zero-Budget Approach)

No paid AI subscription at launch. Three options in order of complexity:

#### Option A — Hand-crafted templates (recommended for launch)

Pre-write 30 style explanation templates keyed by color pair and category combination:

```php
// app/Services/StyleExplanationService.php

class StyleExplanationService
{
    private array $templates = [
        'olive+sand+bottom'     => 'Earth tones in different weights — the jacket anchors the look.',
        'black+cream+top'       => 'High contrast, zero effort. Classic for a reason.',
        'charcoal+sand+bottom'  => 'Muted tones that shift from café to trail without trying.',
        // ... 27 more
    ];

    public function explain(array $productColors, string $anchorRole): string
    {
        $key = implode('+', array_slice($productColors, 0, 2)) . '+' . $anchorRole;
        return $this->templates[$key]
            ?? 'Pieces that share a palette and a point of view.';
    }
}
```

Pros: instant, zero cost, zero API dependency, perfectly on-brand tone.  
Cons: limited variety (30 templates covers most combinations at launch).

#### Option B — Groq free tier (when you want real LLM)

Groq offers **14,400 requests/day free** with Llama 3.1 8B. API is OpenAI-compatible:

```php
// app/Services/StyleExplanationService.php

public function explainWithLlm(string $products): string
{
    $cached = Cache::get('style:' . md5($products));
    if ($cached) return $cached;

    $response = Http::withToken(config('services.groq.api_key'))
        ->post('https://api.groq.com/openai/v1/chat/completions', [
            'model'    => 'llama-3.1-8b-instant',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a minimal fashion stylist for a Nepali brand. Describe why these pieces work together in one terse, confident sentence.'],
                ['role' => 'user',   'content' => $products],
            ],
            'max_tokens' => 60,
        ]);

    $explanation = $response->json('choices.0.message.content');

    // Cache by look composition — same look always gets same explanation
    Cache::put('style:' . md5($products), $explanation, now()->addDays(30));

    return $explanation;
}
```

Sign up at `console.groq.com` — free tier, no credit card needed.

#### Option C — Google Gemini free tier

1,500 requests/day free with `gemini-1.5-flash`. Same caching strategy applies.

---

**Launch recommendation:** Ship with Option A (templates). Add Option B (Groq) once the wardrobe builder is used enough to justify the integration effort. The tone of hand-crafted copy will likely be better than LLM output anyway.

Used for:
- Wardrobe Builder "why this works" explanation card
- Shareable look page caption
- Homepage "Styled for you" section

---

## AI Feature Metrics

Track these in Plausible custom events:

| Event | What it measures |
|---|---|
| `size_finder_opened` | Sizing feature discovery |
| `size_finder_completed` | Sizing recommendation given |
| `size_finder_applied` | User applied suggestion to product |
| `wardrobe_builder_opened` | Wardrobe feature discovery |
| `look_added_to_cart` | Wardrobe Builder conversion |
| `look_shared` | Virality of look feature |
| `restock_alert_set` | Demand signal strength |
| `style_affinity_reorder` | Did affinity sorting improve CTR? |

---

## Data & Privacy

- **No biometric data stored** — height/weight only in localStorage
- **No personal data in ML models** — only aggregate patterns
- **Phone hash only** — never plaintext phone stored in preference tables
- **User can clear all data** — "Clear my data" option in footer/settings
- **GDPR-adjacent approach** — not legally required in Nepal but good practice
