<?php

namespace App\Http\Controllers;

use App\Models\artikelModel;
use App\Models\nilaiModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class nilaiController extends Controller
{
    private function clearNilaiCache($idNilai = null, $idArtikel = null, $idUser = null)
    {
        Cache::forget('nilai_all');
        if ($idNilai) Cache::forget('nilai_id_' . $idNilai);
        if ($idArtikel) Cache::forget('nilai_artikel_' . $idArtikel);
        if ($idArtikel && $idUser) Cache::forget("nilai_art_{$idArtikel}_usr_{$idUser}");
    }

    public function getNilai(Request $request)
    {
        try {
            // Ambil berdasarkan ID Nilai
            if ($request->id_nilai) {
                $idNilai = $request->id_nilai;
                $nilai = Cache::remember('nilai_id_' . $idNilai, 180, function () use ($idNilai) {
                    return nilaiModel::with(['artikel', 'user'])->find($idNilai);
                });

                if (!$nilai) {
                    return response()->json([
                        'message' => 'Nilai not found',
                    ], 404);
                }
                return response()->json([
                    'success' => true,
                    'data' => $nilai
                ], 200);
            }

            // Ambil berdasarkan kombinasi id_artikel dan id_user
            if ($request->id_artikel && $request->id_user) {
                $idArtikel = $request->id_artikel;
                $idUser = $request->id_user;
                $nilai = Cache::remember("nilai_art_{$idArtikel}_usr_{$idUser}", 180, function () use ($idArtikel, $idUser) {
                    return nilaiModel::with(['artikel', 'user'])
                        ->where('id_artikel', $idArtikel)
                        ->where('id_user', $idUser)
                        ->first();
                });

                if (!$nilai) {
                    return response()->json([
                        'message' => 'Nilai not found',
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'data' => $nilai
                ], 200);
            }

            // Ambil berdasarkan ID Artikel
            if ($request->id_artikel) {
                $idArtikel = $request->id_artikel;
                $artikel = Cache::remember('nilai_artikel_' . $idArtikel, 180, function () use ($idArtikel) {
                    return artikelModel::with('nilai.user')
                        ->where('id', $idArtikel)
                        ->first();
                });

                if (!$artikel) {
                    return response()->json([
                        'message' => 'Artikel not found',
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'data' => $artikel
                ], 200);
            }

            // Ambil semua nilai dengan artikel & user
            $artikel = Cache::remember('nilai_all', 180, function () {
                return artikelModel::with('nilai.user')->get();
            });

            return response()->json([
                'success' => true,
                'data' => $artikel
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createNilai(Request $request)
    {
        $validated = $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'id_user' => 'required|exists:users,id',
            'id_artikel' => 'required|exists:artikel,id',
        ]);

        try {
            $nilai = nilaiModel::create($validated);
            $this->clearNilaiCache($nilai->id, $nilai->id_artikel, $nilai->id_user);

            return response()->json([
                'success' => true,
                'data' => $nilai,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateNilai(Request $request, $id)
    {
        try {
            $nilai = nilaiModel::find($id);
            if (!$nilai) {
                return response()->json([
                    'message' => 'Nilai not found',
                ], 404);
            }
            $nilai->update($request->all());
            $this->clearNilaiCache($nilai->id, $nilai->id_artikel, $nilai->id_user);

            return response()->json([
                'success' => true,
                'data' => $nilai
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function deleteNilai($id)
    {
        try {
            $nilai = nilaiModel::find($id);
            if (!$nilai) {
                return response()->json([
                    'message' => 'Nilai not found',
                ], 404);
            }
            $idArtikel = $nilai->id_artikel;
            $idUser = $nilai->id_user;
            $nilai->delete();
            $this->clearNilaiCache($id, $idArtikel, $idUser);

            return response()->json([
                'success' => true,
                'message' => 'Nilai deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}

