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
            $table->string('contrato_path', 255)->nullable()->after('horas_diarias');
        });
    }

    public function down(): void
    {
        Schema::table('estagiarios', function (Blueprint $table) {
            $table->dropColumn('contrato_path');
        });
    }
};
