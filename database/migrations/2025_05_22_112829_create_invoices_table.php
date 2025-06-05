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
        Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained('users');
    $table->foreignId('reseller_id')->nullable()->constrained('users');
    $table->string('invoice_number')->unique();
    $table->date('issue_date'); // Fecha de emisión formal
    $table->date('due_date')->index();
    $table->date('paid_date')->nullable();
    // Nuevos campos
    $table->timestamp('requested_date')->nullable()->comment('Fecha de solicitud del servicio/pedido');
    $table->string('ip_address')->nullable();
    $table->string('payment_gateway_slug')->nullable()->comment('Pasarela de pago usada o seleccionada');

    // Status actualizado
    $table->enum('status', [
        'draft',
        'unpaid',
        'pending_confirmation', // Añadido
        'paid',
        'pending_activation', // Pagada, esperando activación del servicio
        'active_service',     // Servicio activo y facturado correctamente
        'overdue',
        'cancelled',
        'refunded',
        'collections',
        'failed_payment'
    ])->default('draft')->index();

    $table->string('paypal_order_id')->nullable()->unique(); // Mantener si se usa PayPal
    $table->decimal('subtotal', 10, 2);
    $table->string('tax1_name')->nullable();
    $table->decimal('tax1_rate', 5, 2)->nullable();
    $table->decimal('tax1_amount', 10, 2)->nullable();
    $table->string('tax2_name')->nullable();
    $table->decimal('tax2_rate', 5, 2)->nullable();
    $table->decimal('tax2_amount', 10, 2)->nullable();
    $table->decimal('total_amount', 10, 2);
    $table->string('currency_code', 3);
    $table->text('notes_to_client')->nullable();
    $table->text('admin_notes')->nullable();
    $table->timestamps();
    $table->softDeletes();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
