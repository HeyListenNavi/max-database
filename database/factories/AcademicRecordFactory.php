<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicRecord>
 */
class AcademicRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['passed', 'failed', 'enrolled']);

        // Definir calificación según el status
        $grade = match ($status) {
            'passed' => $this->faker->randomFloat(2, 70, 100), // Entre 70 y 100
            'failed' => $this->faker->randomFloat(2, 0, 69),   // Entre 0 y 69
            'enrolled' => null,
        };

        return [
            'user_id' => User::factory(),
            'subject_id' => Subject::factory(),
            'status' => $status,
            'grade' => $grade,
        ];
    }
}
