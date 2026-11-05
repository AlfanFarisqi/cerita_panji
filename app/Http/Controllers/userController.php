<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\verify;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class userController extends Controller
{
    //


    public function addUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['refreshToken'] = '';
        $data['originalPass'] = ''; // ⚠️ tidak aman, sebaiknya dihapus kalau tidak benar-benar dibutuhkan
        $data['role'] = $data['role'] ?? 'umum';
        // $data["email_verified_at"] = 
        try {
            $user = User::create($data);

            $token = $user->createToken('auth_token')->plainTextToken;

            // Mail::to($data['email'])->send(new verify($user)); // pastikan Mailable `verify` ada

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'id' => $user->id,
                    'name' => $user->nama,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getUser()
    {
        try {
            return response()->json(User::all(), 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getUserById($id)
    {
        try {
            return response()->json(User::find($id), 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateUser(Request $request, $id)
    {
        try {
            $data = User::find($id);
            if ($request->password) {
                $data->password = Hash::make($request->password);
            }
            $data->nama = $request->nama;
            $data->username = $request->username;
            $data->email = $request->email;
            $data->kelas = $request->kelas;
            $data->sekolah = $request->sekolah;

            $data->save();

            return response()->json(["message" => "user berhasil diupdate"], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteUser($id)
    {
        try {
            $user = User::find($id);
            $user->delete();

            return response()->json(["message" => "berhasil delete user"], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function profile()
    {
        $user = Auth::user();
        return response()->json($user, 200);
    }

    public function updateProfile(Request $request, $id)
    {
        try {
            User::find($id)
                ->update($request);
            return response()->json(["message" => 'berhasil mengupdate Profile'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }

    }
}
