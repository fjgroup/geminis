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
            $table->string('name'); // e.g., "Web Hosting", "VPS", "Domain Registration", "SSL Certificate"
            $table->string('slug')->unique(); // e.g., "hosting", "vps", "domain", "ssl"
            $table->boolean('requires_domain')->default(false); // Does this product type inherently need a domain?
            $table->boolean('creates_service_instance')->default(false); // Does ordering this create a ClientService record?
            $table->text('description')->nullable(); // Optional description of the product type
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
