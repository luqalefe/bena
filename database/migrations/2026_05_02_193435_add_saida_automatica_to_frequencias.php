<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frequencias', function (Blueprint $table) {
            $table->boolean('saida_automatica')->default(false)->after('observacao');
        });
    }

    public function down(): void
    {
        Schema::table('frequencias', function (Blueprint $table) {
            $table->dropColumn('saida_automatica');
        });
    }
};
