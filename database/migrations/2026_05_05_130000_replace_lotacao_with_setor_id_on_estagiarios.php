<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estagiarios', function (Blueprint $table) {
            $table->unsignedBigInteger('setor_id')->nullable()->after('matricula');
            $table->index('setor_id', 'idx_est_setor_id');
        });

        $this->migrarLotacaoParaSetorId();

        Schema::table('estagiarios', function (Blueprint $table) {
            $table->dropIndex('idx_estagiarios_lotacao');
            $table->dropColumn('lotacao');
        });
    }

    public function down(): void
    {
        Schema::table('estagiarios', function (Blueprint $table) {
            $table->string('lotacao', 100)->nullable()->after('matricula');
            $table->index('lotacao', 'idx_estagiarios_lotacao');
        });

        $this->reverterSetorIdParaLotacao();

        Schema::table('estagiarios', function (Blueprint $table) {
            $table->dropIndex('idx_est_setor_id');
            $table->dropColumn('setor_id');
        });
    }

    private function migrarLotacaoParaSetorId(): void
    {
        $siglas = DB::table('setores')->pluck('id', 'sigla')->all();
        if (empty($siglas)) {
            return;
        }

        $siglasNorm = [];
        foreach ($siglas as $sigla => $id) {
            $siglasNorm[$this->normalize((string) $sigla)] = $id;
        }

        $registros = DB::table('estagiarios')
            ->whereNotNull('lotacao')
            ->select('id', 'lotacao')
            ->get();

        foreach ($registros as $registro) {
            $norm = $this->normalize((string) $registro->lotacao);
            $setorId = $siglasNorm[$norm] ?? null;
            if ($setorId !== null) {
                DB::table('estagiarios')->where('id', $registro->id)->update(['setor_id' => $setorId]);
            }
        }
    }

    private function reverterSetorIdParaLotacao(): void
    {
        $siglasPorId = DB::table('setores')->pluck('sigla', 'id')->all();

        $registros = DB::table('estagiarios')
            ->whereNotNull('setor_id')
            ->select('id', 'setor_id')
            ->get();

        foreach ($registros as $registro) {
            $sigla = $siglasPorId[$registro->setor_id] ?? null;
            if ($sigla !== null) {
                DB::table('estagiarios')->where('id', $registro->id)->update(['lotacao' => $sigla]);
            }
        }
    }

    private function normalize(string $s): string
    {
        $s = mb_strtoupper($s);
        $s = strtr($s, [
            'Ã' => 'A', 'Á' => 'A', 'À' => 'A', 'Â' => 'A',
            'É' => 'E', 'Ê' => 'E',
            'Í' => 'I',
            'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'Ú' => 'U',
            'Ç' => 'C',
            'º' => 'ª',
        ]);
        $s = str_replace([' ', 'ZONA'], ['', 'ZE'], $s);

        return $s;
    }
};
