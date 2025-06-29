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
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('cascade'); // FK a users.id (puede ser NULL para productos de plataforma, o ID de reseller)
            $table->foreignId('product_type_id')
                ->nullable()
                                           // Placing it near the start for clarity, or after 'type' if preferred
                ->constrained('product_types') // Assumes the table is named 'product_types'
                ->onUpdate('cascade')
                ->onDelete('set null'); // Or ->onDelete('restrict') or ->onDelete('cascade') depending on desired behavior
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
                                                                // $table->enum('type', ['shared_hosting', 'vps', 'dedicated_server', 'domain_registration', 'ssl_certificate', 'other'])->index(); // ELIMINAR ESTA LÍNEA
            $table->string('module_name')->nullable()->index(); // Para integración con cPanel, Plesk, etc.
            $table->boolean('is_publicly_available')->default(true);
            $table->boolean('is_resellable_by_default')->default(true); // Para productos de plataforma

                                                                                 // $table->foreignId('welcome_email_template_id')->nullable()->constrained('email_templates')->onDelete('set null'); // Descomentar cuando exista la tabla email_templates
            $table->unsignedBigInteger('welcome_email_template_id')->nullable(); // Placeholder hasta crear email_templates

            $table->enum('status', ['active', 'inactive', 'hidden'])->default('active')->index();
            $table->integer('display_order')->default(0);
            $table->boolean('auto_setup')->default(false);        // Configuración automática
            $table->boolean('requires_approval')->default(false); // Requiere aprobación manual
            $table->decimal('setup_fee', 10, 2)->default(0.00);   // Tarifa de configuración base
            $table->integer('stock_quantity')->nullable();        // Para productos con stock limitado
            $table->boolean('track_stock')->default(false);       // Si debe rastrear inventario

                                                                     // Recursos base incluidos en el producto
       //     $table->decimal('base_disk_space_gb', 8, 2)->nullable(); // Espacio en disco base (GB)
        //    $table->integer('base_vcpu_cores')->nullable();          // vCPU cores base
        //    $table->decimal('base_ram_gb', 8, 2)->nullable();        // RAM base (GB)
        //    $table->integer('base_bandwidth_gb')->nullable();        // Ancho de banda base (GB)
        //    $table->integer('base_email_accounts')->nullable();      // Cuentas de email base
        //    $table->integer('base_databases')->nullable();           // Bases de datos base
       //     $table->integer('base_domains')->nullable();             // Dominios adicionales base
       //     $table->integer('base_subdomains')->nullable();          // Subdominios base

                                                                             // Landing page y marketing
            $table->string('landing_page_slug')->nullable()->unique();       // Slug para landing page pública
            $table->text('landing_page_description')->nullable();            // Descripción para landing page
            $table->string('landing_page_image')->nullable();                // Imagen para landing page
            $table->json('features_list')->nullable();                       // Lista de características para mostrar
            $table->string('call_to_action_text')->default('Comprar Ahora'); // Texto del botón CTA

            $table->json('metadata')->nullable(); // Para configuraciones adicionales

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
