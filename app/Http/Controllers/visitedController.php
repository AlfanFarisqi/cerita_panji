<?php

namespace App\Http\Controllers;

use App\Models\visitedModel;
use Illuminate\Support\Facades\Cache;

class visitedController extends Controller
{
    public function newVisited()
    {
        try{
            visitedModel::create();
            Cache::forget('visited_count');
            return response()->json(["Message" => "new visited"]);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function getAllVisited()
    {
        try{
            $count = Cache::remember('visited_count', 60, function () {
                return visitedModel::count();
            });
            return response()->json(["visited" => $count], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }
}

