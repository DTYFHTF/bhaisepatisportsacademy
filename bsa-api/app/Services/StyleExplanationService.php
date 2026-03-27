<?php

namespace App\Services;

class StyleExplanationService
{
    private array $templates = [
        'olive+sand+bottom'       => 'Earth tones in different weights - the jacket anchors the look.',
        'olive+charcoal+bottom'   => 'Dark base, muted top. Everything finds its place.',
        'olive+cream+top'         => 'Warm neutral layers. The olive sits right at the center.',
        'olive+black+bottom'      => 'An everyday palette that wears in, not out.',
        'black+cream+top'         => 'High contrast, zero effort. Classic for a reason.',
        'black+sand+bottom'       => 'Sand softens the black. The pairing does the work.',
        'black+olive+bottom'      => 'A clean foundation with enough warmth to feel human.',
        'charcoal+sand+bottom'    => 'Muted tones that shift from café to trail without trying.',
        'charcoal+cream+top'      => 'Quiet warmth. Cream lifts what charcoal grounds.',
        'charcoal+olive+bottom'   => 'Two grounded neutrals that don't compete.',
        'sand+olive+bottom'       => 'A natural palette - warm and unfussy.',
        'sand+charcoal+top'       => 'Light over dark. Clean without being stark.',
        'sand+black+bottom'       => 'The sand pulls this whole thing together.',
        'sand+brown+bottom'       => 'Tonal dressing at its simplest.',
        'brown+cream+top'         => 'Rich and gentle. The kind of look you don't think about twice.',
        'brown+sand+bottom'       => 'Earth tones, honest materials. That's the whole idea.',
        'brown+olive+bottom'      => 'The outdoors meets the neighborhood.',
        'brown+black+bottom'      => 'Brown anchors and black frames. Done.',
        'cream+charcoal+bottom'   => 'Bright enough to notice, muted enough to mean it.',
        'cream+olive+top'         => 'Warm meets deliberate. Like a good morning.',
        'cream+brown+bottom'      => 'Soft palette, no filler. Exactly enough.',
        'cream+black+bottom'      => 'As clean as it gets.',
        'olive+sand+top'          => 'Relaxed layers that feel pulled together, not assembled.',
        'olive+cream+bottom'      => 'Weighted neutrals - the olive does the talking.',
        'black+charcoal+top'      => 'A monochrome base with just enough texture.',
        'charcoal+black+bottom'   => 'Two darks, different weights. It works because it's intentional.',
        'sand+cream+top'          => 'Tonal lightness. The quietest kind of statement.',
        'brown+charcoal+bottom'   => 'Earthy and decisive.',
        'olive+brown+bottom'      => 'Natural pairing - these shades grew up together.',
        'cream+sand+bottom'       => 'Light, warm, and effortless.',
    ];

    public function explain(array $productColors, string $anchorRole): string
    {
        $key = implode('+', array_slice($productColors, 0, 2)) . '+' . $anchorRole;

        return $this->templates[$key]
            ?? 'Pieces that share a palette and a point of view.';
    }
}
