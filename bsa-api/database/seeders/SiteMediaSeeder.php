<?php

namespace Database\Seeders;

use App\Models\SiteMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SiteMediaSeeder extends Seeder
{
    // url is intentionally left null for every slot — the site falls back to a
    // branded placeholder until an owner uploads a real photo via the admin
    // (Site Media page). No fake stock is written to the owner's Cloudinary.
    public function run(): void
    {
        SiteMedia::truncate();

        $slots = [
            // ── Home ────────────────────────────────────────────────
            ['key' => 'home_hero_poster', 'page_group' => 'Home', 'label' => 'Hero Background — full-screen image behind the homepage headline (shown while the background video loads)', 'sort_order' => 1],
            ['key' => 'home_stats_background', 'page_group' => 'Home', 'label' => 'Stats Section Background — full-bleed dark image behind the numbers counter', 'sort_order' => 2],
            ['key' => 'home_cta_background', 'page_group' => 'Home', 'label' => 'Bottom CTA Background — behind the "Ready to Play?" call-to-action', 'sort_order' => 3],
            ['key' => 'home_gallery_courts', 'page_group' => 'Home', 'label' => 'Gallery — "Professional Courts" (large featured tile in Life at BSA)', 'sort_order' => 4],
            ['key' => 'home_gallery_gym_floor', 'page_group' => 'Home', 'label' => 'Gallery — "Gym Floor" tile', 'sort_order' => 5],
            ['key' => 'home_gallery_sauna', 'page_group' => 'Home', 'label' => 'Gallery — "Sauna & Steam" tile', 'sort_order' => 6],
            ['key' => 'home_gallery_strength', 'page_group' => 'Home', 'label' => 'Gallery — "Strength Training" tile', 'sort_order' => 7],
            ['key' => 'home_gallery_team', 'page_group' => 'Home', 'label' => 'Gallery — "Team Sessions" tile', 'sort_order' => 8],

            // ── Programs ────────────────────────────────────────────
            ['key' => 'programs_header_banner', 'page_group' => 'Programs', 'label' => 'Page Header Banner — top of the Programs page', 'sort_order' => 1],
            ['key' => 'programs_cta_background', 'page_group' => 'Programs', 'label' => 'Bottom CTA Background — "Not sure which program?" section', 'sort_order' => 2],

            // ── Facilities ──────────────────────────────────────────
            ['key' => 'facilities_header_banner', 'page_group' => 'Facilities', 'label' => 'Page Header Banner — top of the Facilities page', 'sort_order' => 1],
            ['key' => 'facilities_cta_background', 'page_group' => 'Facilities', 'label' => 'Bottom CTA Background', 'sort_order' => 2],
            ['key' => 'facilities_fallback_badminton', 'page_group' => 'Facilities', 'label' => 'Fallback Photo — Badminton (used only if that facility record has no uploaded photo)', 'sort_order' => 3],
            ['key' => 'facilities_fallback_gym', 'page_group' => 'Facilities', 'label' => 'Fallback Photo — Gym (used only if that facility record has no uploaded photo)', 'sort_order' => 4],
            ['key' => 'facilities_fallback_sauna', 'page_group' => 'Facilities', 'label' => 'Fallback Photo — Sauna (used only if that facility record has no uploaded photo)', 'sort_order' => 5],

            // ── Kitchen ─────────────────────────────────────────────
            ['key' => 'kitchen_header_banner', 'page_group' => 'Kitchen', 'label' => 'Page Header Banner — top of the Kitchen page', 'sort_order' => 1],

            // ── About ───────────────────────────────────────────────
            ['key' => 'about_header_banner', 'page_group' => 'About', 'label' => 'Page Header Banner — top of the About page', 'sort_order' => 1],
            ['key' => 'about_mission_image', 'page_group' => 'About', 'label' => 'Mission Section Photo — side image next to the "Since 2024" badge', 'sort_order' => 2],
            ['key' => 'about_gallery_courts', 'page_group' => 'About', 'label' => 'Facility Gallery — "Professional Courts"', 'sort_order' => 3],
            ['key' => 'about_gallery_action', 'page_group' => 'About', 'label' => 'Facility Gallery — "Action on Court"', 'sort_order' => 4],
            ['key' => 'about_gallery_gym', 'page_group' => 'About', 'label' => 'Facility Gallery — "Fully Equipped Gym"', 'sort_order' => 5],
            ['key' => 'about_gallery_strength', 'page_group' => 'About', 'label' => 'Facility Gallery — "Strength Training"', 'sort_order' => 6],
            ['key' => 'about_gallery_sauna', 'page_group' => 'About', 'label' => 'Facility Gallery — "Sauna & Steam"', 'sort_order' => 7],
            ['key' => 'about_gallery_team', 'page_group' => 'About', 'label' => 'Facility Gallery — "Team Sessions"', 'sort_order' => 8],
        ];

        foreach ($slots as $slot) {
            SiteMedia::create(array_merge(['id' => Str::uuid()], $slot));
        }
    }
}
