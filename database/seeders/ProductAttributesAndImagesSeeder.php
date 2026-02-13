<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Backfills product_attribute_values (so products show in listing) and
 * product_images (downloads images to storage) for storefront products.
 */
class ProductAttributesAndImagesSeeder extends Seeder
{
    protected string $locale = 'en';

    protected string $channel = 'default';

    protected array $typeToColumn = [
        'text'     => 'text_value',
        'textarea' => 'text_value',
        'price'    => 'float_value',
        'boolean'  => 'boolean_value',
        'select'   => 'integer_value',
        'datetime' => 'datetime_value',
        'date'     => 'date_value',
        'image'    => 'text_value',
        'file'     => 'text_value',
        'checkbox' => 'text_value',
        'multiselect' => 'text_value',
    ];

    public function run(): void
    {
        $productFlats = DB::table('product_flat')
            ->where('channel', $this->channel)
            ->where('locale', $this->locale)
            ->whereNull('parent_id')
            ->get();

        if ($productFlats->isEmpty()) {
            $this->command?->warn('No product_flat rows found for default channel/locale.');

            return;
        }

        $attributes = DB::table('attributes')
            ->whereIn('code', ['name', 'url_key', 'status', 'visible_individually'])
            ->get()
            ->keyBy('code');

        if ($attributes->count() < 4) {
            $this->command?->warn('Missing required attributes (name, url_key, status, visible_individually).');
        }

        $productImageUrls = config('placeholder_images.products', [
            'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&q=80',
            'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=400&q=80',
        ]);
        $imageIndex = 0;

        foreach ($productFlats as $flat) {
            $productId = $flat->product_id;

            // 1. Ensure product_attribute_values so product appears in listing
            $existingAttr = DB::table('product_attribute_values')
                ->where('product_id', $productId)
                ->whereIn('attribute_id', $attributes->pluck('id'))
                ->count();

            if ($existingAttr < 4 && $attributes->isNotEmpty()) {
                $this->addAttributeValuesForProduct($flat, $attributes);
            }

            // 2. Ensure product has at least one image
            $hasImage = DB::table('product_images')->where('product_id', $productId)->exists();
            if (! $hasImage) {
                $url = $productImageUrls[$imageIndex % count($productImageUrls)];
                $imageIndex++;
                $this->addProductImage($productId, $url);
            }
        }

        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        if (class_exists(\Spatie\ResponseCache\Facades\ResponseCache::class)) {
            \Spatie\ResponseCache\Facades\ResponseCache::clear();
        }

        $this->command?->info('Product attributes and images backfill done.');
    }

    protected function addAttributeValuesForProduct($flat, $attributes): void
    {
        $productId = $flat->product_id;
        $rows = [];

        foreach (['name', 'url_key', 'status', 'visible_individually'] as $code) {
            $attr = $attributes->get($code);
            if (! $attr) {
                continue;
            }
            if (DB::table('product_attribute_values')
                ->where('product_id', $productId)
                ->where('attribute_id', $attr->id)
                ->exists()) {
                continue;
            }
            $col = $this->typeToColumn[$attr->type] ?? 'text_value';
            $value = match ($code) {
                'name' => $flat->name,
                'url_key' => $flat->url_key,
                'status', 'visible_individually' => 1,
                default => null,
            };
            $uniqueId = implode('|', array_filter([
                $attr->value_per_channel ? $this->channel : null,
                $attr->value_per_locale ? $this->locale : null,
                $productId,
                $attr->id,
            ]));
            $row = [
                'attribute_id'   => $attr->id,
                'product_id'    => $productId,
                'channel'       => $attr->value_per_channel ? $this->channel : null,
                'locale'        => $attr->value_per_locale ? $this->locale : null,
                'unique_id'     => $uniqueId,
                'text_value'    => null,
                'boolean_value' => null,
                'integer_value' => null,
                'float_value'   => null,
                'datetime_value'=> null,
                'date_value'    => null,
                'json_value'    => null,
            ];
            $row[$col] = $value;
            $rows[] = $row;
        }
        if (! empty($rows)) {
            DB::table('product_attribute_values')->insert($rows);
        }
    }

    protected function addProductImage(int $productId, string $imageUrl): void
    {
        try {
            $response = Http::timeout(15)->get($imageUrl);
            if (! $response->successful()) {
                return;
            }
            $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            if (! in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $ext = 'jpg';
            }
            $path = 'product/' . $productId . '/' . uniqid('img_') . '.' . $ext;
            Storage::disk('public')->put($path, $response->body());

            $maxPos = (int) DB::table('product_images')->where('product_id', $productId)->max('position');
            DB::table('product_images')->insert([
                'type'       => 'image',
                'path'       => $path,
                'product_id' => $productId,
                'position'   => $maxPos + 1,
            ]);
        } catch (\Throwable $e) {
            $this->command?->warn("Could not add image for product {$productId}: " . $e->getMessage());
        }
    }
}
