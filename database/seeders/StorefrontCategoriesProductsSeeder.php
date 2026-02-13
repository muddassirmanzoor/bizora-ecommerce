<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Ensures categories and products show on the storefront after migration.
 * Fixes channel root_category_id, ensures root category exists, adds 6 categories
 * (Cars, Watches, Electronics, Fashion, Home & Living, Sports) and sample products.
 */
class StorefrontCategoriesProductsSeeder extends Seeder
{
    protected string $locale = 'en';

    protected string $channel = 'default';

    protected array $categoryData = [
        ['name' => 'Cars', 'slug' => 'cars', 'description' => 'Cars and automotive accessories'],
        ['name' => 'Watches', 'slug' => 'watches', 'description' => 'Watches and smart wearables'],
        ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronics and gadgets'],
        ['name' => 'Fashion', 'slug' => 'fashion', 'description' => 'Fashion and clothing'],
        ['name' => 'Home & Living', 'slug' => 'home-living', 'description' => 'Home and living products'],
        ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports and outdoor gear'],
    ];

    public function run(): void
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        // 1. Ensure channel has root category
        DB::table('channels')->where('id', 1)->update([
            'root_category_id' => 1,
            'updated_at'       => $now,
        ]);

        // 2. Ensure root category exists
        $root = DB::table('categories')->where('id', 1)->first();
        if (! $root) {
            DB::table('categories')->insert([
                'id'         => 1,
                'position'   => 0,
                'status'     => 1,
                '_lft'       => 1,
                '_rgt'       => 14,
                'parent_id'  => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('category_translations')->insert([
                'category_id'      => 1,
                'name'             => 'Root',
                'slug'             => 'root',
                'url_path'         => '',
                'description'      => '',
                'meta_title'       => '',
                'meta_description' => '',
                'meta_keywords'    => '',
                'locale'           => $this->locale,
            ]);
            $root = (object) ['_rgt' => 14];
        }

        // 3. Add 6 categories (nested set under root)
        $existingSlugs = DB::table('category_translations')
            ->where('locale', $this->locale)
            ->whereIn('slug', array_column($this->categoryData, 'slug'))
            ->pluck('slug')
            ->toArray();

        $toAdd = array_filter($this->categoryData, fn ($c) => ! in_array($c['slug'], $existingSlugs, true));
        if (empty($toAdd)) {
            $this->command?->info('Storefront categories already exist.');
        } else {
            $rootRgt = (int) (DB::table('categories')->where('id', 1)->value('_rgt') ?? 2);
            $shift = count($toAdd) * 2;
            DB::table('categories')->where('_lft', '>=', $rootRgt)->update(['_lft' => DB::raw("_lft + {$shift}")]);
            DB::table('categories')->where('_rgt', '>=', $rootRgt)->update(['_rgt' => DB::raw("_rgt + {$shift}")]);
            DB::table('categories')->where('id', 1)->update(['_rgt' => $rootRgt + $shift, 'updated_at' => $now]);

            $maxCatId = (int) DB::table('categories')->max('id');
            $position = 1;
            foreach ($toAdd as $cat) {
                $maxCatId++;
                $lft = $rootRgt + ($position - 1) * 2;
                $rgt = $lft + 1;
                DB::table('categories')->insert([
                    'id'         => $maxCatId,
                    'position'   => $position,
                    'status'     => 1,
                    'display_mode' => 'products_and_description',
                    '_lft'       => $lft,
                    '_rgt'       => $rgt,
                    'parent_id'  => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                DB::table('category_translations')->insert([
                    'category_id'      => $maxCatId,
                    'name'             => $cat['name'],
                    'slug'             => $cat['slug'],
                    'url_path'         => $cat['slug'],
                    'description'      => $cat['description'],
                    'meta_title'       => $cat['name'],
                    'meta_description' => $cat['description'],
                    'meta_keywords'    => '',
                    'locale'           => $this->locale,
                ]);
                $position++;
            }
        }

        // 4. Get category ids for our 6 slugs (all locales for safety)
        $categoryIds = DB::table('category_translations')
            ->whereIn('slug', array_column($this->categoryData, 'slug'))
            ->pluck('category_id')
            ->unique()
            ->values()
            ->toArray();

        if (empty($categoryIds)) {
            $this->command?->warn('No categories found to attach products.');

            return;
        }

        // 5. Attributes required for products to show in listing (product_attribute_values)
        $attrTypeColumn = [
            'text' => 'text_value', 'textarea' => 'text_value', 'boolean' => 'boolean_value',
            'price' => 'float_value', 'select' => 'integer_value', 'datetime' => 'datetime_value',
            'date' => 'date_value', 'image' => 'text_value', 'file' => 'text_value',
            'checkbox' => 'text_value', 'multiselect' => 'text_value',
        ];
        $requiredAttrs = DB::table('attributes')
            ->whereIn('code', ['name', 'url_key', 'status', 'visible_individually'])
            ->get()
            ->keyBy('code');

        // 6. Add simple products (2 per category) with attribute values and images
        $attributeFamilyId = (int) (DB::table('attribute_families')->value('id') ?? 1);
        $maxProductId = (int) DB::table('products')->max('id');
        $customerGroups = DB::table('customer_groups')->pluck('id')->toArray();
        if (empty($customerGroups)) {
            $customerGroups = [1];
        }
        $productImageUrls = config('placeholder_images.products', [
            'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&q=80',
            'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=400&q=80',
        ]);
        $imageUrlIndex = 0;

        $productsToCreate = [];
        $productIndex = 0;
        foreach ($categoryIds as $categoryId) {
            for ($i = 1; $i <= 2; $i++) {
                $productIndex++;
                $maxProductId++;
                $name = "Product {$productIndex}";
                $slug = 'product-' . $maxProductId . '-' . Str::slug($name);
                $productsToCreate[] = [
                    'product_id'   => $maxProductId,
                    'category_id'  => $categoryId,
                    'sku'          => 'SF-' . $maxProductId,
                    'name'         => $name,
                    'url_key'      => $slug,
                    'price'        => rand(20, 200),
                ];
            }
        }

        foreach ($productsToCreate as $p) {
            if (DB::table('products')->where('id', $p['product_id'])->exists()) {
                continue;
            }
            DB::table('products')->insert([
                'id'                  => $p['product_id'],
                'sku'                 => $p['sku'],
                'type'                => 'simple',
                'attribute_family_id' => $attributeFamilyId,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
            DB::table('product_flat')->insert([
                'sku'                  => $p['sku'],
                'type'                 => 'simple',
                'name'                 => $p['name'],
                'url_key'              => $p['url_key'],
                'short_description'   => 'Quality product.',
                'description'         => 'Quality product for your needs.',
                'price'                => $p['price'],
                'status'               => 1,
                'visible_individually' => 1,
                'new'                  => 1,
                'featured'             => 0,
                'locale'               => $this->locale,
                'channel'              => $this->channel,
                'attribute_family_id' => $attributeFamilyId,
                'product_id'          => $p['product_id'],
                'parent_id'           => null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
            // Required so products appear in storefront listing
            foreach ($requiredAttrs as $code => $attr) {
                $col = $attrTypeColumn[$attr->type] ?? 'text_value';
                $value = match ($code) {
                    'name' => $p['name'],
                    'url_key' => $p['url_key'],
                    'status', 'visible_individually' => 1,
                    default => null,
                };
                $uniqueId = implode('|', array_filter([
                    $attr->value_per_channel ? $this->channel : null,
                    $attr->value_per_locale ? $this->locale : null,
                    $p['product_id'],
                    $attr->id,
                ]));
                $row = [
                    'attribute_id'    => $attr->id,
                    'product_id'     => $p['product_id'],
                    'channel'        => $attr->value_per_channel ? $this->channel : null,
                    'locale'         => $attr->value_per_locale ? $this->locale : null,
                    'unique_id'      => $uniqueId,
                    'text_value'     => null,
                    'boolean_value'  => null,
                    'integer_value'  => null,
                    'float_value'    => null,
                    'datetime_value' => null,
                    'date_value'     => null,
                    'json_value'     => null,
                ];
                $row[$col] = $value;
                DB::table('product_attribute_values')->insert($row);
            }
            DB::table('product_categories')->insert([
                'product_id'  => $p['product_id'],
                'category_id' => $p['category_id'],
            ]);
            DB::table('product_channels')->insert([
                'product_id'  => $p['product_id'],
                'channel_id'  => 1,
            ]);
            $invSourceId = (int) (DB::table('inventory_sources')->value('id') ?? 1);
            DB::table('product_inventories')->insert([
                'product_id'           => $p['product_id'],
                'vendor_id'            => 0,
                'inventory_source_id'  => $invSourceId,
                'qty'                  => 100,
            ]);
            DB::table('product_inventory_indices')->insert([
                'product_id'  => $p['product_id'],
                'channel_id'  => 1,
                'qty'         => 100,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            foreach ($customerGroups as $cgId) {
                DB::table('product_price_indices')->insert([
                    'product_id'        => $p['product_id'],
                    'customer_group_id' => $cgId,
                    'channel_id'        => 1,
                    'min_price'         => $p['price'],
                    'regular_min_price' => $p['price'],
                    'max_price'         => $p['price'],
                    'regular_max_price' => $p['price'],
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
            }
            // Product image (download and store)
            $imgUrl = $productImageUrls[$imageUrlIndex % count($productImageUrls)];
            $imageUrlIndex++;
            $this->storeProductImage($p['product_id'], $imgUrl);
        }

        // 7. Backfill attribute values and images for products created earlier (no attribute_values)
        $this->call(ProductAttributesAndImagesSeeder::class);

        // 8. Clear caches so storefront shows new data
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        if (class_exists(\Spatie\ResponseCache\Facades\ResponseCache::class)) {
            \Spatie\ResponseCache\Facades\ResponseCache::clear();
        }

        $this->command?->info('Storefront categories and products seeded. Reload the site (clear browser localStorage for categories if needed).');
    }

    protected function storeProductImage(int $productId, string $imageUrl): void
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
            DB::table('product_images')->insert([
                'type'       => 'image',
                'path'       => $path,
                'product_id' => $productId,
                'position'   => 1,
            ]);
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
