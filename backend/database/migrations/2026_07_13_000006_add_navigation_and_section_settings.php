<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $settings=[
            'navigation.primary'=>json_encode([['/','Home'],['/mall','Mall'],['/leasing','Leasing'],['/about','About Us'],['/services','Services'],['/inquire','Inquire']]),
            'navigation.mall'=>json_encode([['/directory','Directory'],['/events','Events & Promos']]),
            'footer.discover_links'=>json_encode([['/mall','The Mall'],['/directory','Directory'],['/events','Events & Promos'],['/services','Services'],['/leasing','Leasing Spaces'],['/about','About Island Central']]),
            'footer.connect_links'=>json_encode([['/leasing','Retail Leasing'],['/leasing','Dining Leasing'],['/events','Events & Collaborations'],['/inquire','Contact Us'],['/','Back to Home']]),
            'footer.legal_links'=>json_encode([['/privacy-policy','Privacy Policy'],['/terms-of-service','Terms of Service'],['/cookies-policy','Cookies Policy']]),
            'home.welcome_eyebrow'=>'Welcome','home.welcome_title'=>'A complete destination for shoppers, tenants, and the Mactan community.',
            'directory.eyebrow'=>'Mall Directory','directory.title'=>'Mall directory','directory.description'=>'Find dining, retail, services, and entertainment throughout the mall.','directory.background_url'=>'images/mall_images/002.webp',
            'hours.title'=>'Open daily for shopping, dining, services, and entertainment.','hours.schedule'=>'10:00 AM–9:00 PM Monday–Thursday · 9:00 AM–9:00 PM Friday–Sunday',
            'location.title'=>'Visit Island Central Mactan.','location.description'=>'Strategically positioned in the Mactan Economic Zone in Lapu-Lapu City, Cebu.','location.landmark'=>'Across Mactan-Cebu International Airport.',
        ];
        foreach($settings as $key=>$value)DB::table('site_settings')->updateOrInsert(['key'=>$key],['group'=>strtok($key,'.'),'value'=>$value,'value_type'=>str_ends_with($key,'links')||str_starts_with($key,'navigation.')?'json':'string','is_public'=>true,'created_at'=>now(),'updated_at'=>now()]);
    }
    public function down(): void { /* Preserve administrator-edited content. */ }
};
