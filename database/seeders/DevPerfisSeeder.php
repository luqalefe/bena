<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Estagiario;
use Illuminate\Database\Seeder;

/**
 * Seeder de DEV: monta o trio "lucas.dev / lucas.supervisor / lucas.rh"
 * pra testar os 3 papéis sem subir Authelia. Idempotente.
 *
 * Em prod nada disso entra: os registros de estagiário são criados pelo
 * middleware ConfigureUserSession no primeiro login, e o
 * supervisor_username é preenchido pelo RH via H16.
 */
class DevPerfisSeeder extends Seeder
{
    public function run(): void
    {
        Estagiario::updateOrCreate(
            ['username' => 'lucas.dev'],
            [
                'nome' => 'Lucas Dev',
                'email' => 'lucas.dev@example.local',
                'matricula' => 'EST00001',
                'lotacao' => 'STI',
                'supervisor_nome' => 'Lucas Dev (auto)',
                'supervisor_username' => 'lucas.dev',
                'sei' => 'SEI-DEV-00001/2026',
                'horas_diarias' => 5.00,
                'ativo' => true,
                // Em dev, o autor sempre encarna a própria carta lendária.
                'buddy_tipo' => 'lucas',
            ]
        );

        Estagiario::updateOrCreate(
            ['username' => 'lucas.supervisor'],
            [
                'nome' => 'Lucas Supervisor',
                'email' => 'lucas.supervisor@example.local',
                'horas_diarias' => 5.00,
                'ativo' => false, // supervisor não é estagiário
            ]
        );

        Estagiario::updateOrCreate(
            ['username' => 'lucas.rh'],
            [
                'nome' => 'Lucas RH',
                'email' => 'lucas.rh@example.local',
                'horas_diarias' => 5.00,
                'ativo' => false, // RH não é estagiário
            ]
        );
    }
}
