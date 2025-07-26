<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->student()->create([
            'password' => bcrypt('password'),
        ]);
        $sections = ['3A', '3B', '3C'];
        $specialization = ['BA', 'SM', 'WMAD'];

        return [
            'user_id' => $user->id,
            'student_number' => $this->generateStudentNumber(),
            'first_name' => $this->faker->firstName,
            'middle_name' => $this->faker->streetName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'section' => $this->faker->randomElement($sections),
            'specialization' => $this->faker->randomElement($specialization),
            'address' => $this->faker->address,
            'birth_date' => $this->faker->date(),
        ];
    }

    private function generateStudentNumber(): string
    {
        $year = date('Y');
        $randomNumber = $this->faker->unique()->numberBetween(0, 999999);
        return $year . str_pad($randomNumber, 6, '0', STR_PAD_LEFT);
    }
}
