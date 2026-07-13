<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('site_settings')->updateOrInsert(
            ['key' => 'social.indeed'],
            [
                'group' => 'social',
                'value' => 'https://ph.indeed.com/cmp/Geege-Central-Mall,-Inc.?campaignid=mobvjcmp&from=mobviewjob&tk=1jsb5sktmh12a800&fromjk=f5ec568918477b9e',
                'value_type' => 'string',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void { /* Preserve the latest administrator-approved URL. */ }
};
