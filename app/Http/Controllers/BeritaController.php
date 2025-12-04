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

        $berita->map(function ($item) {
            $item->media_url = $item->media ? url('storage/' . $item->media) : null;
            return $item;
        });

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

        $berita->media_url = $berita->media ? url('storage/' . $berita->media) : null;

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

        $folder = 'berita/' . date('Y') . '/' . date('m') . '/' . date('d');
        $path = $request->file('media')->store($folder, 'public');

        $validated['media'] = $path;
        $validated['slug'] = $slug;
        $validated['publish_date'] = $request->publish_date ?? now();

        $berita = berita::create($validated);

        $berita->media_url = url('storage/' . $berita->media);

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

        if ($request->hasFile('media')) {
            if ($berita->media) {
                Storage::disk('public')->delete($berita->media);
            }

            $folder = 'berita/' . date('Y') . '/' . date('m') . '/' . date('d');
            $path = $request->file('media')->store($folder, 'public');

            $validated['media'] = $path;
            $validated['publish_date'] = now();
        }

        if ($request->publish_date) {
            $validated['publish_date'] = $request->publish_date;
        }

        $berita->update($validated);

        $berita->media_url = url('storage/' . $berita->media);

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
            Storage::disk('public')->delete($berita->media);
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
