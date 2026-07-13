<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('highlights', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->string('slug')->unique();
            $table->string('eyebrow')->nullable(); $table->text('description')->nullable();
            $table->json('images'); $table->string('button_text')->nullable(); $table->string('button_url')->nullable();
            $table->string('status')->default('active'); $table->unsignedInteger('display_order')->default(0);
            $table->timestamps(); $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('highlights'); }
};
