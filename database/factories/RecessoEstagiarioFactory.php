<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Estagiario;
use App\Models\RecessoEstagiario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecessoEstagiario>
 */
class RecessoEstagiarioFactory extends Factory
{
    protected $model = RecessoEstagiario::class;

    public function definition(): array
    {
        return [
            'estagiario_id' => Estagiario::factory(),
            'inicio' => '2026-07-01',
            'fim' => '2026-07-30',
            'observacao' => null,
        ];
    }
}
