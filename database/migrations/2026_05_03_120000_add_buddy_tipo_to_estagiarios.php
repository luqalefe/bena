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
            $table->string('buddy_tipo', 20)->nullable()->after('tutorial_visto_em');
        });
    }

    public function down(): void
    {
        Schema::table('estagiarios', function (Blueprint $table) {
            $table->dropColumn('buddy_tipo');
        });
    }
};
