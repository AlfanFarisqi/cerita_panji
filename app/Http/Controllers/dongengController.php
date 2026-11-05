<?php

namespace App\Http\Controllers;

use App\Models\dongengModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class dongengController extends Controller
{
    private function clearDongengCache($id = null)
    {
        Cache::forget('dongeng_all');
        Cache::forget('dongeng_count');
        Cache::forget('dongeng_sum_views');
        Cache::forget('dongeng_popular');
        if ($id) {
            Cache::forget('dongeng_' . $id);
        }
    }

    public function createDongeng(Request $request)
    { 
        try{
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'pdfURL' => 'required|string',
                'cover' => 'required|image|mimes:jpeg,png,jpg,gif',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $cover = $request->file('cover'); 
            $destinationPath = 'images/';
            $profileImage = date('YmdHis') . "." . $cover->getClientOriginalExtension();
            $cover->move($destinationPath, $profileImage);

            dongengModel::create([
                'title' => $request->title,
                'filename' => $profileImage,
                'pdfURL' => $request->pdfURL,
                'view' => 0,
                'cover' => $request->getScheme() . "://" .  $request->header('Host') . "/" . $destinationPath . $profileImage,
            ]);
            
            $this->clearDongengCache();

            return response()->json(["message" => "berhasil"], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function getDongeng(Request $request){
        try{
            $data = Cache::remember('dongeng_all', 300, function () {
                return dongengModel::all();
            });
            return response()->json($data , 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDongengById(Request $request, $id){
        try{
            $data = Cache::remember('dongeng_' . $id, 300, function () use ($id) {
                return dongengModel::find($id);
            });
            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateDongeng(Request $request, $id){
        $data = dongengModel::find($id);
        if(!$data){
            return response()->json(["message" => "dongeng tidak tersedia!"]);
        }

        try{
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'pdfURL' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()
                ], 422);
            }

            $data->title = $request->title;
            $data->pdfURL = $request->pdfURL;

            if($request->file("cover"))
            {
                if (File::exists("images/" . $data['filename'])) {
                    File::delete("images/" . $data['filename']);
                }

                $cover = $request->file('cover'); 
                $destinationPath = 'images/';
                $filename = date('YmdHis') . "." . $cover->getClientOriginalExtension();
                $cover->move($destinationPath, $filename);

                $data->cover = $request->getScheme() . "://" .  $request->header('Host') . "/" . $destinationPath . $filename;
                $data->filename = $filename;
            }

            $data->save();
            $this->clearDongengCache($id);

            return response()->json(["message" => "dongeng berhasil diperbarui!"]);

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteDongeng(Request $request, $id)
    {
        // 404
        $data = dongengModel::find($id);
        if(!$data){
            return response()->json(["message" => 'dongeng tidak tersedia'], 404);
        }

        try{
            
            if (File::exists($data['cover'])) {
                File::delete($data['cover']);
            }

            $data->delete();
            $this->clearDongengCache($id);

            return response()->json(["messgae" => "dongeng berhasil dihapus!"]);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function countDongeng(){
        try{
            $data = Cache::remember('dongeng_count', 300, function () {
                return dongengModel::count();
            });
            return response()->json(["row" => $data ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function countAllView()
    {
        try{
            $data = Cache::remember('dongeng_sum_views', 300, function () {
                return (int) dongengModel::sum('view');
            });
            return response()->json(["views" => $data], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function popularView()
    {
        try{
            $data = Cache::remember('dongeng_popular', 300, function () {
                return dongengModel::orderBy("view", "desc")->take(4)->get();
            });
            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sumView(Request $request, $id)
    {
        try{
            $data = dongengModel::find($id);
            if ($data) {
                $data->view = $data->view + 1;
                $data->save();
                $this->clearDongengCache($id);
            }
            return response()->json(["message" => "berhasil menambahkan view! ", "totals" => $data ? $data->view : 0], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}

