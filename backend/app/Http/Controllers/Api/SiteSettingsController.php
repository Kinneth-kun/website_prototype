<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SiteSettingsController extends Controller
{
    public function index()
    {
        return DB::table('site_settings')
            ->orderBy('group')
            ->orderBy('key')
            ->get(['id', 'group', 'key', 'value', 'value_type', 'is_public']);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => ['required', 'array', 'max:250'],
            'settings.*' => ['nullable', 'string', 'max:10000'],
        ]);
        $existing = DB::table('site_settings')->whereIn('key', array_keys($data['settings']))->get()->keyBy('key');
        $unknown = array_diff(array_keys($data['settings']), $existing->keys()->all());
        if ($unknown) {
            throw ValidationException::withMessages(['settings' => 'One or more settings are not recognized.']);
        }

        foreach ($data['settings'] as $key => $value) {
            if (($existing[$key]->value_type ?? 'string') === 'json' && filled($value)) {
                Validator::make(['value' => $value], ['value' => ['json']])->validate();
            }
        }

        DB::transaction(function () use ($request, $data, $existing) {
            foreach ($data['settings'] as $key => $value) {
                DB::table('site_settings')->where('key', $key)->update(['value' => $value, 'updated_at' => now()]);
            }
            DB::table('audit_logs')->insert([
                'user_id' => $request->user()->id,
                'action' => 'updated',
                'entity_type' => 'site_settings',
                'entity_id' => null,
                'old_values' => json_encode($existing->mapWithKeys(fn ($row) => [$row->key => $row->value])),
                'new_values' => json_encode($data['settings']),
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
            Cache::forever('cms_content_version', (int) Cache::get('cms_content_version', 1) + 1);
        });

        return response()->json([
            'message' => 'Website settings saved successfully.',
            'settings' => DB::table('site_settings')->where('is_public', true)->pluck('value', 'key'),
        ]);
    }
}
