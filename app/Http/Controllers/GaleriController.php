<?php

namespace App\Http\Controllers;

use App\Models\galeri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaleriController extends Controller
{
    public function index()
    {
        $galeri = galeri::all();

        return response()->json([
            'status' => 'success',
            'jumlah' => $galeri->count(),
            'data' => $galeri
        ]);
    }

    public function show($id)
    {
        $galeri = galeri::find($id);
        if (!$galeri) {
            return response()->json(['message' => 'Galeri not found'], 404);
        }
        return response()->json($galeri);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'media' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:102400',  
            'tipe_media' => 'required|in:gambar,vidio', 
            'tanggal' => 'nullable|date', 
        ]);

        if ($request->hasFile('media')) {

            $folder = 'galeri/' . date('Y') . '/' . date('m') . '/' . date('d');
            $path = $request->file('media')->store($folder, 'public');
            $validated['media'] = asset('storage/' . $path);
    
        } else {
            return response()->json([
                'success' => false,
                'message' => 'File media wajib dikirim'
            ], 400);
        }
        $galeri = galeri::create([
            'judul' => $request['judul'],
            'deskripsi' => $request['deskripsi'],
            'media' => $path,
            'tipe_media' => $request['tipe_media'],
            'tanggal' => $request['tanggal'] ?? now(), 
        ]);

        return response()->json([
            'message' => 'Galeri berhasil disimpan',
            'data' => $galeri
        ], 201); 
    }

    public function update(Request $request, $id)
    {
        $galeri = galeri::find($id);
        if (!$galeri) {
            return response()->json(['message' => 'Galeri not found'], 404);
        }

        $validated = $request->validate([
            'judul' => 'string|max:255',
            'deskripsi' => 'nullable|string',
            'media' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:102400', 
            'tipe_media '=>'string',
            'tanggal' => 'nullable|date', 
        ]);

        if ($request->hasFile('media')) {

            if ($galeri->media) {
                $oldPath = str_replace('/storage/', '', $galeri->media);
                Storage::disk('public')->delete($oldPath);
            }
    
            $folder = 'galeri/' . date('Y') . '/' . date('m') . '/' . date('d');
            $path = $request->file('media')->store($folder, 'public');
    
            $validated['media'] = url('storage/' . $path);
    
    
        } else {
            $validated['media'] = $galeri->media;
        }
    

        $galeri->update($validated);

        return response()->json([
            'message' => 'Galeri berhasil diperbarui',
            'data' => $galeri
        ]);
    }

    public function destroy($id)
    {
        $galeri = galeri::find($id);
        if (!$galeri) {
            return response()->json(['message' => 'Galeri not found'], 404);
        }
        if ($galeri->media) {
            $oldPath = str_replace(url('storage') . '/', '', $galeri->media);
            Storage::disk('public')->delete($oldPath);
        }
        $galeri->delete();

        return response()->json(['message' => 'Galeri berhasil dihapus']);
    }
}
