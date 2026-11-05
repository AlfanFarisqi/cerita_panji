<?php

namespace App\Http\Controllers;

use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use App\Models\soalPilganModel;
use Illuminate\Support\Facades\Cache;

class pilganController extends Controller
{
    private function clearPilganCache()
    {
        Cache::forget('soal_pilgan_all');
    }

    public function createSoalPilgan(Request $request){
        try{
            soalPilganModel::create([
                "id" => Uuid::uuid4(),
                "soal" => $request->soal,
                "idDongeng" => $request->idDongeng,
                "opsi_1" => $request->opsi_1,
                "opsi_2" => $request->opsi_2,
                "opsi_3" => $request->opsi_3,
                "opsi_4" => $request->opsi_4,
                "jawaban" => $request->jawaban
            ]);

            $this->clearPilganCache();

            return response()->json(["message" => "Soal Pilihan Ganda Created!"], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function getSoalPilgan(){
        try{
            $data = Cache::remember('soal_pilgan_all', 300, function () {
                return soalPilganModel::with("dongeng")->get();
            });
            return response()->json($data, 200);

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function updateSoalPilgan(Request $request, $id){
        try{
            $data = soalPilganModel::find($id);
            if(!$data){
                return response()->json(["message" => "Soal Pilihan Ganda not found"], 404);
            }
            $data->update($request->all());
            $this->clearPilganCache();

            return response()->json(["message" => "Soal Pilihan Ganda Updated!"], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function deleteSoalPilgan($id){
        try{
            $data = soalPilganModel::find($id);
            if ($data) {
                $data->delete();
                $this->clearPilganCache();
            }

            return response()->json(["message" => "Soal Pilihan Ganda Deleted!"], 200);
            
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }
}

