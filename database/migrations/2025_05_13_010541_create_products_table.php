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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['shared_hosting', 'vps', 'dedicated_server', 'domain_registration', 'ssl_certificate', 'other'])->index();
            $table->string('module_name')->nullable()->index(); // Para integración con cPanel, Plesk, etc.
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('cascade'); // FK a users.id (puede ser NULL para productos de plataforma, o ID de reseller)
            $table->boolean('is_publicly_available')->default(true);
            $table->boolean('is_resellable_by_default')->default(true); // Para productos de plataforma

            // $table->foreignId('welcome_email_template_id')->nullable()->constrained('email_templates')->onDelete('set null'); // Descomentar cuando exista la tabla email_templates
            $table->unsignedBigInteger('welcome_email_template_id')->nullable(); // Placeholder hasta crear email_templates

            $table->enum('status', ['active', 'inactive', 'hidden'])->default('active')->index();
            $table->integer('display_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
