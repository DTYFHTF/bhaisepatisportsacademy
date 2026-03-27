<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncFromProduction extends Command
{
    protected $signature = 'sync:production
                            {--url=https://api.bsa.example.com : Production API base URL}
                            ';  // sync - pulls all products from production API

    protected $description = 'Pull products and site settings from production into local DB';

    public function handle(): int
    {
        $base = rtrim($this->option('url'), '/');

        $this->info("Syncing from {$base}");
        $this->newLine();

        $this->syncSettings($base);
        $this->syncProducts($base);

        $this->newLine();
        $this->info('✓ Sync complete.');

        return self::SUCCESS;
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36',
                'Accept'     => 'application/json',
            ]);
    }

    private function syncSettings(string $base): void
    {
        $this->line('  Settings...');

        $response = $this->http()->get("{$base}/api/settings");

        if (! $response->successful()) {
            $this->warn("  ✗ Failed to fetch settings ({$response->status()})");
            return;
        }

        $data = $response->json();

        // Guard against bot-protection HTML/JSON responses
        if (! is_array($data) || ! isset($data['storeName']) && ! isset($data['store_name'])) {
            $this->warn('  ✗ Settings response blocked or invalid. Whitelist your IP in cPanel → Imunify360.');
            $this->line('  Preview: ' . substr($response->body(), 0, 120));
            return;
        }

        // API returns camelCase - convert to snake_case for the DB
        $mapped = collect($data)
            ->mapWithKeys(fn ($v, $k) => [Str::snake($k) => $v])
            ->toArray();

        DB::table('site_settings')->updateOrInsert(['id' => 1], $mapped);

        $this->line('  ✓ Settings synced.');
    }

    private function syncProducts(string $base): void
    {
        $this->line('  Products...');

        $response = $this->http()->get("{$base}/api/products", ['limit' => 100]);

        if (! $response->successful()) {
            $this->warn("  ✗ Failed to fetch products ({$response->status()})");
            return;
        }

        $products = $response->json();

        // Guard against bot-protection response
        if (! is_array($products) || (! empty($products) && ! isset($products[0]['id']))) {
            $this->warn('  ✗ Products response blocked or invalid. Whitelist your IP in cPanel → Imunify360.');
            $this->line('  Preview: ' . substr($response->body(), 0, 120));
            return;
        }

        if (empty($products)) {
            $this->warn('  No products returned from production.');
            return;
        }

        $this->line("  Found " . count($products) . " products - importing...");

        $productFields = [
            'id', 'name', 'slug', 'color_name', 'category', 'description',
            'fabric_story', 'price', 'compare_at_price', 'wardrobe_role',
            'tags', 'is_active', 'seo_title', 'seo_description',
            'created_at', 'updated_at',
        ];
        $variantFields = ['id', 'product_id', 'size', 'stock', 'sku', 'created_at', 'updated_at'];
        $imageFields   = ['id', 'product_id', 'url', 'alt_text', 'cloudinary_id', 'order', 'created_at', 'updated_at'];

        // Production IDs differ from seeder IDs - wipe local tables and re-insert fresh.
        // FK checks off so we can clear in any order; seeded test-orders become orphaned
        // (acceptable on local dev).
        DB::statement('PRAGMA foreign_keys = OFF');
        DB::table('product_images')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('products')->truncate();

        DB::transaction(function () use ($products, $productFields, $variantFields, $imageFields) {
            foreach ($products as $p) {
                $data = $this->mapFields($p, $productFields);
                if (isset($data['tags']) && is_array($data['tags'])) {
                    $data['tags'] = json_encode($data['tags']);
                }
                DB::table('products')->updateOrInsert(['id' => $data['id']], $data);

                foreach ($p['variants'] ?? [] as $v) {
                    $vData = $this->mapFields($v, $variantFields);
                    DB::table('product_variants')->updateOrInsert(['id' => $vData['id']], $vData);
                }
                foreach ($p['images'] ?? [] as $img) {
                    $iData = $this->mapFields($img, $imageFields);
                    DB::table('product_images')->updateOrInsert(['id' => $iData['id']], $iData);
                }
            }
        });
        DB::statement('PRAGMA foreign_keys = ON');

        $variantCount = DB::table('product_variants')->count();
        $imageCount   = DB::table('product_images')->count();
        $this->line('  ✓ ' . count($products) . " products, {$variantCount} variants, {$imageCount} images.");
    }

    /**
     * Convert camelCase API response keys to snake_case and pick only $allowedKeys.
     */
    private function mapFields(array $data, array $allowedKeys): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $snake = Str::snake($key);
            if (in_array($snake, $allowedKeys, true)) {
                $result[$snake] = $value;
            }
        }
        return $result;
    }
}
