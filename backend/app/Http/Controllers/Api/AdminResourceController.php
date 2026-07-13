<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class AdminResourceController extends Controller
{
    private array $tables = ['tenants','categories','floors','leasing_spaces','events','promotions','services','highlights','inquiries','mall_hours','special_hours','site_settings'];

    private function table(string $resource): string { abort_unless(in_array($resource, $this->tables, true), 404); return $resource; }

    public function index(Request $request, string $resource)
    {
        $table = $this->table($resource); $query = DB::table($table);
        if (Schema::hasColumn($table, 'deleted_at')) $query->whereNull('deleted_at');
        if ($request->filled('search')) {
            $column = Schema::hasColumn($table, 'name') ? 'name' : (Schema::hasColumn($table, 'title') ? 'title' : null);
            if ($column) $query->where($column, 'like', '%'.$request->string('search').'%');
        }
        if ($table === 'tenants' && $request->filled('category_id')) $query->where('category_id', $request->integer('category_id'));
        return $query->orderByDesc('id')->paginate(min($request->integer('per_page', 25), 100));
    }

    public function store(Request $request, string $resource)
    {
        $table = $this->table($resource); $data = $this->clean($request, $table);
        $this->validateData($table, $data); $now = now();
        if (Schema::hasColumn($table, 'created_at')) $data['created_at'] = $now;
        if (Schema::hasColumn($table, 'updated_at')) $data['updated_at'] = $now;
        $id = DB::table($table)->insertGetId($data); $this->audit($request, 'created', $table, $id, null, $data); $this->invalidatePublicCache();
        return response()->json(DB::table($table)->find($id), 201);
    }

    public function show(string $resource, int $id) { return response()->json(DB::table($this->table($resource))->find($id) ?? abort(404)); }

    public function update(Request $request, string $resource, int $id)
    {
        $table = $this->table($resource); $old = DB::table($table)->find($id); abort_unless($old, 404);
        $data = $this->clean($request, $table); $this->validateData($table, $data, $id, true); if (Schema::hasColumn($table, 'updated_at')) $data['updated_at'] = now();
        DB::table($table)->where('id', $id)->update($data); $this->audit($request, 'updated', $table, $id, (array)$old, $data); $this->invalidatePublicCache();
        return DB::table($table)->find($id);
    }

    public function destroy(Request $request, string $resource, int $id)
    {
        abort_unless($request->user()->role === 'super_admin', 403, 'Only a super administrator can delete records.');
        $table = $this->table($resource); $old = DB::table($table)->find($id); abort_unless($old, 404);
        Schema::hasColumn($table, 'deleted_at') ? DB::table($table)->where('id', $id)->update(['deleted_at' => now()]) : DB::table($table)->where('id', $id)->delete();
        $this->audit($request, 'deleted', $table, $id, (array)$old, null); $this->invalidatePublicCache(); return response()->noContent();
    }

    private function clean(Request $request, string $table): array
    {
        $blocked = ['id','created_at','updated_at','deleted_at','api_token_hash','password'];
        $data = collect($request->all())->only(array_diff(Schema::getColumnListing($table), $blocked))->toArray();
        if (isset($data['name']) && Schema::hasColumn($table, 'slug') && empty($data['slug'])) $data['slug'] = Str::slug($data['name']);
        if (isset($data['title']) && Schema::hasColumn($table, 'slug') && empty($data['slug'])) $data['slug'] = Str::slug($data['title']);
        foreach ($data as $key => $value) if (is_array($value)) $data[$key] = json_encode($value);
        return $data;
    }

    private function required(string $table, array $data): void
    {
        $required = ['tenants'=>['name'],'categories'=>['name'],'floors'=>['name'],'leasing_spaces'=>['title','space_type','description'],'events'=>['title','start_datetime'],'promotions'=>['title','start_date','end_date'],'services'=>['name'],'highlights'=>['title','images'],'special_hours'=>['date','title'],'site_settings'=>['group','key']][$table] ?? [];
        foreach ($required as $field) abort_if(blank($data[$field] ?? null), 422, ucfirst(str_replace('_',' ',$field)).' is required.');
    }

    private function validateData(string $table, array $data, ?int $id = null, bool $partial = false): void
    {
        $rules = [
            'tenants'=>['name'=>['required','string','max:190'],'category_id'=>['required','integer','exists:categories,id'],'slug'=>['nullable','string','max:190',Rule::unique('tenants','slug')->ignore($id)],'email'=>['nullable','email','max:190'],'website_url'=>['nullable','url','max:500'],'status'=>['nullable',Rule::in(['active','inactive','draft'])]],
            'categories'=>['name'=>['required','string','max:120'],'display_order'=>['nullable','integer','min:0']],
            'floors'=>['name'=>['required','string','max:120'],'floor_number'=>['nullable','integer'],'display_order'=>['nullable','integer','min:0']],
            'leasing_spaces'=>['title'=>['required','string','max:190'],'space_type'=>['required','string','max:80'],'description'=>['required','string','max:5000'],'area_sqm'=>['nullable','numeric','min:0'],'cover_image_url'=>['nullable','string','max:1000']],
            'events'=>['title'=>['required','string','max:190'],'start_datetime'=>['required','date'],'end_datetime'=>['nullable','date','after_or_equal:start_datetime'],'status'=>['nullable',Rule::in(['draft','published','archived'])]],
            'promotions'=>['title'=>['required','string','max:190'],'start_date'=>['required','date'],'end_date'=>['required','date','after_or_equal:start_date'],'status'=>['nullable',Rule::in(['draft','active','archived'])]],
            'services'=>['name'=>['required','string','max:190'],'action_url'=>['nullable','string','max:1000'],'status'=>['nullable',Rule::in(['active','inactive','draft'])]],
            'highlights'=>['title'=>['required','string','max:190'],'button_url'=>['nullable','string','max:1000'],'status'=>['nullable',Rule::in(['active','inactive','draft'])]],
            'mall_hours'=>['day_of_week'=>['required','integer','between:0,6'],'opening_time'=>['nullable','date_format:H:i'],'closing_time'=>['nullable','date_format:H:i','after:opening_time']],
            'special_hours'=>['date'=>['required','date'],'title'=>['required','string','max:190']],
            'site_settings'=>['group'=>['required','string','max:80'],'key'=>['required','string','max:190',Rule::unique('site_settings','key')->ignore($id)],'value'=>['nullable','string','max:20000']],
            'inquiries'=>['status'=>['nullable',Rule::in(['new','in_progress','resolved','closed'])],'priority'=>['nullable',Rule::in(['low','normal','high','urgent'])],'internal_notes'=>['nullable','string','max:10000']],
        ][$table] ?? [];
        if ($partial) foreach ($rules as $field => &$fieldRules) if (! array_key_exists($field, $data)) array_unshift($fieldRules, 'sometimes');
        Validator::make($data, $rules)->validate();
    }

    private function audit(Request $request, string $action, string $table, int $id, ?array $old, ?array $new): void
    {
        DB::table('audit_logs')->insert(['user_id'=>$request->user()->id,'action'=>$action,'entity_type'=>$table,'entity_id'=>$id,'old_values'=>$old?json_encode($old):null,'new_values'=>$new?json_encode($new):null,'ip_address'=>$request->ip(),'created_at'=>now()]);
    }

    private function invalidatePublicCache(): void
    {
        Cache::forever('cms_content_version', (int) Cache::get('cms_content_version', 1) + 1);
    }
}
