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
        Schema::create('client_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->comment('FK a users.id del cliente');
            $table->foreignId('reseller_id')->nullable()->constrained('users')->onDelete('set null')->comment('FK a users.id del revendedor, si aplica');
            // $table->foreignId('order_id')->nullable()->constrained('orders')->comment('FK a la orden que originó este servicio'); // Comentado temporalmente
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('billing_cycle_id')->nullable()->constrained('billing_cycles')->onDelete('set null')->comment('FK a billing_cycles.id');
            $table->foreignId('product_pricing_id')->constrained('product_pricings')->comment('Ciclo de facturación elegido');
            $table->string('domain_name')->nullable()->index();
            $table->string('username')->nullable();
            $table->text('password_encrypted')->nullable(); // Considerar encriptación real
            // $table->foreignId('server_id')->nullable()->constrained('servers'); // Comentado temporalmente
            $table->enum('status', ['pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud', 'pending_configuration', 'provisioning_failed'])->default('pending')->index();
            $table->date('registration_date');
            $table->date('next_due_date')->index();
            $table->date('termination_date')->nullable();
            $table->decimal('billing_amount', 10, 2); // Monto recurrente actual (puede incluir opciones)
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_services');
    }
};
