<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assinaturas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('estagiario_id')->constrained('estagiarios')->cascadeOnDelete();
            $table->unsignedSmallInteger('ano');
            $table->unsignedTinyInteger('mes');
            $table->string('papel', 20); // 'estagiario' | 'supervisor'
            $table->string('assinante_username', 100);
            $table->text('snapshot');         // JSON canônico do mês assinado
            $table->string('hash', 64);       // SHA-256 hex
            $table->timestamp('assinado_em');
            $table->string('ip', 45)->nullable();

            $table->timestamps();

            $table->unique(['estagiario_id', 'ano', 'mes', 'papel'], 'uq_assinaturas_papel');
            $table->index(['estagiario_id', 'ano', 'mes'], 'idx_assinaturas_periodo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assinaturas');
    }
};
