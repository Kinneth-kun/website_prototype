<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        foreach (DB::table('site_settings')->whereIn('key', ['home.background_url','mall.background_url','leasing.background_url','events.background_url','services.background_url','about.background_url','inquire.background_url','seo.og_image'])->get() as $setting) {
            DB::table('site_settings')->where('id',$setting->id)->update(['value'=>preg_replace('/\.(png|jpe?g)$/i','.webp',$setting->value),'updated_at'=>now()]);
        }
        foreach (['leasing_spaces','events','promotions','services'] as $table) foreach (DB::table($table)->whereNotNull('cover_image_url')->get() as $row) {
            if (str_contains($row->cover_image_url, 'mall_images/') || str_contains($row->cover_image_url, 'general_images/')) DB::table($table)->where('id',$row->id)->update(['cover_image_url'=>preg_replace('/\.(png|jpe?g)$/i','.webp',$row->cover_image_url)]);
        }
        foreach (DB::table('highlights')->get() as $row) {
            $images=json_decode($row->images,true) ?: [];
            $images=array_map(fn($path)=>preg_replace('/\.(png|jpe?g)$/i','.webp',$path),$images);
            DB::table('highlights')->where('id',$row->id)->update(['images'=>json_encode($images),'updated_at'=>now()]);
        }
    }
    public function down(): void { /* Keep optimized references. */ }
};
