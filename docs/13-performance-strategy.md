# 13 — Performance Strategy

## Performance Targets

| Metric | Target | Priority |
|---|---|---|
| Lighthouse Performance | ≥ 95 | P0 |
| LCP (Largest Contentful Paint) | < 1.8s | P0 |
| FID / INP (Interaction) | < 100ms | P0 |
| CLS (Cumulative Layout Shift) | < 0.05 | P0 |
| First Contentful Paint | < 1.2s | P1 |
| Total Blocking Time | < 150ms | P1 |
| Critical JS (gzipped) | < 80KB | P1 |
| Time to Interactive | < 2.5s | P1 |

Measured against: 4G connection (40Mbps down, 10Mbps up, 20ms RTT), Moto G4 equivalent.

---

## Strategy 1: SSG / ISR for All Public Pages

```ts
// Product page — revalidate every 10 minutes
export const revalidate = 600

// Homepage — revalidate every hour
export const revalidate = 3600

// Static pages (story, sizing, faq)
export const dynamic = 'force-static'
```

Benefits:
- HTML served from Vercel CDN edge (Singapore for Nepal = ~40ms TTFB)
- No database queries on page load for static content
- Stock changes reflected within 10 minutes (product page ISR)

---

## Strategy 2: Cloudinary Image Pipeline

All product images delivered through Cloudinary with automatic optimization:

```ts
// src/lib/cloudinary.ts

export function getProductImageUrl(
  cloudinaryId: string,
  width: number,
  quality = 'auto'
): string {
  return `https://res.cloudinary.com/${process.env.NEXT_PUBLIC_CLOUDINARY_CLOUD_NAME}/image/upload/f_auto,q_${quality},w_${width}/${cloudinaryId}`
}
```

**Transformations applied automatically:**
- `f_auto` — serves WebP to modern browsers, JPEG to old browsers
- `q_auto` — Cloudinary selects optimal quality per image
- `w_[n]` — crops/resizes to exact display size

**Responsive `srcset`:**
```tsx
<Image
  src={getProductImageUrl(img.cloudinaryId, 800)}
  srcSet={`
    ${getProductImageUrl(img.cloudinaryId, 400)} 400w,
    ${getProductImageUrl(img.cloudinaryId, 600)} 600w,
    ${getProductImageUrl(img.cloudinaryId, 800)} 800w,
    ${getProductImageUrl(img.cloudinaryId, 1200)} 1200w
  `}
  sizes="(max-width: 480px) 100vw, (max-width: 1024px) 50vw, 600px"
  alt={img.altText}
  width={800}
  height={1000}
/>
```

**BlurHash placeholder:**
- Computed at upload time using `blurhash` npm package
- Stored in DB alongside Cloudinary ID
- Rendered as `background-image: url(data:image/png;base64,...)` — removes CLS

---

## Strategy 3: JavaScript Budget

Target: < 80KB total JS for first load (gzipped).

**Enforcement tools:**
```js
// next.config.js
module.exports = {
  webpack: (config, { isServer }) => {
    if (!isServer) {
      config.performance = {
        maxAssetSize: 100_000,  // 100KB per chunk
        maxEntrypointSize: 200_000,
        hints: 'error',  // Fail build if over budget
      }
    }
    return config
  }
}
```

**Lazy loading heavy components:**
```ts
// These components are not needed on initial render
const WardrobeBuilder = dynamic(() => import('@/components/ai/WardrobeBuilder'))
const SizeFinder = dynamic(() => import('@/components/ai/SizeFinder'))
const PaymentWidget = dynamic(() => import('@/components/checkout/PaymentWidget'))

// Fuse.js — only when search bar is focused
const initSearch = () => import('@/lib/search').then(m => m.init())
```

**Avoid heavy dependencies:**
- No Lodash (use native JS)
- No Moment.js (use `date-fns` or `Intl.DateTimeFormat`)
- No full icon libraries (cherry-pick from Lucide React)

---

## Strategy 4: Font Optimization

```tsx
// src/app/layout.tsx
import { Inter, Cormorant_Garamond } from 'next/font/google'

const inter = Inter({
  subsets: ['latin'],
  variable: '--font-sans',
  display: 'swap',
  preload: true,
})

const cormorant = Cormorant_Garamond({
  subsets: ['latin'],
  weight: ['300', '400'],
  variable: '--font-serif',
  display: 'swap',
  preload: false,  // Not used above the fold
})
```

`next/font` automatically:
- Hosts fonts on same domain (no external requests)
- Adds `font-display: swap`
- Inlines the critical CSS for the font

---

## Strategy 5: Cache Headers

```ts
// next.config.js
async headers() {
  return [
    {
      source: '/_next/static/(.*)',
      headers: [{ key: 'Cache-Control', value: 'public, max-age=31536000, immutable' }]
    },
    {
      source: '/api/products(.*)',
      headers: [{ key: 'Cache-Control', value: 's-maxage=600, stale-while-revalidate=60' }]
    },
    {
      source: '/(.*\.(jpg|jpeg|png|webp|avif|svg|ico))',
      headers: [{ key: 'Cache-Control', value: 'public, max-age=86400' }]
    },
  ]
}
```

Static assets: cached 1 year (immutable — hash in filename changes on update)
API responses: cached 10 minutes on CDN edge

---

## Strategy 6: Preloading Critical Resources

```tsx
// In layout.tsx <head>
<link rel="preconnect" href="https://res.cloudinary.com" />
<link rel="dns-prefetch" href="https://res.cloudinary.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
```

Hero image preloaded on homepage:
```tsx
<link
  rel="preload"
  as="image"
  href={heroImageUrl}
  imageSrcSet="..."
  imageSizes="100vw"
/>
```

---

## Strategy 7: No Layout Shift

All images must have explicit `width` and `height` attributes to prevent CLS:

```tsx
// Always specify dimensions
<Image
  src={url}
  width={800}
  height={1000}      // 4:5 ratio maintained — no CLS
  alt={altText}
  placeholder="blur"
  blurDataURL={blurHash}
/>
```

Font CLS prevention: `font-display: swap` with `size-adjust` where needed.

Skeleton screens for all async content — match exact dimensions of loaded content.

---

## Strategy 8: Prefetching Navigation

```tsx
// Next.js Link prefetches on hover — already covered
<Link href="/p/field-jacket-olive" prefetch={true}>
  ...
</Link>
```

Aggressive prefetch on homepage: top 6 products prefetched during idle time:

```ts
// After page interaction settles
if ('requestIdleCallback' in window) {
  requestIdleCallback(() => {
    topProducts.forEach(slug => router.prefetch(`/p/${slug}`))
  })
}
```

---

## Strategy 9: Critical CSS Inlining

Tailwind CSS purges unused classes at build time. Critical above-fold CSS is automatically inlined by Next.js.

Custom CSS variables (`tokens.css`) imported in `globals.css` — inlined in `<head>`.

---

## Strategy 10: Monitoring

Lighthouse CI in GitHub Actions:
```yaml
# .github/workflows/lighthouse.yml
- name: Run Lighthouse
  uses: treosh/lighthouse-ci-action@v10
  with:
    urls: |
      https://bsa.example.com/
      https://bsa.example.com/p/field-jacket-olive
      https://bsa.example.com/shop
    budgetPath: ./lighthouse-budget.json
    uploadArtifacts: true
```

Budget file:
```json
[{
  "path": "/",
  "timings": [
    { "metric": "interactive", "budget": 3000 },
    { "metric": "first-contentful-paint", "budget": 1200 }
  ],
  "resourceSizes": [
    { "resourceType": "script", "budget": 80 },
    { "resourceType": "total", "budget": 300 }
  ]
}]
```

Real-user monitoring: Vercel Speed Insights (free tier, enabled by default on Vercel).
