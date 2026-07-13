<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('editor');
            $table->string('status')->default('active');
            $table->string('api_token_hash', 64)->nullable()->unique();
            $table->timestamp('last_login_at')->nullable();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->string('name')->unique(); $table->string('slug')->unique();
            $table->text('description')->nullable(); $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('floors', function (Blueprint $table) {
            $table->id(); $table->string('name')->unique(); $table->string('slug')->unique();
            $table->integer('floor_number')->nullable(); $table->text('description')->nullable();
            $table->string('map_image_url')->nullable(); $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('tenants', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('floor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location_detail')->nullable(); $table->string('lease_type')->nullable();
            $table->string('logo_url')->nullable(); $table->text('description')->nullable();
            $table->string('phone')->nullable(); $table->string('email')->nullable(); $table->string('website_url')->nullable();
            $table->string('status')->default('active'); $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('display_order')->default(0); $table->timestamp('published_at')->nullable();
            $table->timestamps(); $table->softDeletes();
        });
        Schema::create('leasing_spaces', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->string('slug')->unique(); $table->string('space_type');
            $table->foreignId('floor_id')->nullable()->constrained()->nullOnDelete(); $table->string('unit_number')->nullable();
            $table->decimal('area_sqm', 10, 2)->nullable(); $table->text('description'); $table->json('features')->nullable();
            $table->string('availability_status')->default('available'); $table->string('cover_image_url')->nullable();
            $table->boolean('is_featured')->default(false); $table->unsignedInteger('display_order')->default(0);
            $table->timestamp('published_at')->nullable(); $table->timestamps(); $table->softDeletes();
        });
        Schema::create('events', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->string('slug')->unique(); $table->text('summary')->nullable();
            $table->longText('description')->nullable(); $table->string('venue')->nullable();
            $table->dateTime('start_datetime'); $table->dateTime('end_datetime')->nullable();
            $table->string('cover_image_url')->nullable(); $table->string('registration_url')->nullable();
            $table->string('status')->default('draft'); $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('display_order')->default(0); $table->timestamp('published_at')->nullable();
            $table->timestamps(); $table->softDeletes();
        });
        Schema::create('promotions', function (Blueprint $table) {
            $table->id(); $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title'); $table->string('slug')->unique(); $table->text('summary')->nullable();
            $table->longText('description')->nullable(); $table->longText('terms')->nullable();
            $table->date('start_date'); $table->date('end_date'); $table->string('cover_image_url')->nullable();
            $table->string('status')->default('draft'); $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('display_order')->default(0); $table->timestamp('published_at')->nullable();
            $table->timestamps(); $table->softDeletes();
        });
        Schema::create('services', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug')->unique();
            $table->text('description')->nullable(); $table->foreignId('floor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location_detail')->nullable(); $table->string('icon')->nullable(); $table->string('cover_image_url')->nullable();
            $table->string('status')->default('active'); $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('display_order')->default(0); $table->timestamp('published_at')->nullable();
            $table->timestamps(); $table->softDeletes();
        });
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id(); $table->string('reference_number')->unique(); $table->string('inquiry_type');
            $table->foreignId('leasing_space_id')->nullable()->constrained()->nullOnDelete(); $table->string('name');
            $table->string('company_name')->nullable(); $table->string('email'); $table->string('phone')->nullable();
            $table->date('preferred_date')->nullable(); $table->string('subject')->nullable(); $table->text('message');
            $table->string('status')->default('new'); $table->string('priority')->default('normal');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable(); $table->timestamp('resolved_at')->nullable(); $table->timestamps();
        });
        Schema::create('mall_hours', function (Blueprint $table) {
            $table->id(); $table->unsignedTinyInteger('day_of_week')->unique(); $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable(); $table->boolean('is_closed')->default(false); $table->string('label')->nullable();
            $table->timestamps();
        });
        Schema::create('special_hours', function (Blueprint $table) {
            $table->id(); $table->date('date')->unique(); $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable(); $table->boolean('is_closed')->default(false);
            $table->string('title'); $table->text('description')->nullable(); $table->timestamps();
        });
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id(); $table->string('group'); $table->string('key')->unique(); $table->text('value')->nullable();
            $table->string('value_type')->default('string'); $table->boolean('is_public')->default(true); $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('action');
            $table->string('entity_type'); $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable(); $table->json('new_values')->nullable(); $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach (['audit_logs','site_settings','special_hours','mall_hours','inquiries','services','promotions','events','leasing_spaces','tenants','floors','categories'] as $table) Schema::dropIfExists($table);
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role','status','api_token_hash','last_login_at']));
    }
};
