<?php
namespace App\Console\Commands;

use App\Services\PricingCalculatorService;
use Illuminate\Console\Command;

class TestPricingCalculator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:pricing-calculator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la calculadora de precios con datos reales';

    protected $pricingCalculator;

    public function __construct(PricingCalculatorService $pricingCalculator)
    {
        parent::__construct();
        $this->pricingCalculator = $pricingCalculator;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧮 Probando la Calculadora de Precios');
        $this->newLine();

        // Test 1: Precio base de Hosting Web Eco (ID 2) - Mensual (ID 1)
        $this->info('📋 Test 1: Hosting Web Eco - Mensual (sin opciones)');
        $result1 = $this->pricingCalculator->calculateProductPrice(2, 1, []);
        $this->displayResult($result1);

        // Test 2: Hosting Web Eco con opciones configurables
        $this->info('📋 Test 2: Hosting Web Eco - Mensual (con opciones)');
        $configurableOptions = [
            1 => 5, // 5GB adicionales de espacio
            2 => 2, // 2 vCPU adicionales
            4 => 1, // SpamExperts activado
        ];
        $result2 = $this->pricingCalculator->calculateProductPrice(2, 1, $configurableOptions);
        $this->displayResult($result2);

        // Test 3: Hosting Web Pro - Anual (con descuento)
        $this->info('📋 Test 3: Hosting Web Pro - Anual (con descuento 18%)');
        $result3 = $this->pricingCalculator->calculateProductPrice(3, 4, []);
        $this->displayResult($result3);

        // Test 4: Recursos base de un producto
        $this->info('📋 Test 4: Recursos base de Hosting Web Eco');
        $baseResources = $this->pricingCalculator->getProductBaseResources(2);
        $this->displayBaseResources($baseResources);

        $this->newLine();
        $this->info('✅ Pruebas completadas');
    }

    private function displayResult(array $result)
    {
        $this->line("  💰 Precio base: {$result['base_price']['price']} {$result['currency_code']}");

        if (! empty($result['base_resources']['details'])) {
            $this->line("  📦 Recursos base incluidos:");
            foreach ($result['base_resources']['details'] as $resource) {
                $this->line("    - {$resource['display_text']}: {$resource['base_quantity']} x {$resource['unit_price']} = {$resource['line_total']} {$result['currency_code']}");
            }
            $this->line("  📊 Total recursos base: {$result['base_resources']['total']} {$result['currency_code']}");
        }

        if (! empty($result['configurable_options']['details'])) {
            $this->line("  🔧 Opciones configurables adicionales:");
            foreach ($result['configurable_options']['details'] as $option) {
                $this->line("    - {$option['option_name']}: {$option['quantity']} x {$option['unit_price']} = {$option['line_total']} {$result['currency_code']}");
            }
            $this->line("  📊 Total opciones adicionales: {$result['configurable_options']['total']} {$result['currency_code']}");
        }

        $this->line("  📈 Subtotal: {$result['subtotal']} {$result['currency_code']}");

        if ($result['discount']['percentage'] > 0) {
            $this->line("  🎯 Descuento: {$result['discount']['name']} ({$result['discount']['percentage']}%)");
            $this->line("  💸 Descuento aplicado: -{$result['discount_amount']} {$result['currency_code']}");
        }

        $this->line("  🏆 TOTAL FINAL: {$result['total']} {$result['currency_code']}");
        $this->newLine();
    }

    private function displayBaseResources(array $baseResources)
    {
        if (empty($baseResources)) {
            $this->line("  ❌ No hay recursos base configurados");
            return;
        }

        $this->line("  📦 Recursos incluidos:");
        foreach ($baseResources as $resource) {
            $this->line("    - {$resource['display_text']}");
        }
        $this->newLine();
    }
}
