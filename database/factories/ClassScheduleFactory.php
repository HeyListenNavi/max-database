<?php

namespace Database\Factories;

use App\Models\CourseSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassSchedule>
 */
class ClassScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Escoger una hora de inicio entre las 7 AM (7) y las 7 PM (19)
        $startHour = $this->faker->numberBetween(7, 19);
        $duration = $this->faker->numberBetween(1, 2); // Duran 1 o 2 horas

        return [
            'course_section_id' => CourseSection::factory(),
            'day_of_week' => $this->faker->randomElement(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),

            // Formateamos la hora para que quede H:00:00
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', $startHour + $duration),
        ];
    }
}
