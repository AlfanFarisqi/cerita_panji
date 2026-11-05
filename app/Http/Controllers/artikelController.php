<?php

namespace App\Http\Controllers;

use App\Models\artikelModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class artikelController extends Controller
{
    private function clearArtikelCache($id = null)
    {
        Cache::forget('artikel_all');
        if ($id) {
            Cache::forget('artikel_' . $id);
        }
    }

    public function getArtikel(Request $request)
    {
        try {
            if ($request->id) {
                $id = $request->id;
                $artikel = Cache::remember('artikel_' . $id, 300, function () use ($id) {
                    return artikelModel::with('soal')->find($id);
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

            $artikel = Cache::remember('artikel_all', 300, function () {
                return artikelModel::with('soal')->get();
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

    public function createArtikel(Request $request)
    {
        try {
            $gambar = $request->file('gambar');
            $destinationPath = 'artikel/';
            $profileImage = date('YmdHis') . "." . $gambar->getClientOriginalExtension();
            $gambar->move($destinationPath, $profileImage);

            $artikel = artikelModel::create([
                'artikel_link' => $request->artikel_link,
                'judul' => $request->judul,
                'image' => $profileImage,
                'type' => $request->type,
                'deskripsi' => $request->deskripsi,
            ]);

            $this->clearArtikelCache();

            return response()->json([
                'success' => true,
                'data' => $artikel
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function updateArtikel(Request $request, $id)
    {
        try {
            $data = artikelModel::find($id);
            if (!$data) {
                return response()->json([
                    'message' => 'Artikel not found',
                ], 404);
            }
            $data->artikel_link = $request->artikel_link;
            $data->judul = $request->judul;
            $data->deskripsi = $request->deskripsi;
            $data->type = $request->type;
            if ($request->file("gambar")) {
                if (File::exists("artikel/" . $data['image'])) {
                    File::delete("artikel/" . $data['image']);
                }

                $gambar = $request->file('gambar');
                $destinationPath = 'artikel/';
                $filename = date('YmdHis') . "." . $gambar->getClientOriginalExtension();
                $gambar->move($destinationPath, $filename);

                $data->image = $filename;
            }
            $data->save();
            $this->clearArtikelCache($id);

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function deleteArtikel($id)
    {
        try {
            $data = artikelModel::find($id);

            if (!$data) {
                return response()->json([
                    'message' => 'Artikel tidak ditemukan.',
                ], 404);
            }

            // Cek relasi soal
            if ($data->soal()->count() > 0) {
                return response()->json([
                    'message' => 'Artikel masih memiliki soal terkait. Hapus soal terlebih dahulu.',
                ], 400);
            }

            // Cek relasi nilai
            if ($data->nilai()->count() > 0) {
                return response()->json([
                    'message' => 'Artikel masih memiliki nilai terkait. Hapus nilai terlebih dahulu.',
                ], 400);
            }

            // Hapus file jika ada
            if ($data->image && File::exists("artikel/" . $data->image)) {
                File::delete("artikel/" . $data->image);
            }

            // Hapus artikel
            $data->delete();
            $this->clearArtikelCache($id);

            return response()->json([
                'success' => true,
                'message' => 'Artikel berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Gagal menghapus artikel: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
            ], 500);
        }
    }
}

