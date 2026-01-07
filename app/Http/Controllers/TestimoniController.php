<?php
namespace App\Http\Controllers;

use App\Models\testimoni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestimoniController extends Controller
{
    // public function index(Request $request)
    // {

    //     $sort = $request->query('sort', 'desc');
    //     if (!in_array($sort, ['asc', 'desc'])) {
    //         $sort = 'desc';
    //     }

    //     $testimoni = testimoni::orderBy('created_at', 'desc')->get();
    //     $total = $testimoni->count();
    //     $total_active = testimoni::where('status_active', 1)->count();
    //     if ($total === 0) {
    //         return response()->json([
    //             'status' => true,
    //             'data' => [
    //                 'testimoni' => [],
    //                 'total_testimoni' => 0,
    //                 'five_star' => '0',
    //                 'four_star' => '0',
    //                 'three_star' => '0',
    //                 'two_star' => '0',
    //                 'one_star' => '0'
    //             ]
    //         ]);
    //     }

    //     $count5 = testimoni::where('rating', 5)->count();
    //     $count4 = testimoni::where('rating', 4)->count();
    //     $count3 = testimoni::where('rating', 3)->count();
    //     $count2 = testimoni::where('rating', 2)->count();
    //     $count1 = testimoni::where('rating', 1)->count();

    //     $percent = function ($value) use ($total) {
    //         return round(($value / $total) * 100, 2) ;
    //     };

    //     return response()->json([
    //         'status' => true,
    //         'data' => [
    //             'testimoni'         => $testimoni,
    //             'total_testimoni'   => $total,
    //             'total_active'      => $total_active,
    //             'five_star'         => $percent($count5),
    //             'four_star'         => $percent($count4),
    //             'three_star'        => $percent($count3),
    //             'two_star'          => $percent($count2),
    //             'one_star'          => $percent($count1),
    //         ]
    //     ]);
    // }

    public function index(Request $request)
    {
        $sort = $request->query('sort', 'desc');
        if (!in_array($sort, ['asc', 'desc'])) {
            $sort = 'desc';
        }

        // HANYA TESTIMONI AKTIF
        $testimoni = Testimoni::where('status_active', 1)->orderBy('created_at', $sort)->get();

        $total = $testimoni->count();

        if ($total === 0) {
            return response()->json([
                'status' => true,
                'data' => [
                    'testimoni' => [],
                    'total_testimoni' => 0,
                    'five_star' => 0,
                    'four_star' => 0,
                    'three_star' => 0,
                    'two_star' => 0,
                    'one_star' => 0,
                ],
            ]);
        }

        $count = fn($star) => $testimoni->where('rating', $star)->count();
        $percent = fn($value) => round(($value / $total) * 100, 2);

        return response()->json([
            'status' => true,
            'data' => [
                'testimoni' => $testimoni,
                'total_testimoni' => $total,
                'five_star' => $percent($count(5)),
                'four_star' => $percent($count(4)),
                'three_star' => $percent($count(3)),
                'two_star' => $percent($count(2)),
                'one_star' => $percent($count(1)),
            ],
        ]);
    }

    public function adminIndex()
    {
        $testimoni = Testimoni::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => [
                'testimoni' => $testimoni,
                'total' => $testimoni->count(),
                'total_active' => $testimoni->where('status_active', 1)->count(),
            ],
        ]);
    }

    public function show($id)
    {
        $data = testimoni::find($id);
        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'pesan' => 'required',
            'rating' => 'nullable|numeric|min:1|max:5',
            'status_active' => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $data = testimoni::create($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'testimoni' => $data->count(),
            'data' => $data,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = testimoni::find($id);
        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $data->update($request->all());

        return response()->json(['status' => true, 'message' => 'Data berhasil diperbarui', 'data' => $data]);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $data = Testimoni::find($id);

        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $request->validate([
            'status_active' => 'required|in:0,1',
        ]);

        $data->update([
            'status_active' => $request->status_active,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Status berhasil diperbarui',
            'data' => $data,
        ]);
    }

    public function destroy($id)
    {
        $data = testimoni::find($id);
        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $data->delete();

        return response()->json(['status' => true, 'message' => 'Data berhasil dihapus']);
    }
}
