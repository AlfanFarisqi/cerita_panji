<?php

namespace App\Http\Controllers;

use App\Models\soalUraianSingkatModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;

class uraianSingkatController extends Controller
{
    private function clearUraianSingkatCache()
    {
        Cache::forget('soal_uraian_singkat_all');
    }

    public function createSoalUraianSingkat(Request $request){
        try{
            soalUraianSingkatModel::create([
                "id" => Uuid::uuid4(),
                "soal" => $request->soal,
                "idDongeng" => $request->idDongeng,
                "jawaban" => $request->jawaban
            ]);

            $this->clearUraianSingkatCache();

            return response()->json(["message" => "Soal Uraian Singkat Created"]);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function getSoalUraianSingkat()
    {
        try{
            $data = Cache::remember('soal_uraian_singkat_all', 300, function () {
                return soalUraianSingkatModel::with('dongeng')->get();
            });
            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function updateSoalUraianSingkat(Request $request, $id){
        try{
            $data = soalUraianSingkatModel::find($id);
            if(!$data){
                return response()->json(["message" => "Soal Uraian Singkat not found"]);
            }

            $data->update($request->all());
            $this->clearUraianSingkatCache();

            return response()->json(["message" => "soal uraian singkat updated"]);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function deleteSoalUraianSingkat($id)
    {
        try{
            $data = soalUraianSingkatModel::find($id);
            if ($data) {
                $data->delete();
                $this->clearUraianSingkatCache();
            }
            return response()->json(["message" => "Soal Uraian Singkat Deleted"]);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }
}

