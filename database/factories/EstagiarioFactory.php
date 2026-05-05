<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Estagiario;
use App\Models\Setor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estagiario>
 */
class EstagiarioFactory extends Factory
{
    protected $model = Estagiario::class;

    public function definition(): array
    {
        $primeiro = fake()->firstName();
        $sobrenome = fake()->lastName();

        return [
            'username' => strtolower($primeiro).'.'.strtolower($sobrenome),
            'nome' => $primeiro.' '.$sobrenome,
            'email' => strtolower($primeiro).'.'.strtolower($sobrenome).'@example.local',
            'matricula' => fake()->numerify('EST#####'),
            'setor_id' => Setor::query()->inRandomOrder()->value('id')
                ?? Setor::create(['sigla' => fake()->unique()->lexify('SET???'), 'ativo' => true])->id,
            'supervisor_nome' => fake()->name(),
            'supervisor_username' => null,
            'sei' => fake()->numerify('SEI-#####/2026'),
            'inicio_estagio' => fake()->dateTimeBetween('-6 months', '-1 month')->format('Y-m-d'),
            'fim_estagio' => fake()->dateTimeBetween('+6 months', '+1 year')->format('Y-m-d'),
            'horas_diarias' => 5.00,
            'ativo' => true,
            // Default: tutorial já visto. Testes que envolvem onboarding
            // chamam ->semOnboarding() para representar primeiro acesso.
            'tutorial_visto_em' => now(),
        ];
    }

    public function inativo(): static
    {
        return $this->state(['ativo' => false]);
    }

    public function admin(): static
    {
        return $this->state(['username' => 'admin.'.fake()->userName()]);
    }

    public function semOnboarding(): static
    {
        return $this->state(['tutorial_visto_em' => null]);
    }

    public function comBuddy(string $tipo = 'coruja'): static
    {
        return $this->state(['buddy_tipo' => $tipo]);
    }
}
