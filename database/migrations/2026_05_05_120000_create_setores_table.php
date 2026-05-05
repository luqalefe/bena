<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setores', function (Blueprint $table) {
            $table->id();
            $table->string('sigla', 30);
            $table->unsignedInteger('quantidade_servidores')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamp('sincronizado_em')->nullable();
            $table->timestamps();

            $table->unique('sigla', 'uq_setores_sigla');
            $table->index('ativo', 'idx_setores_ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setores');
    }
};
