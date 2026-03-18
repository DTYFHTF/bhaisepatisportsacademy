# 15 — Unique Experiences

## What Makes BSA Different

These are the brand-differentiated experiences that separate the BSA website from a generic sports facility page. Each one should feel considered, not bolted on.

---

## 1. Court-Feel Page Loading

**Where:** Homepage hero, initial page load

When the homepage loads, the hero content fades in with a fast, directional motion — like a shuttlecock cutting across the court. No spinner, no progress bar. The animation is a single sweep: 200ms, done.

This is the brand in motion: fast, precise, intentional.

**Technical implementation:**
```ts
// @vueuse/motion fade-in-up on hero section
// 200ms duration, ease-out — no bounce
const { motion } = useMotion(heroRef, {
  initial: { opacity: 0, y: 20 },
  enter: { opacity: 1, y: 0, transition: { duration: 200 } },
})
```

---

## 2. Program Match Quiz

**Where:** `/programs` page, sidebar CTA

A 3-step quiz that helps visitors find their right program:
1. **Age group** (Under 12 / 12–18 / 18+ / Adult)
2. **Experience level** (Never played / Recreational / Competitive)
3. **Goal** (Have fun / Improve skills / Compete in tournaments)

Result: A specific program recommendation with a direct enrollment CTA.

Deterministic logic — no AI. Defined in `useProgramMatch.ts`.

---

## 3. Live Schedule Preview

**Where:** Homepage widget, `/programs` page

A compact weekly schedule shows upcoming sessions for the current week. Pulls from the `/api/schedule` endpoint (if available) or falls back to a static timetable.

- Color-coded by program level (Foundation / Intermediate / Advanced)
- Shows court number and coach name
- One-tap to enroll in a session

---

## 4. Enrollment Journey — Progress Indicator

**Where:** Enrollment flow (`/enroll`)

The enrollment form is split into clear steps with a persistent progress bar:
1. Select program
2. Enter contact details (phone OTP verification)
3. Review & confirm
4. Payment (Khalti / eSewa / Walk-in)

Each step is fast to complete. The progress bar never goes backwards without user action.

---

## 5. BSA Member Card (Post-Enrollment)

**Where:** Order/enrollment confirmation page, `/account`

After successful enrollment, the student receives a digital member card showing:
- Student name
- Enrollment ID (`BSA-YYMM-XXXX` format)
- Program name and level
- Valid dates
- QR code for check-in (future)

This is a brand moment. The student just committed — give them something that feels real.

---

## 6. Court Booking (Future)

**Where:** `/courts`

Direct court time booking for members. Time slots, court selection, duration.
Blocked until member enrollment is confirmed.

*Planned for Phase 2.*

---

## 7. Academy Story — About Page

**Where:** `/about`

Not a corporate about page. A short, direct story from the founder:

> *We built BSA because we believe everyone in Bhaisepati deserves access to a proper court and real coaching.*
>
> *Badminton is our game. We play it seriously, and we want to teach it seriously.*
>
> *Come train with us.*

No fluff. No timelines. No mission statement jargon.

---

## 8. Facility Photography — First-Person Feel

**Where:** Homepage facility section, `/facilities`

Photography is shot from a player's perspective — racket in hand, shuttlecock mid-air, court markings under foot. Visitors should feel like they're already there.

Technical:
- All photos delivered as WebP
- Cloudinary handles CDN delivery and responsive sizing
- Hero images minimum 1920px wide

---

## 9. Performance Transparency

**Where:** Site-wide

No fake reviews. No inflated numbers. BSA earns trust through:
- Real coach profiles with actual credentials
- Honest program descriptions (this is for beginners / this is intense)
- Clear pricing — no hidden fees

---

## 10. Mobile-First Enrollment

**Where:** Enrollment flow

The entire enrollment can be completed on a phone in under 90 seconds:
- Phone number → OTP → program selection → payment
- Supports Khalti and eSewa for instant digital payment
- Walk-in payment option for families who prefer cash

This is the most important flow on the site. It must work perfectly on low-end Android devices on Nepali mobile data.
