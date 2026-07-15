<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('trade_name')->nullable()->after('id');
            $table->string('industry_name')->nullable()->after('trade_name');
            $table->text('company_address')->nullable()->after('industry_name');
            $table->string('email_address')->nullable()->after('company_address');
            $table->text('nature_of_business')->nullable()->after('email_address');
            $table->longText('approved_products')->nullable()->after('nature_of_business');
            $table->longText('picture_of_branches')->nullable()->after('approved_products');
            $table->longText('picture_of_menu')->nullable()->after('picture_of_branches');
        });

        $industries = DB::table('categories')->pluck('name', 'id');
        DB::table('tenants')->orderBy('id')->chunkById(100, function ($tenants) use ($industries) {
            foreach ($tenants as $tenant) {
                DB::table('tenants')->where('id', $tenant->id)->update([
                    'trade_name' => $tenant->name,
                    'industry_name' => $tenant->category_id ? ($industries[$tenant->category_id] ?? null) : null,
                    'company_address' => $tenant->location_detail,
                    'email_address' => $tenant->email,
                    'nature_of_business' => $tenant->description,
                    'picture_of_branches' => $tenant->logo_url,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['trade_name', 'industry_name', 'company_address', 'email_address', 'nature_of_business', 'approved_products', 'picture_of_branches', 'picture_of_menu']);
        });
    }
};
