<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop a unique antiga (estagiario, ano, mes, papel) — agora podem
        // existir múltiplas assinaturas pro mesmo papel/mês: uma vigente
        // (substituida_em IS NULL) e zero ou mais substituídas. A
        // unicidade da vigente passa a ser garantida pelo service
        // (AssinaturaService::garantirNaoAssinadoAinda).
        Schema::table('assinaturas', function (Blueprint $table) {
            $table->dropUnique('uq_assinaturas_papel');
        });

        Schema::table('assinaturas', function (Blueprint $table) {
            $table->timestamp('substituida_em')->nullable()->after('ip');
            $table->index(
                ['estagiario_id', 'ano', 'mes', 'papel'],
                'idx_assinaturas_papel_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::table('assinaturas', function (Blueprint $table) {
            $table->dropIndex('idx_assinaturas_papel_lookup');
            $table->dropColumn('substituida_em');
        });

        Schema::table('assinaturas', function (Blueprint $table) {
            $table->unique(
                ['estagiario_id', 'ano', 'mes', 'papel'],
                'uq_assinaturas_papel'
            );
        });
    }
};
