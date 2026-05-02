<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feriados', function (Blueprint $table) {
            $table->id();

            $table->date('data');
            $table->string('descricao', 200);
            $table->string('tipo', 20);
            $table->string('uf', 2)->nullable();
            $table->boolean('recorrente')->default(false);

            $table->timestamps();

            $table->unique(['data', 'tipo'], 'uq_feriados_data_tipo');
            $table->index('data', 'idx_feriados_data');
            $table->index('tipo', 'idx_feriados_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feriados');
    }
};
