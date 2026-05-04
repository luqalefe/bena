<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Estagiários importados do CSV podem não ter email — e, sem email,
        // não há como derivar username. Mantemos a row e o admin preenche
        // pela UI quando o estagiário aparecer.
        Schema::table('estagiarios', function (Blueprint $table) {
            $table->string('username', 100)->nullable()->change();
            $table->string('email', 200)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('estagiarios', function (Blueprint $table) {
            $table->string('username', 100)->nullable(false)->change();
            $table->string('email', 200)->nullable(false)->change();
        });
    }
};
