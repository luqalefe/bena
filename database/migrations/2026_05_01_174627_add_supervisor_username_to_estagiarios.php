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
            $table->string('supervisor_username', 100)->nullable()->after('supervisor_nome');
            $table->index('supervisor_username', 'idx_estag_supervisor_user');
        });
    }

    public function down(): void
    {
        Schema::table('estagiarios', function (Blueprint $table) {
            $table->dropIndex('idx_estag_supervisor_user');
            $table->dropColumn('supervisor_username');
        });
    }
};
