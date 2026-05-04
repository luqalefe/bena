<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estagiarios', function (Blueprint $table) {
            $table->foreignId('supervisor_id')
                ->nullable()
                ->after('supervisor_username')
                ->constrained('supervisores')
                ->nullOnDelete();

            $table->string('instituicao_ensino', 200)->nullable()->after('sei');
            $table->date('prorrogacao_inicio')->nullable()->after('fim_estagio');
            $table->date('prorrogacao_fim')->nullable()->after('prorrogacao_inicio');

            $table->index('supervisor_id', 'idx_estag_supervisor_id');
        });
    }

    public function down(): void
    {
        Schema::table('estagiarios', function (Blueprint $table) {
            $table->dropIndex('idx_estag_supervisor_id');
            $table->dropConstrainedForeignId('supervisor_id');
            $table->dropColumn(['instituicao_ensino', 'prorrogacao_inicio', 'prorrogacao_fim']);
        });
    }
};
