# Content Strategy

> The site currently reads as *pages*; it should read as one story. Every page must answer, in order: who we are → why trust us → what you'll become → what to do next.

## Voice & Tone

- **Confident, not boastful.** "Professional courts. Serious coaching." — not "world-class amenities" (currently on the homepage; we cannot substantiate "world-class" and it reads template-like).
- **Direct, second person.** Talk to the player or the parent: "Your game starts here" (already good).
- **Local and specific.** Bhaisepati, Lalitpur, Nepali players, morning batches, dal bhat in the kitchen menu — specificity is the anti-template. Generic sports-stock phrasing is the enemy.
- **Bilingual future**: the audience is Nepali; plan for a Nepali (नेपाली) content track as a roadmap item, starting with the pages parents read (programs, FAQ, contact).

## The Narrative Spine

| Page | Story beat | Primary question answered | Primary CTA |
|---|---|---|---|
| Home | The hook: energy + proof | "Is this place real and good?" | Book a Court |
| Story/About | Origin: why BSA exists in Bhaisepati | "Who is behind this?" | Meet the programs |
| Programs | Transformation: beginner → competitor | "What will my child/I become?" | Enroll / Contact |
| Facilities | Evidence: real courts, real gym, real sauna | "What am I paying for?" | Book a Court |
| Book | Frictionless action | "Can I start now?" | Confirm booking |
| Kitchen | Belonging: train, eat, stay | "Is this a community?" | Visit / order |
| FAQ | Objection handling | "What about X?" | Contact |

**Missing beats today**: coaches (no names, faces, or credentials anywhere — the single most trust-building content a training academy can publish), real member outcomes (testimonials are seeded placeholders), pricing transparency copy (prices exist; context like "what's included, how to pay" is thin), and parent-specific reassurance (safety, supervision, age groups).

## Trust & Factual Accuracy — flagged claims

These are currently published and must be verified with the owner or removed (never invent facts):

- Stats band: "200+ Active Members", "5+ Expert Coaches", "6 Years Experience" — seeded demo values.
- Testimonials (Rajan Shrestha, Sita Maharjan, Bikash Tamang) — seeded demo content presented as real quotes with 5-star ratings.
- "Trained coaches for every skill level", "tournament-grade equipment" — verify.
- Homepage imagery: Unsplash stock presented as "Life at BSA" / "Visual Tour" — this is the most damaging authenticity gap. Replace with real photos even if imperfect; real > polished-fake.
- Shop products (Yonex racket etc.) are seeded demo inventory, currently purchasable.

## Rewrite Priorities (preserve facts, upgrade emotion)

1. **Hero subline**: fine today; consider grounding it — "Bhaisepati's home for badminton — 3 courts, a full gym, and coaching for every age."
2. **Section eyebrows**: replace generic ("World-Class Amenities", "Train With Us") with story beats ("Built for the game", "From first serve to first tournament").
3. **Programs**: each program gets a one-line *transformation promise* ("In 4 weeks you'll rally, score, and move like a player") above the feature list.
4. **About/Story**: currently the weakest link in the trust chain; needs the founder's badminton story, coach bios with photos, and the academy's promise to parents.
5. **CTAs**: standardize on two verbs sitewide — **Book** (court) and **Join** (program). Every page ends on one of them.

## Content Governance

- All content lives in the DB where it's admin-editable (programs, facilities, testimonials, stats, settings) — the duplicated hardcoded copies in `utils/constants.ts` should be removed as pages are touched (see [ARCHITECTURE.md](ARCHITECTURE.md) debt #4).
- Testimonials: keep the moderation flow; add a policy — real names with consent, role, month/year.
- Photography: build a shot list (courts in play, coaching moments, gym, sauna, kitchen, exterior signage) — one session unlocks the hero, gallery, facilities, OG image, and Google Business Profile.
