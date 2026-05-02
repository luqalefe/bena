<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estagiarios', function (Blueprint $table) {
            $table->id();

            $table->string('username', 100);
            $table->string('nome', 200);
            $table->string('email', 200);

            $table->string('matricula', 30)->nullable();
            $table->string('lotacao', 100)->nullable();
            $table->string('supervisor_nome', 200)->nullable();
            $table->string('sei', 50)->nullable();

            $table->date('inicio_estagio')->nullable();
            $table->date('fim_estagio')->nullable();
            $table->decimal('horas_diarias', 4, 2)->default(5.00);

            $table->boolean('ativo')->default(true);

            $table->timestamps();

            $table->unique('username', 'uq_estagiarios_username');
            $table->index('lotacao', 'idx_estagiarios_lotacao');
            $table->index('ativo', 'idx_estagiarios_ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estagiarios');
    }
};
