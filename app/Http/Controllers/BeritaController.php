<?php

namespace App\Http\Controllers;

use App\Models\berita;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BeritaController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $sort   = $request->query('sort', 'desc'); 

        if (!in_array($sort, ['asc', 'desc'])) {
            $sort = 'desc';
        }

        $query = berita::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $query->orderBy('publish_date', $sort);

        $berita = $query->get();

        return response()->json([
            'success' => true,
            'jumlah' => $berita->count(),
            'sort_by' => 'publish_date',
            'sort_order' => $sort,
            'filtered_status' => $status,
            'data' => $berita
        ]);
    }

    public function show($id)
    {
        $berita = berita::find($id);

        if (!$berita) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail berita ditemukan',
            'data' => $berita
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'        => 'required|string',
            'isi'          => 'required|string',
            'deskripsi'    => 'nullable|string',
            'media'        => 'required|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:102400',
            'status'       => 'required|integer',
            'kategori'     => 'required|string',
            'publish_date' => 'nullable|date'
        ]);

        $slug = Str::slug($validated['judul'], '-');
        $count = berita::where('slug', 'LIKE', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        if ($request->hasFile('media')) {

            $folder = 'berita/' . date('Y') . '/' . date('m') . '/' . date('d');
            $path = $request->file('media')->store($folder, 's3');
        
            Storage::disk('s3')->setVisibility($path, 'public');
        
            $validated['media'] = Storage::disk('s3')->url($path);
            $validated['publish_date'] = now();
            
        } else {
            return response()->json([
                'success' => false,
                'message' => 'File media wajib dikirim'
            ], 400);
        }
        
        if ($request->publish_date) {
            $validated['publish_date'] = $request->publish_date;
        }

        $validated['slug'] = $slug;

        $berita = berita::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil ditambahkan',
            'data' => $berita
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $berita = berita::find($id);

        if (!$berita) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'judul'     => 'required|string|max:255',
            'isi'       => 'required|string',
            'deskripsi' => 'nullable|string',
            'media'     => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:102400',
            'status'    => 'required|integer',
            'kategori'  => 'required|string',
            'publish_date' => 'nullable|date'
        ]);

        if ($request->judul !== $berita->judul) {
            $slug = Str::slug($request->judul, '-');
            $count = berita::where('slug', 'LIKE', "{$slug}%")->count();

            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }
            $validated['slug'] = $slug;
        } else {
            $validated['slug'] = $berita->slug;
        }

        if ($request->hasFile('media') && $request->file('media')->isValid()) {

            if ($berita->media) {
                $oldPath = str_replace(env('AWS_URL') . '/', '', $berita->media);
                Storage::disk('s3')->delete($oldPath);
            }

            $folder = 'berita/' . date('Y') . '/' . date('m') . '/' . date('d');
            $path = $request->file('media')->store($folder, 's3');

            $baseUrl = rtrim(env('AWS_URL'), '/');
            $validated['media'] = $baseUrl . '/' . ltrim($path, '/');

            $validated['publish_date'] = now();
        } else {
            $validated['media'] = $berita->media;
        }

        if ($request->publish_date) {
            $validated['publish_date'] = $request->publish_date;
        }

        $berita->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil diperbarui',
            'data' => $berita
        ]);
    }

    public function destroy($id)
    {
        $berita = berita::find($id);

        if (!$berita) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan'
            ], 404);
        }

        if ($berita->media) {
            $oldPath = str_replace(env('AWS_URL') . '/', '', $berita->media);
            Storage::disk('s3')->delete($oldPath);
        }

        $berita->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil dihapus'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $berita = berita::find($id);
        if (!$berita) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|integer'
        ]);

        $berita->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status berita berhasil diperbarui',
            'data' => $berita
        ], 200);
    }
}
