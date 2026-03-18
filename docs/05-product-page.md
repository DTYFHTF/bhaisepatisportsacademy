# 05 — Product Page

## Purpose

The product page is where the sale is won or lost. It must answer three questions instantly:
1. What is this exactly?
2. How much?
3. Will it fit me?

Everything else — fabric story, pairings, reviews — is secondary but present.

---

## Mobile Layout (375px)

```
┌─────────────────────────────┐
│ [←]         BSA    [🛒2] │  ← Minimal header
├─────────────────────────────┤
│                             │
│   [  ←  Product Image  →  ] │  ← Swipeable gallery, 4:5 ratio
│                             │
│          ○ ○ ● ○            │  ← Dot pagination
├─────────────────────────────┤
│ Field Jacket                │
│ NPR 5,500                   │
│ ——————————————————————————— │
│ COLOR: Olive ·  □ □ ■        │  ← Color swatches (other colors)
│                             │
│ SIZE                        │
│ [XS] [S] [■ M ■] [L] [XL]  │  ← M is selected + AI suggestion
│ ✦ Based on your body: M     │  ← AI recommendation
│       Not sure? → Size guide│
├─────────────────────────────┤
│                             │
│   [ — ADD TO CART — ]       │  ← Full-width, bottom thumb zone
│                             │
├─────────────────────────────┤
│ ▸ Fabric & Care             │  ← Accordion
│ ▸ Delivery & Returns        │  ← Accordion
│ ▸ The Making                │  ← Accordion (brand story)
├─────────────────────────────┤
│ COMPLETE THE LOOK           │
│ [Img] Essential Tee         │
│ [Img] Cargo Trousers        │
└─────────────────────────────┘
```

**Critical rule:** "Add to Cart" button must be visible without scrolling on a 375px screen. Position it just below the size selector.

---

## Desktop Layout (1280px)

```
┌─────────────────────────────────────────────────────┐
│ BSA          Shop  Wardrobe  Story  Track  🛒│
├──────────────────────┬──────────────────────────────┤
│                      │                              │
│  Main Image          │  Field Jacket                │
│  (4:5 ratio)         │  NPR 5,500                   │
│                      │                              │
│  ┌──┬──┬──┬──┐       │  Color: Olive                │
│  │  │  │  │  │       │  ○ Sand  ● Olive  ○ Black    │
│  └──┴──┴──┴──┘       │                              │
│  Thumbnail strip      │  Size                        │
│                      │  [XS][S][M✓][L][XL]          │
│                      │  ✦ We suggest M for your fit │
│                      │                              │
│                      │  [  ADD TO CART — NPR 5,500 ] │
│                      │                              │
│                      │  ▸ Fabric & Care              │
│                      │  ▸ Delivery & Returns         │
│                      │  ▸ The Making                 │
├──────────────────────┴──────────────────────────────┤
│                  COMPLETE THE LOOK                   │
│  [Img]               [Img]              [Img]        │
│  Field Jacket OLV    Essential Tee Sand  Cargo Trs   │
│  NPR 5,500 · In Cart NPR 2,200          NPR 4,200   │
├──────────────────────────────────────────────────────┤
│                  YOU MAY ALSO LIKE                   │
│  [Img]   [Img]   [Img]   [Img]                      │
└──────────────────────────────────────────────────────┘
```

---

## Image Gallery

### Behavior
- **Mobile:** Horizontal swipe gesture, full-width images, dot pagination
- **Desktop:** Scrollable vertical thumbnail strip (left side). Click thumbnail → main image changes
- **Zoom:** Tap on desktop → lightbox with keyboard navigation (←/→)
- **Pinch to zoom:** On mobile, native browser pinch zoom (not blocked)

### Required Shot Sequence
1. Front — clean background, product centered
2. Back — same setup
3. Detail — close-up of interesting feature (collar, pocket, fabric texture)
4. Worn (front) — model, lifestyle context
5. Worn (back) or side (optional)

### Image Spec
- Cloudinary CDN delivery  
- Responsive: `srcset` at 360, 480, 720, 960, 1280px
- Format: WebP with AVIF fallback
- Placeholder: BlurHash (renders instantly, 30 bytes encoded)
- Aspect ratio enforced at `4:5` (portrait) using `aspect-ratio: 4/5` CSS

---

## Color Selector

When a product has multiple colors:

```
Color: Olive
  [○ Sand] [● Olive] [○ Black]
```

- Each swatch is a circular button (24×24px)
- Filled with the actual color
- Active: ring indicator (2px border offset)
- Clicking a color: navigates to `/p/field-jacket-[color]` (separate URL per colorway)
- Pre-selects the same size if available in that colorway

---

## AI Size Suggestion

Shown as a subtle line below size selector:

```
✦ Based on your measurements: Medium
```

- Triggered only if measurements are saved in localStorage
- Click: expands inline: "Based on your 175cm / 70kg, medium fits relaxed. You could also try large for a more generous fit."
- "Not sure?" link → opens size finder modal

---

## Accordion Sections

### Fabric & Care

```
▸ Fabric & Care

  This jacket is made from 250g/m² cotton-twill. Wind-resistant
  without being stiff. Gets better with age.

  Care: Machine wash cold, hang dry. Do not tumble dry.
  Composition: 100% cotton.
  GSM: 250g/m²
  Origin: Kathmandu, Nepal
```

### Delivery & Returns

```
▸ Delivery & Returns

  Kathmandu Valley: 1–2 days · NPR 100
  Outside Valley: 3–5 days · NPR 150
  Free delivery on orders above NPR 7,000

  Exchange or return within 7 days of delivery.
  Item must be unworn with tags attached.
```

### The Making

```
▸ The Making

  BSA pieces are cut and sewn by hand at our workshop
  in Kathmandu. Each jacket takes approximately 4 hours
  to complete. We make small batches — which means
  occasional waits, but no waste.
```

---

## SEO Considerations

**Title tag:**
```
Field Jacket in Olive — Bhaisepati Sports Academy | NPR 5,500
```

**Meta description:**
```
{First 155 characters of fabricStory field}
```

**Structured data (JSON-LD):**
```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Field Jacket",
  "color": "Olive",
  "offers": {
    "@type": "Offer",
    "price": "5500",
    "priceCurrency": "NPR",
    "availability": "https://schema.org/InStock"
  },
  "image": ["...cloudinary url..."],
  "brand": {
    "@type": "Brand",
    "name": "Bhaisepati Sports Academy"
  }
}
```

---

## Out-of-Stock State

```
[XS] [S] [~~M~~] [L] [XL]
       ↑ size M is crossed out

"Size M is out of stock"
[ Notify me when M is back ]
```

The notify button opens a sheet asking for phone number only. No OTP required for restock alerts.

---

## Recently Viewed (Client-side)

Stored in localStorage. Shown at the bottom of product pages:

```
YOU RECENTLY VIEWED
[img] Essential Tee    [img] Cargo Trousers
      NPR 2,200               NPR 4,200
```

Max 4 items. FIFO order. Cleared after 7 days.

---

## Performance Budget for Product Page

| Metric | Target |
|---|---|
| LCP | < 1.8s |
| CLS | < 0.05 |
| FID / INP | < 100ms |
| Total JS (critical) | < 80KB gzipped |
| First meaningful paint | < 1.2s |

Key strategies:
- Product page is SSG (pre-rendered at build time)
- Images served from Cloudinary with correct `width`/`height` to prevent CLS
- No third-party scripts on product page load path
- Size selector, cart logic loaded with dynamic import
