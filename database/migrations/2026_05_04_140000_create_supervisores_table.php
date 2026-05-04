<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisores', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 200);
            $table->string('username', 100)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('lotacao', 100)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique('username', 'uq_supervisores_username');
            $table->index('nome', 'idx_supervisores_nome');
            $table->index('ativo', 'idx_supervisores_ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisores');
    }
};
