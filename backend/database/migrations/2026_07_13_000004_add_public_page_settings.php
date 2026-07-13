<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $settings = [
            'general.logo_url'=>'images/general_images/icm_logo_transparent.png',
            'home.eyebrow'=>'Island Central Mactan','home.title'=>"More than just a mall, it's living the island life.",'home.description'=>"A vibrant mall destination for daily essentials, restaurants, entertainment, and brands ready to grow with Mactan's community.",'home.background_url'=>'images/general_images/icm.png','home.primary_label'=>'View Leasing','home.primary_url'=>'/leasing','home.secondary_label'=>'Explore the Mall','home.secondary_url'=>'/mall',
            'mall.eyebrow'=>'Shop & Dine','mall.title'=>'Everything you need, all in one place.','mall.description'=>'Curated retail, dining, entertainment, and everyday essentials designed for convenience, comfort, and island living.','mall.background_url'=>'images/mall_images/001.png',
            'leasing.eyebrow'=>'Leasing Opportunities','leasing.title'=>'Put your brand where Mactan moves daily.','leasing.description'=>'Lease retail, dining, and service spaces inside a visible mall destination across Mactan-Cebu International Airport.','leasing.background_url'=>'images/mall_images/003.png',
            'events.eyebrow'=>'Events & Promos','events.title'=>'Fresh reasons to visit Island Central Mactan.','events.description'=>'Discover current happenings, community activities, and mall-wide offers.','events.background_url'=>'images/mall_images/002.png',
            'services.eyebrow'=>'Services','services.title'=>'Designed for visitors, tenants, and day-to-day operations.','services.description'=>'Support services that help make every visit and business operation smoother.','services.background_url'=>'images/mall_images/004.png',
            'about.eyebrow'=>'About Island Central','about.title'=>'More than a mall in the heart of Mactan.','about.background_url'=>'images/mall_images/002.png',
            'inquire.eyebrow'=>'Contact Us','inquire.title'=>"Let's connect",'inquire.description'=>'Send an inquiry and the appropriate team will follow up.','inquire.background_url'=>'images/mall_images/003.png',
            'footer.tagline'=>"Mactan's premier destination",'footer.copyright'=>'Island Central Mactan. All rights reserved.','social.facebook'=>'https://www.facebook.com/islandcentralmactanofficial','social.instagram'=>'https://www.instagram.com/islandcentralmactanofficial','social.tiktok'=>'https://www.tiktok.com/@islandcentralmactanofficial','social.indeed'=>'https://ph.indeed.com/cmp/Geege-Central-Mall,-Inc.?campaignid=mobvjcmp&from=mobviewjob&tk=1jsb5sktmh12a800&fromjk=f5ec568918477b9e',
            'seo.default_title'=>'Island Central Mactan','seo.default_description'=>'Discover shopping, dining, entertainment, services, events, and leasing opportunities at Island Central Mactan.','seo.og_image'=>'images/general_images/icm.png',
        ];
        foreach ($settings as $key=>$value) DB::table('site_settings')->updateOrInsert(['key'=>$key],['group'=>strtok($key,'.'),'value'=>$value,'value_type'=>'string','is_public'=>true,'created_at'=>now(),'updated_at'=>now()]);
    }

    public function down(): void { /* Preserve administrator-edited site content on rollback. */ }
};
