<?php
namespace App\Http\Controllers;

use App\Models\Tentangkami;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TentangkamiController extends Controller
{
    // ini array
    // public function index()
    // {
    //     $data = Tentangkami::all();
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Data Tentang Kami berhasil diambil',
    //         'data' => $data,
    //     ]);
    // }

    public function index()
    {
        $data = Tentangkami::first(); // hanya 1 data

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function show($id)
    {
        $data = Tentangkami::find($id);
        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'telepon' => 'required',
            'instagram' => 'required',
            'alamat' => 'required',
            'deskripsi' => 'required',
            'visi' => 'nullable|string',
            'misi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $data = Tentangkami::create($request->all());

        return response()->json(['status' => true, 'message' => 'Data berhasil ditambahkan', 'data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $data = Tentangkami::find($id);
        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $data->update($request->all());

        return response()->json(['status' => true, 'message' => 'Data berhasil diperbarui', 'data' => $data]);
    }

    public function destroy($id)
    {
        $data = Tentangkami::find($id);
        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
        $data->delete();

        return response()->json(['status' => true, 'message' => 'Data berhasil dihapus']);
    }
}
