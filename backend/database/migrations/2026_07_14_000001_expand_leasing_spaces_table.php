<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leasing_spaces', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->default(1)->after('id');
            $table->string('unit_code', 80)->nullable()->after('branch_id');
            $table->string('unit_name')->nullable()->after('unit_code');
            $table->string('floor_level', 120)->nullable()->after('unit_name');
            $table->text('location_description')->nullable()->after('floor_level');
            $table->decimal('floor_area_sqm', 10, 2)->nullable()->after('location_description');
            $table->decimal('base_rate_sqm', 12, 2)->nullable()->after('floor_area_sqm');
            $table->decimal('cusa_rate_sqm', 12, 2)->nullable()->after('base_rate_sqm');
            $table->decimal('ads_fee_sqm', 12, 2)->nullable()->after('cusa_rate_sqm');
            $table->boolean('is_vatable')->default(false)->after('ads_fee_sqm');
            $table->string('status', 40)->default('available')->after('is_vatable');
        });

        $floorNames = DB::table('floors')->pluck('name', 'id');

        DB::table('leasing_spaces')->orderBy('id')->chunkById(100, function ($spaces) use ($floorNames) {
            foreach ($spaces as $space) {
                $legacyStatus = strtolower(trim((string) $space->availability_status));
                $status = match (str_replace([' ', '-'], '_', $legacyStatus)) {
                    '', 'available' => 'available',
                    'occupied', 'leased', 'unavailable' => 'occupied',
                    'reserved', 'hold', 'on_hold' => 'reserved',
                    'under_maintenance', 'maintenance', 'under_repair' => 'under_maintenance',
                    default => 'occupied',
                };

                DB::table('leasing_spaces')->where('id', $space->id)->update([
                    'branch_id' => 1,
                    'unit_code' => $space->unit_number ?: 'SPACE-'.str_pad((string) $space->id, 4, '0', STR_PAD_LEFT),
                    'unit_name' => $space->title,
                    'floor_level' => $space->floor_id
                        ? (str_starts_with(strtolower((string) ($floorNames[$space->floor_id] ?? '')), 'basement') ? 'Basement' : ($floorNames[$space->floor_id] ?? null))
                        : null,
                    'location_description' => $space->description,
                    'floor_area_sqm' => $space->area_sqm,
                    'status' => $status,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('leasing_spaces', function (Blueprint $table) {
            $table->dropColumn([
                'branch_id',
                'unit_code',
                'unit_name',
                'floor_level',
                'location_description',
                'floor_area_sqm',
                'base_rate_sqm',
                'cusa_rate_sqm',
                'ads_fee_sqm',
                'is_vatable',
                'status',
            ]);
        });
    }
};
