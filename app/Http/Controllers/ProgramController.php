<?php

namespace App\Http\Controllers;

use App\Models\program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProgramController extends Controller
{
    public function index()
    {
        $data = program::all();

        return response()->json([
            'status' => true,
            'message' => 'Data program berhasil diambil',
            'jumlah' => $data->count(),
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $data = program::find($id);

        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'        => 'required|string|max:255',
            'deskripsi'   => 'required',
            'tanggal'     => 'nullable|date',
            'tipe_media'  => 'required|in:gambar,vidio',
            'media'       => 'required|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:102400',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        if ($request->hasFile('media')) {

            $folder = 'program/' . date('Y') . '/' . date('m') . '/' . date('d');
            $path = $request->file('media')->store($folder, 'public');
            $validated['media'] = asset('storage/' . $path);
           
    
        } else {
            return response()->json([
                'success' => false,
                'message' => 'File media wajib dikirim'
            ], 400);
        }

        $data = program::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $data,
            'media' => $path
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = program::find($id);

        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama'        => 'nullable|string|max:255',
            'deskripsi'   => 'nullable',
            'tanggal'     => 'nullable|date',
            'tipe_media'  => 'nullable|in:gambar,vidio',
            'media'       => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:102400',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        if ($request->hasFile('media')) {

            if ($data->media) {
                $oldPath = str_replace('/storage/', '', $data->media);
                Storage::disk('public')->delete($oldPath);
            }
    
            $folder = 'program/' . date('Y') . '/' . date('m') . '/' . date('d');
            $path = $request->file('media')->store($folder, 'public');
    
            $validated['media'] = url('storage/' . $path);
    
        } else {
            $validated['media'] = $data->media;
        }

        $data->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $data = program::find($id);

        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        if ($data->media) {
            $oldPath = str_replace(url('storage') . '/', '', $data->media);
            Storage::disk('public')->delete($oldPath);
        }

        $data->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }
}
