# 02 — User Experience Flows

This document defines the complete journey maps for every major customer interaction on the Bhaisepati Sports Academy platform.

---

## Flow 1: Discovery → Purchase (Standard)

The most common path: someone finds BSA, browses, and buys.

```
[Entry Point]
    │
    ├── Instagram post → /p/[product-slug]  (most common)
    ├── Direct URL → /
    ├── Google search → /shop or /p/[product-slug]
    └── WhatsApp link → /p/[product-slug]

    ↓

[Product Page]
    • View image gallery (swipe on mobile)
    • Read product name + price
    • Select size (with AI size suggestion)
    • Read fabric story (accordion)
    • See "Complete the look" (2 paired items)
    • Add to cart → Cart drawer slides in

    ↓

[Cart]
    • Review items
    • See order total + estimated delivery
    • Proceed to Checkout

    ↓

[Checkout — 3 Steps]
    Step 1: Contact + OTP
    Step 2: Delivery address
    Step 3: Payment method

    ↓

[Order Confirmed]
    • Order ID displayed (e.g., DH-2501-4821)
    • Confirmation SMS sent
    • Option to track order
    • Option to share the look
```

**Key decisions:**
- No account creation anywhere in this flow
- No email required — phone number only
- OTP adds minimal friction while enabling order tracking

---

## Flow 2: Returning Customer (Order Tracking)

```
[SMS with Tracking Link]
    ↓
/track?token=[jwt-token]
    ↓
[Order Status Page — no login]
    • Order timeline with animated milestones
    • Current status highlighted
    • Estimated delivery date
    • Option to exchange/return (within window)
    ↓
[Order delivered]
    • Prompt to review or style the look
    • Prompt to re-order or explore new arrivals
```

**Key decisions:**
- Token-based access: no OTP needed if clicking from SMS
- Token expires after 30 days
- Direct URL `/track` requires phone + OTP (fallback)

---

## Flow 3: AI Wardrobe Builder

```
[Entry Points]
    ├── "Build a look" button on product page
    ├── /wardrobe page (standalone)
    └── Homepage featured section

    ↓

[Step 1 — Select a Foundation Piece]
    • Choose from available products
    • System detects category (outerwear, top, bottom)

    ↓

[Step 2 — AI Suggests Combinations]
    • Shows 2–3 compatible pieces
    • Explains why they work: "The olive pairs with the sand tee — same warmth family"
    • Can swap individual pieces

    ↓

[Step 3 — See the Look]
    • Flat-lay style visualization with product images
    • Total cost of the look
    • "Add all to cart" or "Add selected"
    • Share link generated (unique /look/[hash] URL)

    ↓

[Optional: Save the Look]
    • User provides phone number
    • Look saved to their profile (OTP-less, linked to number)
    • Accessible at /wardrobe/[phone-hash]
```

---

## Flow 4: Smart Size Recommendation

```
[User lands on product page]
    ↓
    ← Sees size selector with "Not sure?" link

[Size Finder Modal]
    • Step 1: Input height and weight (or chest/waist measurements)
    • Step 2: Preferred fit? (Slim · Regular · Relaxed)
    • Step 3: How do you usually fit in other brands? (optional)

    ↓

[AI calculates recommendation]
    • Shows: "You're likely a Medium in this style"
    • Confidence indicator: "High confidence"
    • Brief explanation: "Based on the relaxed silhouette of this jacket"
    • "Remember this" option (saves to localStorage)

    ↓

[Size pre-selected on product page]
    • User can still override
    • Recommendation persists across all products in session
```

---

## Flow 5: Out-of-Stock / Restock Alert

```
[User lands on sold-out product]
    ↓
[Size selector shows: grayed-out sizes]
[CTA: "Notify me when [size] is back"]

    ↓

[Alert Modal]
    • Enter phone number
    • One-tap confirm (no OTP needed for alert registration)

    ↓

[When restocked — admin marks stock as back]
    • SMS sent: "Your size M Field Jacket is back at bsa.example.com/p/field-jacket"
    • Link goes directly to the product
    • First responders get priority reserve for 30 minutes (future feature)
```

---

## Flow 6: Instagram → Direct Purchase

Optimized for the most common acquisition channel.

```
[Instagram Story / Post]
    ↓
[Link in bio / Swipe up → /p/[product-slug]]

[Product page loads in < 1.5s]
    • Hero image matches the Instagram photo seen
    • Price visible immediately without scrolling
    • Size selector prominent
    • "Add to Cart" — one thumb reach on mobile

[Cart → Checkout in < 60 seconds]
    ↓

[Order confirmed]
    • Share button: "I just got the [product] from @bsa.wears"
    • Generates sharable image card with product photo
```

**Design requirement:** Product page on mobile must allow a user to add to cart using only their thumb — no pinching, scrolling past a fold, or two-hand interactions to complete a purchase.

---

## Flow 7: Exchange / Return

```
[Customer wants to exchange size]
    ↓
/track?token=[jwt] → Order page

"Exchange or return" button (visible for 7 days post-delivery)

    ↓

[Exchange Form]
    • Reason dropdown (size, quality, change of mind)
    • For size exchange: select new size
    • Confirm collection address (auto-filled from order)

    ↓

[Admin notified]
    • Exchange logged in admin panel
    • Customer receives SMS confirmation
    • Pickup scheduled (manual for now; automated in Phase 4)

    ↓

[New item dispatched]
    • New tracking generated
    • SMS with new tracking link
```

---

## Error & Edge Case Flows

### OTP Failure
```
[OTP doesn't arrive after 60s]
    → "Resend OTP" link appears
    → After 3 failed attempts: "Contact us on WhatsApp" button shown
```

### Payment Failure
```
[Khalti/eSewa payment fails]
    → Cart is NOT cleared
    → "Payment failed — try again or use a different method"
    → All items remain; user can retry or switch to COD
```

### Session Expiry
```
[User abandons cart for 24h]
    → Cart persists in localStorage (Zustand persist)
    → Products that went out of stock in cart: "No longer available" shown
    → Products still available: remain in cart
```

### Product Not Found
```
[/p/wrong-slug]
    → 404 page with:
        • "Page not found" (no explanation)
        • 3 product suggestions
        • Link to /shop
```

---

## Mobile UX Principles

1. **Thumb-first layout** — primary actions in bottom 40% of screen
2. **No horizontal scroll** — single column always
3. **Touch target minimum 48x48px** — all interactive elements
4. **Swipe gestures** — image gallery swipe, cart drawer dismiss-by-swipe
5. **Skeleton screens** — never show empty content; always show shape of what's loading
6. **Error messages at the field** — never just a toast for form errors

---

## Page Transition Philosophy

- Enter: `opacity 0→1` + `translateY 8px→0` — 200ms ease-out
- Exit: `opacity 1→0` — 150ms ease-in
- Cart drawer: `translateX 100%→0` — 280ms ease-in-out
- No page-level transitions that feel like "loading"; all under 300ms
