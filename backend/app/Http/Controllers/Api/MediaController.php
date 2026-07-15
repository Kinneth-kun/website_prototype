<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('media')->orderByDesc('id');
        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('alt_text', 'like', $term));
        }
        return $query->paginate(min(max($request->integer('per_page', 24), 1), 100));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,avif,gif', 'max:8192'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);
        $file = $data['file'];
        [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];
        abort_if(! $width || ! $height, 422, 'The uploaded file is not a valid image.');
        abort_if($width > 10000 || $height > 10000 || ($width * $height) > 40000000, 422, 'The image dimensions are too large.');
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
        $path = $file->storeAs('media/'.now()->format('Y/m'), $base.'-'.Str::lower(Str::random(8)).'.'.$file->extension(), 'public');
        $id = DB::table('media')->insertGetId([
            'uploaded_by' => $request->user()->id,
            'name' => $file->getClientOriginalName(),
            'disk' => 'public', 'path' => $path,
            'url' => url(Storage::disk('public')->url($path)),
            'mime_type' => $file->getMimeType(), 'size_bytes' => $file->getSize(),
            'width' => $width, 'height' => $height, 'alt_text' => $data['alt_text'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(DB::table('media')->find($id), 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate(['alt_text' => ['nullable', 'string', 'max:255']]);
        abort_unless(DB::table('media')->where('id', $id)->exists(), 404);
        DB::table('media')->where('id', $id)->update(['alt_text' => $data['alt_text'] ?? null, 'updated_at' => now()]);
        return DB::table('media')->find($id);
    }

    public function destroy(Request $request, int $id)
    {
        abort_unless($request->user()->role === 'super_admin', 403, 'Only a super administrator can delete media.');
        $media = DB::table('media')->find($id); abort_unless($media, 404);
        Storage::disk($media->disk)->delete($media->path);
        DB::table('media')->where('id', $id)->delete();
        DB::table('audit_logs')->insert(['user_id'=>$request->user()->id,'action'=>'deleted','entity_type'=>'media','entity_id'=>$id,'old_values'=>json_encode((array)$media),'new_values'=>null,'ip_address'=>$request->ip(),'created_at'=>now()]);
        return response()->noContent();
    }
}
