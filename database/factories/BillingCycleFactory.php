<?php

namespace Database\Factories;

use App\Models\BillingCycle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BillingCycle>
 */
class BillingCycleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BillingCycle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $type = $this->faker->randomElement(['day', 'month', 'year']);
        $name = '';
        $multiplier = 1;

        switch ($type) {
            case 'day':
                $multiplier = $this->faker->numberBetween(1, 30);
                $name = $multiplier . ' Day(s)';
                break;
            case 'month':
                $multiplier = $this->faker->randomElement([1, 3, 6, 12]);
                if ($multiplier === 1) $name = 'Monthly';
                elseif ($multiplier === 3) $name = 'Quarterly';
                elseif ($multiplier === 6) $name = 'Semi-Annually';
                elseif ($multiplier === 12) $name = 'Annually';
                else $name = $multiplier . ' Months'; // Fallback
                break;
            case 'year':
                $multiplier = $this->faker->numberBetween(1, 5);
                $name = $multiplier . ' Year(s)';
                if ($multiplier === 1) $name = 'Annually';
                elseif ($multiplier === 2) $name = 'Biennially';
                break;
        }

        return [
            'name' => $name,
            'multiplier' => $multiplier,
            'type' => $type,
            // 'is_active' => true, // Assuming you might add this field later based on other factories
        ];
    }
}
