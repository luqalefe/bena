<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Supervisor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supervisor>
 */
class SupervisorFactory extends Factory
{
    protected $model = Supervisor::class;

    public function definition(): array
    {
        $primeiro = fake()->firstName();
        $sobrenome = fake()->lastName();

        return [
            'nome' => $primeiro.' '.$sobrenome,
            'username' => strtolower($primeiro).'.'.strtolower($sobrenome),
            'email' => strtolower($primeiro).'.'.strtolower($sobrenome).'@tre-ac.jus.br',
            'lotacao' => fake()->randomElement(['SECEP', 'ASCOM', 'GADG', 'SEDES']),
            'ativo' => true,
        ];
    }

    public function inativo(): static
    {
        return $this->state(['ativo' => false]);
    }

    public function semContato(): static
    {
        return $this->state([
            'username' => null,
            'email' => null,
        ]);
    }
}
