<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        User::where('email', 'admin@islandcentral.test')->delete();
        $adminEmail = env('ADMIN_EMAIL'); $adminPassword = env('ADMIN_PASSWORD');
        if ($adminEmail && $adminPassword) User::updateOrCreate(['email' => $adminEmail], ['name' => env('ADMIN_NAME', 'Website Administrator'), 'password' => Hash::make($adminPassword), 'role' => 'super_admin', 'status' => 'active']);

        $categories = ['Banking & Finance','Dining & Food','Entertainment','Gadgets & Tech','Government & Offices','Health & Wellness','Retail & Essentials','Services'];
        foreach ($categories as $i => $name) DB::table('categories')->updateOrInsert(['slug'=>Str::slug($name)], ['name'=>$name,'display_order'=>$i,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
        $floors = [['Basement 1',-1],['Ground Floor',0],['Second Floor',2],['Third Floor',3],['Fourth Floor',4],['Mall Exhibit Area',null]];
        foreach ($floors as $i => [$name,$number]) DB::table('floors')->updateOrInsert(['slug'=>Str::slug($name)], ['name'=>$name,'floor_number'=>$number,'display_order'=>$i,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);

        $categoryIds = DB::table('categories')->pluck('id','name'); $floorIds = DB::table('floors')->pluck('id','name');
        $tenants = json_decode(file_get_contents(database_path('seeders/data/tenants.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($tenants as $tenant) {
            DB::table('tenants')->updateOrInsert(['slug'=>Str::slug($tenant['name'])], [
                'name'=>$tenant['name'], 'category_id'=>$categoryIds[$tenant['category']]??null,
                'floor_id'=>$floorIds[$tenant['floor']]??null, 'location_detail'=>$tenant['location_detail'],
                'lease_type'=>$tenant['lease_type'], 'logo_url'=>$tenant['logo_url'],
                'description'=>$tenant['description'], 'status'=>$tenant['status'],
                'is_featured'=>$tenant['is_featured'], 'display_order'=>$tenant['display_order'],
                'published_at'=>now(), 'created_at'=>now(), 'updated_at'=>now(),
            ]);
        }

        foreach ([1,2,3,4] as $day) DB::table('mall_hours')->updateOrInsert(['day_of_week'=>$day], ['opening_time'=>'10:00','closing_time'=>'21:00','is_closed'=>false,'updated_at'=>now(),'created_at'=>now()]);
        foreach ([5,6,0] as $day) DB::table('mall_hours')->updateOrInsert(['day_of_week'=>$day], ['opening_time'=>'09:00','closing_time'=>'21:00','is_closed'=>false,'updated_at'=>now(),'created_at'=>now()]);

        $settings = [
            'general.mall_name'=>'Island Central Mactan',
            'general.tagline'=>"Mactan's premier destination",
            'contact.email'=>'leasing.islandcentral@gmail.com',
            'contact.address'=>'Mactan, Cebu, Philippines',
            'contact.maps_embed_url'=>'https://www.google.com/maps?q=Island%20Central%20Mactan%2C%20Lapu-Lapu%20City%2C%20Cebu&output=embed',
            'contact.maps_directions_url'=>'https://www.google.com/maps/search/?api=1&query=Island%20Central%20Mactan%2C%20Lapu-Lapu%20City%2C%20Cebu',
            'about.introduction'=>'Island Central Mactan - I.T. Complex is a dynamic mixed-use development in the Mactan Economic Zone in Lapu-Lapu City, Cebu. Inaugurated in 2016 and positioned across Mactan-Cebu International Airport, it brings together retail, dining, services, entertainment, and business facilities in one accessible island destination.',
            'about.story'=>'Founded on June 26, 2013, Island Central Mactan began mall operations on December 8, 2016. Today, it serves residents, workers, and visitors with more than 100 tenants and a balanced lifestyle experience shaped by the convenience and character of island living.',
            'about.accessibility'=>'Strategically located in the Mactan Economic Zone, the mall is approximately two kilometers from Mactan-Cebu International Airport and is accessible through modern and traditional transport routes.',
            'about.development'=>'A four-storey mixed-use destination with approximately 31,000 square meters of retail, dining, and entertainment space, supported by 400 parking slots.',
            'about.mission'=>"We commit to deliver excellent service that will exceed customer's expectations and ensure growth, financial stability, and sustainability. Thinking innovatively, building lasting relationships, and acting with genuine concern for all and the environment; incessantly providing means for our workers to develop their potentials to the fullest; and living the Company's shared values of integrity, passion for excellence, and family spirit.",
            'about.vision'=>"Island Central Mactan is established as a unique, significant lifestyle resort mall that makes a difference and spreads joy by offering infinite experiences. It's more than just a mall.",
        ];
        foreach ($settings as $key=>$value) DB::table('site_settings')->updateOrInsert(['key'=>$key], ['group'=>strtok($key,'.'),'value'=>$value,'is_public'=>true,'created_at'=>now(),'updated_at'=>now()]);

        $highlights = [
            ['Skybar','skybar','Skybar','Open-air views above Mactan.','images/skybar_images/',5,'Ask about Skybar','/inquire'],
            ['Cinema','cinema','Cinema','Comfortable screenings and convenient mall access.','images/cinema_images/',5,'View cinema schedule','https://cinedesk-icm.online/'],
            ['Inside Island Central','mall-gallery','The Mall','Bright spaces for shopping, dining and everyday errands.','images/mall_images/',4,'Explore the mall','/mall'],
        ];
        foreach ($highlights as $i => [$title,$slug,$eyebrow,$description,$folder,$count,$button,$url]) {
            $images=[]; for($n=1;$n<=$count;$n++) $images[]=$folder.str_pad((string)$n,3,'0',STR_PAD_LEFT).'.webp';
            DB::table('highlights')->updateOrInsert(['slug'=>$slug], ['title'=>$title,'eyebrow'=>$eyebrow,'description'=>$description,'images'=>json_encode($images),'button_text'=>$button,'button_url'=>$url,'status'=>'active','display_order'=>$i,'created_at'=>now(),'updated_at'=>now()]);
        }

        $spaces = [
            ['Retail Storefront Space at Island Central Mactan','Retail','High-visibility area ideal for lifestyle retail, service counters, convenience brands and showroom concepts.','images/mall_images/001.webp',['Strong frontage potential','Easy customer access','Professional mall setting']],
            ['Food and Beverage Space for Dining Concepts','Dining','Space suited for cafes, quick-service dining, dessert shops, snack counters and grab-and-go concepts.','images/mall_images/002.webp',['Repeat-visit potential','Compact operations','Airport-adjacent demand']],
            ['Service-ready Unit for Everyday Customer Needs','Service','Practical mall unit for wellness, beauty, clinics, repairs, payment centers and professional services.','images/mall_images/003.webp',['Flexible fit-out','Useful for daily errands','Managed commercial environment']],
        ];
        foreach($spaces as $i=>[$title,$type,$description,$image,$features]) DB::table('leasing_spaces')->updateOrInsert(['slug'=>Str::slug($title)], ['title'=>$title,'space_type'=>$type,'description'=>$description,'features'=>json_encode($features),'availability_status'=>'available','cover_image_url'=>$image,'is_featured'=>true,'display_order'=>$i,'published_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);

        $services = [['Customer Support','Questions, directions and visit assistance from the mall team.','help-circle'],['Parking Access','Convenient arrival for shoppers, tenants and business guests.','car'],['Maintenance','Property upkeep for a clean and professional setting.','wrench'],['Leasing Assistance','Guidance for availability, fit-out needs and site visits.','building'],['Marketing Assistance','Support for events, activities and brand collaborations.','megaphone'],['Cinema','Movie schedules, screenings and cinema information.','film']];
        foreach($services as $i=>[$name,$description,$icon]) DB::table('services')->updateOrInsert(['slug'=>Str::slug($name)], ['name'=>$name,'description'=>$description,'icon'=>$icon,'cover_image_url'=>$name==='Cinema'?'images/general_images/cinema_background.webp':'images/general_images/services_background.webp','action_label'=>$name==='Cinema'?'View cinema schedule':null,'action_url'=>$name==='Cinema'?'https://cinedesk-icm.online/':null,'status'=>'active','is_featured'=>true,'display_order'=>$i,'published_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);

        $events = [['Food Finds Festival','Sample dining bundles, coffee stops and family-friendly food promos from selected tenants.','Main Atrium','2026-07-08 10:00:00','2026-07-31 21:00:00'],['Cinema Movie Schedules','Check the latest screenings and plan your next movie visit.','ICM Cinema','2026-07-01 09:00:00','2027-12-31 21:00:00'],['Back-to-School Essentials Weekend','School supplies, gadgets, snacks and family errands in one useful campaign.','Mall-wide','2026-08-10 10:00:00','2026-08-12 21:00:00'],['Community Weekend Market','A family-friendly fair for local makers, food sellers and service brands.','Event Area','2026-09-05 10:00:00','2026-09-06 21:00:00']];
        foreach($events as $i=>[$title,$summary,$venue,$start,$end]) DB::table('events')->updateOrInsert(['slug'=>Str::slug($title)], ['title'=>$title,'summary'=>$summary,'venue'=>$venue,'start_datetime'=>$start,'end_datetime'=>$end,'cover_image_url'=>'images/mall_images/'.str_pad((string)(($i%4)+1),3,'0',STR_PAD_LEFT).'.webp','status'=>'published','is_featured'=>$i===0,'display_order'=>$i,'published_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);

        $promotions = [['Shop, Stamp & Save','Collect stamps from participating stores and redeem rewards at the promo booth.','2026-07-01','2026-08-31'],['Weekend Dining Finds','Discover selected weekend offers from cafes, quick bites and family restaurants.','2026-07-01','2026-12-31'],['Pop-ups & Brand Activations','Product launches, sampling booths, mini fairs and community-led activities.','2026-07-01','2027-06-30']];
        foreach($promotions as $i=>[$title,$summary,$start,$end]) DB::table('promotions')->updateOrInsert(['slug'=>Str::slug($title)], ['title'=>$title,'summary'=>$summary,'start_date'=>$start,'end_date'=>$end,'cover_image_url'=>'images/mall_images/'.str_pad((string)(($i%4)+1),3,'0',STR_PAD_LEFT).'.webp','status'=>'active','is_featured'=>$i===0,'display_order'=>$i,'published_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
    }
}
