<?php

namespace Database\Factories;

use App\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductType>
 */
class ProductTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = $this->faker->unique()->randomElement(['Shared Hosting', 'VPS Hosting', 'Dedicated Server', 'Domain Registration', 'SSL Certificate', 'Email Hosting']);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence,
            'requires_domain' => $this->faker->boolean(75), // Higher chance of requiring a domain
            'creates_service_instance' => $this->faker->boolean(90), // Higher chance of creating a service instance
            'is_active' => true,
        ];
    }
}
