<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NewInquiryReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class PublicController extends Controller
{
    private array $tables = ['tenants','categories','floors','leasing_spaces','events','promotions','services','mall_hours','special_hours','highlights'];

    public function index(Request $request, string $resource)
    {
        abort_unless(in_array($resource, $this->tables, true), 404);
        $version=Cache::get('cms_content_version',1); $cacheKey='public-content:'.$version.':'.$resource.':'.sha1($request->getQueryString()??'');
        $content=Cache::remember($cacheKey,60,function() use($request,$resource){
        $query = DB::table($resource);
        if ($resource === 'tenants') $query->select('trade_name', 'industry_name', 'company_address', 'approved_products', 'picture_of_branches', 'picture_of_menu');
        if ($resource === 'categories') $query->where('is_active', true);
        if (in_array($resource, ['tenants','leasing_spaces','events','promotions','services','highlights'], true)) {
            $query->whereNull('deleted_at');
            if (\Illuminate\Support\Facades\Schema::hasColumn($resource, 'published_at')) $query->where(fn($q)=>$q->whereNull('published_at')->orWhere('published_at','<=',now()));
            if (in_array($resource, ['tenants','services','highlights'], true)) $query->where('status', 'active');
            elseif ($resource === 'leasing_spaces') $query->where('availability_status', 'available');
            else $query->whereIn('status', ['published','active']);
        }
        if ($request->boolean('featured')) $query->where('is_featured', true);
        if ($request->filled('category_id') && $resource === 'tenants') $query->where('category_id', $request->integer('category_id'));
        if ($request->filled('floor_id') && in_array($resource, ['tenants','leasing_spaces','services'], true)) $query->where('floor_id', $request->integer('floor_id'));
        if ($request->filled('search') && in_array($resource, ['tenants','leasing_spaces','events','promotions','services'], true)) {
            $column = $resource === 'tenants' || $resource === 'services' ? 'name' : 'title';
            $query->where($column, 'like', '%'.$request->string('search').'%');
        }
        $alphabeticalColumns = ['tenants'=>'trade_name', 'categories'=>'name', 'floors'=>'name'];
        if (isset($alphabeticalColumns[$resource])) $query->orderBy($alphabeticalColumns[$resource]);
        elseif (\Illuminate\Support\Facades\Schema::hasColumn($resource, 'display_order')) $query->orderBy('display_order');
        return $query->orderBy($resource === 'mall_hours' ? 'day_of_week' : 'id')->get();
        });
        return response($content)
            ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
    }

    public function settings() {
        $version=Cache::get('cms_content_version',1);
        return response(Cache::remember('public-settings:'.$version,60,fn()=>DB::table('site_settings')->where('is_public', true)->pluck('value', 'key')))
            ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
    }

    public function inquiry(Request $request)
    {
        $data = $request->validate([
            'inquiry_type' => ['required','string','max:80'], 'leasing_space_id' => ['nullable','exists:leasing_spaces,id'],
            'name' => ['required','string','max:120'], 'company_name' => ['nullable','string','max:160'],
            'email' => ['required','email','max:190'], 'phone' => ['nullable','string','max:40'],
            'preferred_date' => ['nullable','date'], 'subject' => ['nullable','string','max:190'], 'message' => ['required','string','max:5000'],
        ]);
        $data['reference_number'] = 'ICM-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        $data['created_at'] = $data['updated_at'] = now();

        DB::transaction(function () use (&$data) {
            $data['id'] = DB::table('inquiries')->insertGetId($data);

            $administrators = User::query()
                ->where('status', 'active')
                ->whereIn('role', ['super_admin', 'editor'])
                ->get();

            Notification::send($administrators, new NewInquiryReceived($data));
        });

        return response()->json(['message' => 'Inquiry received.', 'reference_number' => $data['reference_number']], 201);
    }
}
