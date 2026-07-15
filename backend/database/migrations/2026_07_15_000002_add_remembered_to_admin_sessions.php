<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admin_sessions', function (Blueprint $table) {
            $table->boolean('remembered')->default(false)->after('token_hash');
        });
    }

    public function down(): void
    {
        Schema::table('admin_sessions', function (Blueprint $table) {
            $table->dropColumn('remembered');
        });
    }
};
