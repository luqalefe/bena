<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('usuario_username', 100);
            $table->string('acao', 50);
            $table->string('entidade', 30);
            $table->string('entidade_id', 50)->nullable();
            $table->text('payload')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Identifiers ≤ 30 chars (compatibilidade Oracle).
            $table->index('usuario_username', 'idx_aud_usuario');
            $table->index('acao', 'idx_aud_acao');
            $table->index('entidade', 'idx_aud_entidade');
            $table->index('created_at', 'idx_aud_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
