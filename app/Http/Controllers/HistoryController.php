<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\historyModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function setHistory(Request $request){
        $id = Auth::id();
        $dongengId = $request->id_dongeng;
        $history = historyModel::
            where('id_user', $id)
            ->where('id_dongeng', $dongengId)
            ->first();
        if (!$history) {
            $history = new historyModel();
            $history->id_user = $id;
            $history->id_dongeng = $dongengId;
        }
        $history->save();

        Cache::forget('user_recommend_' . $id);

        return response()->json(["message" => "success"], 200);
    }
    //
    public function getHistory()
    {
        $userId = Auth::id();
        $result = Cache::remember('user_recommend_' . $userId, 300, function () use ($userId) {
            $targetVector = null;

            $dataSet = Self::getheringHistory(User::with(['history'])->get());
            $dongengList = Self::getDongeng($dataSet);
            $userVectors = array_values(array_map(function ($user) use ($dongengList) {
                return [
                    'id_user' => $user['id_user'],
                    'vector' => Self::createUserVector($user, $dongengList)
                ];
            }, $dataSet));
            
            
            foreach ($userVectors as $user) {
                if ($user['id_user'] == $userId) {
                    $targetVector = $user['vector'];
                    break;
                }
            }
            
            $similarities = [];
            
            foreach ($userVectors as $user) {
                if ($user['id_user'] == $userId) continue;
                
                $similarity = Self::cosineSimilarity($targetVector, $user['vector']);
                
                $similarities[] = [
                    'id_user' => $user['id_user'],
                    'similarity' => $similarity
                ];
            }
            
            $similarities = Self::order($similarities, 'similarity'); 
            
            $historyRecommend = historyModel::with(['dongeng'])->whereIn('id_user', Self::getUserId($similarities))->get();
            $history = [];
            $userObj = User::with(['history'])->find($userId);
            if ($userObj && $userObj->history) {
                foreach ($userObj->history as $value) {
                    $history[] = $value->id_dongeng;
                }
            }
            return array_slice(Self::order(Self::historyComparer($history, $historyRecommend), 'view'), 0, 5);
        });

        return response()->json([$result], 200);
    }


    public function historyComparer($history, $recommend){
        $result = [];

        foreach($history as $value){
            foreach($recommend as $rec){
                if($value != $rec->id_dongeng && !in_array($rec->dongeng, $result)){
                    $result[] = $rec->dongeng;
                }
                
            }
        }
        return $result;
    }

    public function getUserId($similarities){
        $id_user = [];
        foreach($similarities as $user){
            $id_user[] = $user['id_user'];
        }
        return $id_user;
    }

    public function order($similarities, $type){
        usort($similarities, function ($a, $b) use ($type) {
            return $b[$type] <=> $a[$type];
        });

        return $similarities;
    }
    
    public function getDongeng($dataSet){
        $allDongeng = [];
        foreach ($dataSet as $user) {
            foreach ($user['id_dongeng'] as $dongeng) {
                $allDongeng[] = $dongeng;
            }
        }
        return array_values(array_unique($allDongeng));
    }

    public function createUserVector($user, $dongengList) {
        return array_map(function ($dongeng) use ($user) {
            return in_array($dongeng, $user['id_dongeng']) ? 1 : 0;
        }, $dongengList);
    }

    function cosineSimilarity($vecA, $vecB) {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        for ($i = 0; $i < count($vecA); $i++) {
            $dotProduct += $vecA[$i] * $vecB[$i];
            $magnitudeA += $vecA[$i] ** 2;
            $magnitudeB += $vecB[$i] ** 2;
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) return 0;
        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    public function getheringHistory($users){
        $results = [];

        foreach ($users as $user) {
            $userHistory = [];
            $userHistory['id_user'] = $user->id;
            $userHistory['id_dongeng'] = [];

            foreach ($user->history as $history) {
                $userHistory['id_dongeng'][] = $history->id_dongeng;
            }

            $results[] = $userHistory;
        }
        return $results;
    }

    public function testTopKAccuracy(Request $request){
        $k = $request->input('k', 5); // default top-100 jika tidak disediakan

        // Ambil seluruh data history dari semua pengguna dengan relasi history
        $users = User::with(['history'])->get();

        // Persiapkan data training dan data test.
        // Kita gunakan data history yang sudah terurut (misal berdasarkan waktu, jika tersedia).
        // Jika pengguna memiliki <2 interaksi, maka tidak dipakai dalam pengujian.
        $trainingData = [];
        $testData = [];

        foreach ($users as $user) {
            $histories = $user->history->pluck('id_dongeng')->toArray();
            if(count($histories) < 2) continue; // Skip jika tidak cukup data
            // Asumsikan histori sudah terurut; ambil interaksi terakhir sebagai test
            $testItem = array_pop($histories);
            $trainingData[$user->id] = $histories; // simpan data training berbentuk array
            $testData[$user->id] = $testItem;
        }
        // return response()->json([
        //     "training_data" => $trainingData,
        //     "test_data" => $testData
        // ], 200);

        // Gabungkan seluruh item unik (dongeng) dalam data training untuk membuat vector fitur
        $dongengList = [];
        foreach($trainingData as $userId => $histories) {
            $dongengList = array_merge($dongengList, $histories);
        }
        $dongengList = array_values(array_unique($dongengList));

        
        // Buat vector untuk masing-masing pengguna berdasarkan data training
        $userVectors = [];
        foreach($trainingData as $userId => $histories) {
            $userVectors[$userId] = array_map(function($dongeng) use ($histories){
                return in_array($dongeng, $histories) ? 1 : 0;
            }, $dongengList);
        }

        $hitCount = 0;
        $totalUsers = count($userVectors);

        // Untuk setiap pengguna, hitung rekomendasi Top-K menggunakan kemiripan dengan pengguna lain
        foreach($userVectors as $userId => $targetVector){
            $recommendScores = []; // menyimpan skor rekomendasi per item

            foreach($userVectors as $otherId => $otherVector){
                if($userId == $otherId) continue;
                $sim = $this->cosineSimilarity($targetVector, $otherVector);
                if($sim <= 0) continue; // lewati jika kemiripan nol atau negatif
                // Ambil data training pengguna lain
                $otherTraining = $trainingData[$otherId];
                // Agregasi rekomendasi: tambahkan skor similarity untuk setiap item yang belum ada pada training pengguna target
                foreach($otherTraining as $item){
                    // Jika item sudah pernah dilihat oleh target, lewati
                    if(in_array($item, $trainingData[$userId])) continue;
                    if(!isset($recommendScores[$item])){
                        $recommendScores[$item] = 0;
                    }
                    $recommendScores[$item] += $sim;
                }
            }
            // Urutkan rekomendasi berdasarkan skor secara menurun
            arsort($recommendScores);
            // Ambil Top-K item rekomendasi
            $topKItems = array_slice(array_keys($recommendScores), 0, $k);
            // Cek apakah item test pengguna ada dalam rekomendasi Top-K
            if(isset($testData[$userId]) && in_array($testData[$userId], $topKItems)){
                $hitCount++;
            }
        }

        $accuracy = $totalUsers > 0 ? $hitCount / $totalUsers : 0;

        return response()->json([
            "top_k" => $k,
            "accuracy" => $accuracy,
            "total_users" => $totalUsers,
            "hit_count" => $hitCount
        ], 200);
    }
}