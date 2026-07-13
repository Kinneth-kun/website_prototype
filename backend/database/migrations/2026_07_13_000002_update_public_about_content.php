<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private function content(): array
    {
        return [
            'about.introduction' => 'Island Central Mactan - I.T. Complex is a dynamic mixed-use development in the Mactan Economic Zone in Lapu-Lapu City, Cebu. Inaugurated in 2016 and positioned across Mactan-Cebu International Airport, it brings together retail, dining, services, entertainment, and business facilities in one accessible island destination.',
            'about.story' => 'Founded on June 26, 2013, Island Central Mactan began mall operations on December 8, 2016. Today, it serves residents, workers, and visitors with more than 100 tenants and a balanced lifestyle experience shaped by the convenience and character of island living.',
            'about.accessibility' => 'Strategically located in the Mactan Economic Zone, the mall is approximately two kilometers from Mactan-Cebu International Airport and is accessible through modern and traditional transport routes.',
            'about.development' => 'A four-storey mixed-use destination with approximately 31,000 square meters of retail, dining, and entertainment space, supported by 400 parking slots.',
            'about.mission' => "We commit to deliver excellent service that exceeds customers' expectations and supports growth, financial stability, and sustainability. We think innovatively, build lasting relationships, care for people and the environment, help our workers develop their potential, and live our shared values of integrity, passion for excellence, and family spirit.",
            'about.vision' => "Island Central Mactan is established as a unique, significant lifestyle resort mall that makes a difference and spreads joy by offering infinite experiences. It's more than just a mall.",
        ];
    }

    public function up(): void
    {
        foreach ($this->content() as $key => $value) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $key],
                ['group' => 'about', 'value' => $value, 'value_type' => 'string', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', ['about.story', 'about.accessibility', 'about.development'])->delete();
    }
};
