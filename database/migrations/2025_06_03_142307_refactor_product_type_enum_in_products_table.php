<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\ProductType; // Import ProductType model

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Data migration: Map old enum 'type' to 'product_type_id'
        // Do this in a transaction to ensure atomicity
        DB::transaction(function () {
            $products = DB::table('products')->whereNotNull('type')->get();

            $typeMapping = [
                'shared_hosting' => 'web-hosting',
                'vps' => 'vps-hosting',
                'dedicated_server' => 'general-service', // Fallback, as 'dedicated-server' slug is not in ProductTypeSeeder
                'domain_registration' => 'domain-registration',
                'ssl_certificate' => 'ssl-certificate',
                'other' => 'general-service',
            ];

            foreach ($products as $product) {
                $slug = $typeMapping[$product->type] ?? 'general-service'; // Default to 'general-service' if somehow not mapped
                $productType = ProductType::where('slug', $slug)->first();

                if ($productType) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update(['product_type_id' => $productType->id]);
                } else {
                    // Log a warning or handle missing ProductType appropriately
                    // For now, we'll assume 'general-service' will exist or use null if it makes more sense.
                    // If 'general-service' must exist, seeder should ensure it.
                    // Using null might be problematic if product_type_id is not nullable.
                    // For this example, if 'general-service' itself is missing, this will do nothing for that product.
                    // Consider adding a specific log: Log::warning("ProductType with slug '{$slug}' not found for product ID {$product->id}.");
                }
            }
        });

        Schema::table('products', function (Blueprint $table) {
            // Drop the index before dropping the column for SQLite compatibility
            $table->dropIndex('products_type_index'); // Explicitly drop the index
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // It's good practice to define where the column should be re-added, e.g., after 'description'
            // Assuming 'description' exists, otherwise adjust or remove ->after()
            $table->enum('type', ['shared_hosting', 'vps', 'dedicated_server', 'domain_registration', 'ssl_certificate', 'other'])
                  ->index() // This will recreate the index 'products_type_index'
                  ->after('description'); // Or determine its original position if critical
        });

        // Optional: Attempt to revert product_type_id to type
        // This is more complex as it's a many-to-one mapping and information might be lost.
        // For simplicity, this part is often omitted in real-world down migrations unless critical.
        // DB::transaction(function () {
        //     $products = DB::table('products')->whereNotNull('product_type_id')->get();
        //     $reverseTypeMapping = [ // This needs to be carefully constructed based on your ProductType slugs
        //         'web-hosting' => 'shared_hosting',
        //         'vps-hosting' => 'vps',
        //         // etc.
        //     ];
        //
        //     foreach ($products as $product) {
        //         $productType = ProductType::find($product->product_type_id);
        //         if ($productType && isset($reverseTypeMapping[$productType->slug])) {
        //             DB::table('products')
        //                 ->where('id', $product->id)
        //                 ->update(['type' => $reverseTypeMapping[$productType->slug]]);
        //         } else {
        //             // Handle cases where reverse mapping isn't possible (e.g., set to 'other')
        //             DB::table('products')->where('id', $product->id)->update(['type' => 'other']);
        //         }
        //     }
        // });
        //
        // After repopulating 'type', you might want to nullify 'product_type_id' for products
        // where 'type' was successfully restored, if 'product_type_id' was added by this migration.
        // However, product_type_id is likely a pre-existing column based on the problem description context,
        // so we just focus on re-adding the 'type' column.
    }
};
