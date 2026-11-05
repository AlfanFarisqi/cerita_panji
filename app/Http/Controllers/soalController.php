<?php

namespace App\Http\Controllers;

use App\Models\artikelModel;
use App\Models\soalModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class soalController extends Controller
{
    private function clearSoalCache($idSoal = null, $idArtikel = null)
    {
        Cache::forget('soal_all');
        if ($idSoal) Cache::forget('soal_id_' . $idSoal);
        if ($idArtikel) Cache::forget('soal_artikel_' . $idArtikel);
    }

    public function getSoal(Request $request)
    {
        try {
            if($request->id_artikel){
                $idArtikel = $request->id_artikel;
                $soal = Cache::remember('soal_artikel_' . $idArtikel, 300, function () use ($idArtikel) {
                    return artikelModel::with('soal')
                        ->where('id', $idArtikel)
                        ->get();
                });

                if ($soal->isEmpty()) {
                    return response()->json([
                        'message' => 'Soal not found for the given article ID',
                    ], 404);
                }
                return response()->json([
                    'success' => true,
                    'data' => $soal
                ], 200);
            }
            if($request->id){
                $idSoal = $request->id;
                $soal = Cache::remember('soal_id_' . $idSoal, 300, function () use ($idSoal) {
                    return soalModel::with('artikel')->find($idSoal);
                });

                if (!$soal) {
                    return response()->json([
                        'message' => 'Soal not found',
                    ], 404);
                }
                return response()->json([
                    'success' => true,
                    'data' => $soal
                ], 200);
            }

            $soal = Cache::remember('soal_all', 300, function () {
                return soalModel::with('artikel')->get();
            });

            return response()->json([
                'success' => true,
                'data' => $soal
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createSoal(Request $request)
    {
        try {
            $soal = soalModel::create($request->all());
            $this->clearSoalCache($soal->id, $request->id_artikel);

            return response()->json([
                'success' => true,
                'data' => $soal
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateSoal(Request $request, $id)
    {
        try {
            $soal = soalModel::find($id);
            if (!$soal) {
                return response()->json([
                    'message' => 'Soal not found',
                ], 404);
            }
            $soal->update($request->all());
            $this->clearSoalCache($id, $soal->id_artikel);

            return response()->json([
                'success' => true,
                'data' => $soal
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteSoal($id)
    {
        try {
            $soal = soalModel::find($id);
            if (!$soal) {
                return response()->json([
                    'message' => 'Soal not found',
                ], 404);
            }
            $idArtikel = $soal->id_artikel;
            $soal->delete();
            $this->clearSoalCache($id, $idArtikel);

            return response()->json([
                'success' => true,
                'message' => 'Soal deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}

