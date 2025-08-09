<?php

namespace Database\Factories;

use App\Models\HTE;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HTE>
 */
class HTEFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HTE::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->hte(),
            'company_name' => fake()->company(),
            'company_address' => fake()->address(),
            'company_email' => fake()->companyEmail(),
            'cperson_fname' => fake()->firstName(),
            'cperson_lname' => fake()->lastName(),
            'cperson_position' => fake()->randomElement(['HR Manager', 'Talent Manager', 'Recruitment Specialist', 'HR Director', 'Talent Acquisition']),
            'cperson_contactnum' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the HTE is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
