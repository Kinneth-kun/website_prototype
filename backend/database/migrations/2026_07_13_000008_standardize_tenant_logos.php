<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        foreach (DB::table('tenants')->whereNotNull('logo_url')->get() as $tenant) {
            $filename=pathinfo($tenant->logo_url,PATHINFO_FILENAME).'.webp';
            DB::table('tenants')->where('id',$tenant->id)->update([
                'logo_url'=>'images/tenants/standardized/'.$filename,
                'updated_at'=>now(),
            ]);
        }
    }

    public function down(): void { /* Keep standardized public assets. */ }
};
