<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Feriados nacionais brasileiros — fixos. As datas móveis (Carnaval, Sexta
 * Santa, Corpus Christi) ficam fora deste seeder; o admin cadastra ano a ano
 * via UI (H9).
 */
class FeriadosNacionaisSeeder extends Seeder
{
    public function run(): void
    {
        $hoje = now();

        $feriados = [
            ['data' => '2026-01-01', 'descricao' => 'Confraternização Universal'],
            ['data' => '2026-04-21', 'descricao' => 'Tiradentes'],
            ['data' => '2026-05-01', 'descricao' => 'Dia do Trabalho'],
            ['data' => '2026-09-07', 'descricao' => 'Independência do Brasil'],
            ['data' => '2026-10-12', 'descricao' => 'Nossa Senhora Aparecida'],
            ['data' => '2026-11-02', 'descricao' => 'Finados'],
            ['data' => '2026-11-15', 'descricao' => 'Proclamação da República'],
            ['data' => '2026-11-20', 'descricao' => 'Consciência Negra'],
            ['data' => '2026-12-25', 'descricao' => 'Natal'],
        ];

        foreach ($feriados as $f) {
            DB::table('feriados')->updateOrInsert(
                ['data' => $f['data'], 'tipo' => 'nacional'],
                [
                    'descricao' => $f['descricao'],
                    'recorrente' => true,
                    'updated_at' => $hoje,
                    'created_at' => $hoje,
                ]
            );
        }
    }
}
