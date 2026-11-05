<?php

namespace App\Http\Controllers;

use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use App\Models\soalUraianPanjangModel;
use Illuminate\Support\Facades\Cache;

class UraianPanjangController extends Controller
{
    private function clearUraianPanjangCache()
    {
        Cache::forget('soal_uraian_panjang_all');
    }

    public function createSoalUraianPanjang(Request $request){
        try{
            soalUraianPanjangModel::create([
                "id" => Uuid::uuid4(),
                "soal" => $request->soal,
                "idDongeng" => $request->idDongeng,
                "jawaban" => $request->jawaban
            ]);

            $this->clearUraianPanjangCache();

            return response()->json(["message" => "soal uraian panjang created"], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function getSoalUraianPanjang(){
        try{
            $data = Cache::remember('soal_uraian_panjang_all', 300, function () {
                return soalUraianPanjangModel::with("dongeng")->get();
            });
            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }
    public function updateSoalUraianPanjang(Request $request, $id){
        try{
            $data = soalUraianPanjangModel::find($id);
            if(!$data){
                return response()->json(["message" => "Soal Uraian Panjang not found"], 404);
            }

            $data->soal = $request->soal;
            $data->idDongeng = $request->idDongeng;
            $data->jawaban = $request->jawaban;

            $data->save();
            $this->clearUraianPanjangCache();

            return response()->json(["message" => "Soal Uraian Panjang Updated!"], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function deleteSoalUraianPanjang($id){
        try{
            $data = soalUraianPanjangModel::where("id" ,$id)->first();
            if ($data) {
                $data->delete();
                $this->clearUraianPanjangCache();
            }
            return response()->json(["message" => "Soal Uraian Panjang Deleted!"], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }
}

