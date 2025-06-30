<?php

namespace Database\Seeders;

use App\Models\BillingCycle;
use App\Models\ClientService;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ClientServiceWithConfigurationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar o crear un usuario cliente
        $client = User::where('role', 'client')->first();
        if (!$client) {
            $client = User::factory()->create([
                'role' => 'client',
                'email' => 'cliente.test@example.com',
                'name' => 'Cliente de Prueba',
            ]);
        }

        // Buscar un producto de hosting
        $hostingProduct = Product::whereHas('productType', function ($query) {
            $query->where('name', 'Hosting');
        })->first();

        if (!$hostingProduct) {
            $this->command->error('No se encontró un producto de hosting. Ejecuta primero HostingProductsSeeder.');
            return;
        }

        // Buscar un pricing mensual para el producto
        $monthlyPricing = ProductPricing::where('product_id', $hostingProduct->id)
            ->whereHas('billingCycle', function ($query) {
                $query->where('name', 'Mensual');
            })
            ->first();

        if (!$monthlyPricing) {
            $this->command->error('No se encontró pricing mensual para el producto de hosting.');
            return;
        }

        // Crear servicio con configuraciones adicionales
        $service = ClientService::create([
            'client_id' => $client->id,
            'reseller_id' => $client->reseller_id ?? 1, // Asumiendo que hay un reseller con ID 1
            'product_id' => $hostingProduct->id,
            'product_pricing_id' => $monthlyPricing->id,
            'billing_cycle_id' => $monthlyPricing->billing_cycle_id,
            'domain_name' => 'ejemplo-configuraciones.com',
            'status' => 'active',
            'registration_date' => Carbon::now()->subDays(30),
            'next_due_date' => Carbon::now()->addDays(30),
            'billing_amount' => $monthlyPricing->price + 15.50, // Precio base + configuraciones adicionales
            'notes' => "5 GB de espacio web adicional\n2 vCPU adicionales\n1 GB de RAM adicional",
        ]);

        // Crear otro servicio sin configuraciones adicionales para comparar
        $serviceBasic = ClientService::create([
            'client_id' => $client->id,
            'reseller_id' => $client->reseller_id ?? 1,
            'product_id' => $hostingProduct->id,
            'product_pricing_id' => $monthlyPricing->id,
            'billing_cycle_id' => $monthlyPricing->billing_cycle_id,
            'domain_name' => 'ejemplo-basico.com',
            'status' => 'active',
            'registration_date' => Carbon::now()->subDays(15),
            'next_due_date' => Carbon::now()->addDays(45),
            'billing_amount' => $monthlyPricing->price,
            'notes' => null,
        ]);

        $this->command->info('Servicios de cliente creados:');
        $this->command->info("- {$service->domain_name} (con configuraciones adicionales)");
        $this->command->info("- {$serviceBasic->domain_name} (básico)");
        $this->command->info("Usuario cliente: {$client->email}");
    }
}
