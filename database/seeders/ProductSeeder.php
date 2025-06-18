<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure ProductTypeSeeder has run, or at least the types you need exist
        // For simplicity, we'll fetch them by slug. Handle null cases if slugs might be missing.

        $webHostingType = ProductType::where('slug', 'web-hosting')->first();
        $vpsHostingType = ProductType::where('slug', 'vps-hosting')->first();
        $domainRegistrationType = ProductType::where('slug', 'domain-registration')->first();
        $sslCertificateType = ProductType::where('slug', 'ssl-certificate')->first();
        $generalServiceType = ProductType::where('slug', 'general-service')->first();

        if ($webHostingType) {
            Product::updateOrCreate(
                ['name' => 'Basic Shared Hosting'],
                [
                    'description' => 'Affordable shared hosting for small websites.',
                    'product_type_id' => $webHostingType->id,
                    'is_active' => true,
                ]
            );
        }

        if ($vpsHostingType) {
            Product::updateOrCreate(
                ['name' => 'Standard VPS'],
                [
                    'description' => 'Reliable VPS with moderate resources.',
                    'product_type_id' => $vpsHostingType->id,
                    'is_active' => true,
                ]
            );
        }

        if ($domainRegistrationType) {
            Product::updateOrCreate(
                ['name' => '.COM Domain Registration'],
                [
                    'description' => 'Register your .COM domain name.',
                    'product_type_id' => $domainRegistrationType->id,
                    'is_active' => true,
                ]
            );
        }

        if ($sslCertificateType) {
            Product::updateOrCreate(
                ['name' => 'Standard SSL Certificate'],
                [
                    'description' => 'Secure your website with a standard SSL certificate.',
                    'product_type_id' => $sslCertificateType->id,
                    'is_active' => true,
                ]
            );
        }

        if ($generalServiceType) {
            Product::updateOrCreate(
                ['name' => 'Website Migration Service'],
                [
                    'description' => 'Professional website migration service.',
                    'product_type_id' => $generalServiceType->id,
                    'is_active' => true,
                ]
            );
            Product::updateOrCreate(
                ['name' => 'Custom Development Work (Other)'],
                [
                    'description' => 'Billing for other custom development tasks.',
                    'product_type_id' => $generalServiceType->id,
                    'is_active' => true,
                ]
            );
        }

        // You can also use the factory to create more products
        // Ensure ProductTypes exist before factory attempts to pick one.
        if (ProductType::count() > 0) {
             Product::factory()->count(5)->create(); // Create 5 more random products
        }
    }
}
