<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // e.g., "Web Hosting", "VPS", "Domain Registration", "SSL Certificate"
            $table->string('slug')->unique(); // e.g., "hosting", "vps", "domain", "ssl"
            $table->enum('status', ['active', 'inactive', 'deprecated'])->default('active');
            $table->boolean('requires_domain')->default(false);              // Does this product type inherently need a domain?
            $table->boolean('creates_service_instance')->default(false);     // Does ordering this create a ClientService record?
            $table->boolean('is_publicly_available')->default(true);         // Can clients see this type?
            $table->boolean('supports_configurable_options')->default(true); // Can have configurable options?
            $table->boolean('supports_billing_cycles')->default(true);       // Can have different billing cycles?
            $table->boolean('supports_discounts')->default(true);            // Can have discounts applied?
            $table->text('description')->nullable();                         // Optional description of the product type
            $table->integer('display_order')->default(0);                    // For sorting in admin/client interfaces
            $table->json('metadata')->nullable();                            // For additional configuration
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_types');
    }
};
