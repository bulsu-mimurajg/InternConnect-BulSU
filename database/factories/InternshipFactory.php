<?php

namespace Database\Factories;

use App\Models\HTE;
use App\Models\Internship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Internship>
 */
class InternshipFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Internship::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $positions = [
            'Software Development Intern',
            'Data Science Intern',
            'Marketing Intern',
            'Human Resources Intern',
            'Finance Intern',
            'Graphic Design Intern',
            'Content Writing Intern',
            'Sales Intern',
            'Customer Service Intern',
            'Research Intern',
        ];

        $departments = [
            'Information Technology',
            'Marketing',
            'Human Resources',
            'Finance',
            'Operations',
            'Sales',
            'Customer Service',
            'Research & Development',
            'Legal',
            'Communications',
        ];

        return [
            'h_t_e_id' => HTE::factory(),
            'position_title' => fake()->randomElement($positions),
            'department' => fake()->randomElement($departments),
            'placement_description' => fake()->paragraphs(3, true),
            'slot_count' => fake()->numberBetween(1, 5),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the internship is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create an internship with specific slot count.
     */
    public function withSlots(int $slots): static
    {
        return $this->state(fn (array $attributes) => [
            'slot_count' => $slots,
        ]);
    }
}
