<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use App\Models\forumQuizModel;
use Ramsey\Uuid\Uuid;
use App\Models\rekapNilaiModel;
use Illuminate\Support\Facades\Cache;

class forumController extends Controller
{
    private function clearForumCache($forumId = null, $userId = null, $rekapId = null)
    {
        if ($forumId) Cache::forget('forum_rekap_' . $forumId);
        if ($userId) Cache::forget('user_forum_quiz_' . $userId);
        if ($rekapId) Cache::forget('rekap_' . $rekapId);
    }

    public function getRekapByForumId($id_forum)
    {
        try{
            $data = Cache::remember('forum_rekap_' . $id_forum, 180, function () use ($id_forum) {
                return rekapNilaiModel::join('users', 'users.id', '=', 'rekapnilai.id_User')
                    ->with(['user', 'forum'])
                    ->where('id_Forum', $id_forum)
                    ->orderBy('users.nama', 'ASC')
                    ->select('rekapnilai.*')
                    ->get();
            });

            return response()->json($data, 200);    

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function joinForumByToken(Request $request){

        try{
            $forum = forumQuizModel::where('token', $request->token)->first();
            if(!$forum){
                return response()->json(["message" => "invalid token"], 400);
            }else if(new DateTime($forum->expired_date) < now()){
                return response()->json(["message" => "token expired"]);
            }else if(new DateTime($forum->access_date) > now()){
                return response()->json(["message" => "token not yet avaliable"]);
            }
    
            $checkJoinStatus = rekapNilaiModel::where([
                "id_User" => $request->id_user,
                "id_Forum" => $forum->id
            ])->exists();
    
            if($checkJoinStatus){
                return response()->json(['message' => "already join"]);
            }
    
            rekapNilaiModel::create([
                "id" => Uuid::uuid4(),
                "id_Forum" => $forum->id,
                "id_User" => $request->id_user
            ]);

            $this->clearForumCache($forum->id, $request->id_user);
    
            return response()->json([
                "message" => "Success join forum",
                "data" => $forum
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function updateNilaiQuiz(Request $request){
        try{
            $data = rekapNilaiModel::find($request->id);
            if ($data) {
                $data->nilai = $request->nilai;
                $data->save();
                $this->clearForumCache($data->id_Forum, $data->id_User, $data->id);
            }

            return response()->json([
                $data
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function getQuizByUserId($id_user)
    {
        try{
            $data = Cache::remember('user_forum_quiz_' . $id_user, 180, function () use ($id_user) {
                return rekapNilaiModel::with(['user', 'forum'])->where("id_User", $id_user)->get();
            });
            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function getRekapById($id)
    {
        try{
            $data = Cache::remember('rekap_' . $id, 180, function () use ($id) {
                return rekapNilaiModel::find($id);
            });
            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }
}

