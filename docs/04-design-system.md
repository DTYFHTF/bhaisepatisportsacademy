# 04 — Design System

## Design Tokens

These CSS custom properties are the single source of truth for all visual decisions. Defined in `src/styles/tokens.css` and referenced via Tailwind config.

### Colors

```css
:root {
  /* Base */
  --color-canvas: #F5F2ED;
  --color-surface: #FAFAF8;
  --color-border: #E8E3DB;

  /* Text */
  --color-ink: #1A1A1A;
  --color-ink-muted: #767676;
  --color-ink-faint: #A8A8A8;

  /* Brand */
  --color-accent: #A69885;
  --color-accent-hover: #8C7F6E;

  /* Feedback */
  --color-success: #4A6741;
  --color-error: #C0392B;
  --color-warning: #B8860B;

  /* Overlays */
  --color-overlay: rgba(26, 26, 26, 0.5);
  --color-overlay-light: rgba(26, 26, 26, 0.08);
}
```

### Typography

```css
:root {
  --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
  --font-serif: 'Cormorant Garamond', Georgia, serif;
  --font-mono: 'JetBrains Mono', 'Courier New', monospace;

  /* Scale */
  --text-2xs: 0.6875rem;   /* 11px */
  --text-xs:  0.75rem;     /* 12px */
  --text-sm:  0.875rem;    /* 14px */
  --text-base: 1rem;       /* 16px */
  --text-lg:  1.125rem;    /* 18px */
  --text-xl:  1.25rem;     /* 20px */
  --text-2xl: 1.5rem;      /* 24px */
  --text-3xl: 2rem;        /* 32px */
  --text-4xl: 2.75rem;     /* 44px */
  --text-5xl: 4rem;        /* 64px */
}
```

### Spacing

Uses a 4px base unit.

```css
:root {
  --space-1:  0.25rem;   /* 4px */
  --space-2:  0.5rem;    /* 8px */
  --space-3:  0.75rem;   /* 12px */
  --space-4:  1rem;      /* 16px */
  --space-5:  1.25rem;   /* 20px */
  --space-6:  1.5rem;    /* 24px */
  --space-8:  2rem;      /* 32px */
  --space-10: 2.5rem;    /* 40px */
  --space-12: 3rem;      /* 48px */
  --space-16: 4rem;      /* 64px */
  --space-20: 5rem;      /* 80px */
  --space-24: 6rem;      /* 96px */
}
```

### Borders & Radii

```css
:root {
  --radius-sm: 2px;
  --radius-md: 4px;
  --radius-lg: 8px;
  --radius-xl: 12px;
  --radius-full: 9999px;

  --border-thin: 1px;
  --border-base: 1.5px;
  --border-thick: 2px;
}
```

### Motion

```css
:root {
  --ease-out: cubic-bezier(0.0, 0.0, 0.2, 1);
  --ease-in: cubic-bezier(0.4, 0.0, 1, 1);
  --ease-in-out: cubic-bezier(0.4, 0.0, 0.2, 1);
  --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);

  --duration-fast: 120ms;
  --duration-base: 200ms;
  --duration-slow: 320ms;
  --duration-enter: 280ms;
  --duration-exit: 180ms;
}
```

---

## Component Specifications

### Button

**Variants:** `primary` | `secondary` | `ghost` | `destructive`
**Sizes:** `sm` | `md` | `lg`

```tsx
// Usage
<Button variant="primary" size="md">Add to Cart</Button>
<Button variant="secondary" size="sm">Save to Wardrobe</Button>
<Button variant="ghost">Continue Shopping</Button>
```

**Primary Button:**
- Background: `var(--color-ink)` (#1A1A1A)
- Text: `#FFFFFF`
- Hover: background `#333333`
- Active: scale `0.97`
- Disabled: opacity `0.4`, cursor `not-allowed`
- Border-radius: `var(--radius-md)` (4px)
- Padding: `12px 24px` (md), `8px 16px` (sm), `16px 32px` (lg)
- Letter-spacing: `0.05em`
- Text: uppercase, `--text-xs`

**Secondary Button:**
- Background: transparent
- Border: `1.5px solid var(--color-ink)`
- Text: `var(--color-ink)`
- Hover: background `var(--color-overlay-light)`

**Ghost Button:**
- No border, no background
- Text: `var(--color-ink-muted)`
- Hover: text `var(--color-ink)`
- Underline on hover (text-decoration)

---

### ProductCard

Displays product in grid and list views.

**Card dimensions:** 3:4 aspect ratio image (e.g., 360×480px)

**Grid card structure:**
```
┌─────────────────────┐
│                     │
│   Product Image     │  3:4 ratio
│   (with hover)      │
│                     │
│  [Quick add: S M L] │  ← appears on hover (desktop)
├─────────────────────┤
│ Product Name        │  --text-sm, --color-ink
│ NPR 5,500           │  --text-sm, --color-ink-muted
└─────────────────────┘
```

**Hover state (desktop):**
- Image: slight scale `1.02`, 300ms ease
- Quick-add bar: slides up from bottom (`translateY 100%→0`)

**Mobile:** No hover. Tap → product page. Quick-add not on mobile.

**"New" badge:**
- Position: top-left corner, 8px inset
- Style: `--text-2xs`, letter-spaced, uppercase, `#1A1A1A` on `#F5F2ED`, 1px border

---

### SizeSelector

```
[XS]  [S]  [M✓]  [L]  [XL]
```

- Available: outlined box, `--color-ink` border, `--color-ink` text
- Selected: filled `--color-ink` background, white text
- Out of stock: text with strikethrough, `--color-ink-faint` color, cursor not-allowed
- AI-suggested: subtle underline dot beneath the size label

---

### CartDrawer

Slides in from the right. Full-height on mobile, max-width 480px on desktop.

```
┌────────────────────────┐
│ Cart (2)         [×]   │
├────────────────────────┤
│ [img] Field Jacket     │
│       Olive · M        │
│       NPR 5,500   [−+] │
├────────────────────────┤
│ [img] Essential Tee    │
│       Sand · M         │
│       NPR 2,200   [−+] │
├────────────────────────┤
│ ─────────────────────  │
│ Subtotal    NPR 7,700  │
│                        │
│ [  Proceed to Checkout ] │
│ [    Continue Shopping ] │
└────────────────────────┘
```

- Overlay: `var(--color-overlay)` with tap-to-close
- Animation: `translateX(100%) → translateX(0)`, 280ms ease-in-out

---

### Toast / Notification

Position: Bottom-center on mobile, bottom-right on desktop.

```
✓  Added to cart
✕  This size is out of stock
!  OTP sent to 98XXXXXXXX
```

- Duration: 3000ms (success), persists (error, requires dismiss)
- Animation: slides up from below, fades out
- Maximum 2 toasts stacked

---

### Input / FormField

```tsx
<FormField
  label="Phone Number"
  type="tel"
  placeholder="98XXXXXXXX"
  helper="We'll send your OTP to this number"
  error="Invalid phone number"
/>
```

**States:** Default → Focus → Filled → Error

- Default: `var(--border-base)` border, `var(--color-border)` color
- Focus: border `var(--color-ink)`, no outline ring (custom focus style)
- Error: border `var(--color-error)`, helper text in error color
- Label: always above the field (never floating — floating labels cause accessibility issues)

---

### Modal / Sheet

- Desktop: centered modal with backdrop
- Mobile: bottom sheet (slides up, `border-radius` top-left/right `--radius-xl`)
- Dismiss: backdrop click, swipe down (mobile), `Esc` key
- Focus trap: yes — keyboard accessibility

---

### OTPInput

```
[ _ ] [ _ ] [ _ ] [ _ ] [ _ ] [ _ ]
```

- 6 individual digit inputs
- Auto-advances on input
- Backspace navigates to previous
- Paste fills all at once
- On complete: auto-submits (300ms delay for UX)

---

## Animation Reference

Defined as Framer Motion `variants` — imported from `src/lib/animations.ts`:

```ts
export const fadeIn = {
  hidden: { opacity: 0, y: 8 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.2, ease: 'easeOut' } }
}

export const slideInRight = {
  hidden: { x: '100%' },
  visible: { x: 0, transition: { duration: 0.28, ease: [0.4, 0.0, 0.2, 1] } },
  exit: { x: '100%', transition: { duration: 0.18 } }
}

export const scaleIn = {
  hidden: { opacity: 0, scale: 0.96 },
  visible: { opacity: 1, scale: 1, transition: { duration: 0.2, ease: 'easeOut' } }
}

export const staggerChildren = {
  visible: {
    transition: { staggerChildren: 0.06, delayChildren: 0.1 }
  }
}
```

---

## Accessibility Standards

- **WCAG 2.1 AA compliance** minimum
- Color contrast ratio: ≥ 4.5:1 for body text, ≥ 3:1 for large text
- All interactive elements keyboard-navigable
- `aria-label` on icon-only buttons
- `alt` text on all images (product name + context)
- Focus indicators: custom — 2px solid `var(--color-accent)` offset 2px
- Form errors linked with `aria-describedby`
- Cart count announced with `aria-live="polite"`

---

## Responsive Breakpoints

```ts
// In tailwind.config.ts
screens: {
  'sm': '480px',   // large phone
  'md': '768px',   // tablet portrait
  'lg': '1024px',  // tablet landscape / laptop
  'xl': '1280px',  // desktop
  '2xl': '1440px', // wide desktop
}
```

Design approach: **mobile first**. Base styles target 375px viewport (iPhone SE). All breakpoints add to the base.
