<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str; // <--- NO OLVIDES ESTA IMPORTACIÓN

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Nombre descriptivo aleatorio
            'name' => 'Materia ' . $this->faker->word() . ' ' . Str::random(5),
            
            // Genera una cadena aleatoria de 10 caracteres (Ej: SUB-X7K9P2M4L1)
            // La probabilidad de colisión aquí es prácticamente nula.
            'code' => 'SUB-' . Str::upper(Str::random(10)),
            
            'semester' => $this->faker->numberBetween(1, 9),
            'credits' => $this->faker->numberBetween(4, 8),
        ];
    }
}