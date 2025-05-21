<?php

namespace Database\Seeders;

use App\Models\BillingCycle;
use Illuminate\Database\Seeder;

class BillingCycleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cycles = [
            ['name' => 'Mensual', 'slug' => 'monthly', 'days' => 30],
            ['name' => 'Trimestral', 'slug' => 'quarterly', 'days' => 90],
            ['name' => 'Semestral', 'slug' => 'semi_annually', 'days' => 180],
            ['name' => 'Anual', 'slug' => 'annually', 'days' => 365],
            ['name' => 'Bienal', 'slug' => 'biennially', 'days' => 730],
            ['name' => 'Trienal', 'slug' => 'triennially', 'days' => 1095],
            ['name' => 'Única vez', 'slug' => 'one_time', 'days' => 0],
        ];

        foreach ($cycles as $cycle) {
            BillingCycle::firstOrCreate(
                ['slug' => $cycle['slug']],
                ['name' => $cycle['name'], 'days' => $cycle['days']]
            );
        }
    }
}
