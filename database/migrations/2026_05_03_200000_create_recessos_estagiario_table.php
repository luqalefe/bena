<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recessos_estagiario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estagiario_id')
                ->constrained('estagiarios')
                ->cascadeOnDelete();
            $table->date('inicio');
            $table->date('fim');
            $table->string('observacao', 255)->nullable();
            $table->timestamps();

            // Identifiers ≤ 30 chars (compatibilidade Oracle).
            $table->index(['estagiario_id', 'inicio', 'fim'], 'idx_rec_estag_periodo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recessos_estagiario');
    }
};
