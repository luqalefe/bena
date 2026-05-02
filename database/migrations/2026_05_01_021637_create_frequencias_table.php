<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frequencias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('estagiario_id')
                ->constrained('estagiarios')
                ->cascadeOnDelete();

            $table->date('data');
            // entrada/saida como string(8) HH:MM:SS — Oracle não tem TIME type
            // (o $table->time() vira DATE e quebra com ORA-01843 ao receber
            // só um horário). Acessor no model devolve Carbon.
            $table->string('entrada', 8)->nullable();
            $table->string('saida', 8)->nullable();

            $table->decimal('horas', 5, 2)->nullable();

            $table->string('ip_entrada', 45)->nullable();
            $table->string('ip_saida', 45)->nullable();

            $table->string('observacao', 500)->nullable();

            $table->timestamps();

            $table->unique(['estagiario_id', 'data'], 'uq_freq_estagiario_data');
            $table->index('data', 'idx_freq_data');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frequencias');
    }
};
