<?php

namespace App\Http\Controllers;

use Mail;
use App\Mail\verify;
use App\Models\User;
use App\Mail\forgotPassword;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class authController extends Controller
{
    public function accountVerify($token){
        $id = decrypt($token);
        $user = User::find($id);
        $user->email_verified_at = now();
        $user->save();
        return response()->json([
            "success" => "true",
            "message" => "Berhasil verifikasi akun!"
        ], 200);
    }
    //

    public function updateProfile(Request $request, $id){
        try{
            User::find($id)->update($request);
            return response()->json(["message" => "Profile successfully update"], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function validJWT($token){
        try{

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function isAvailableUsername(Request $request){
        try{
            $user = User::where('username', $request->search)->first();
            if($user){
                return response()->json(["isAvailable" => false]);
            }
            return response()->json(["isAvailable" => true]);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function verify($token){
        try{

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function emailVerify($to){
        Mail::to($to)->send(new verify($to));
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'username' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',
            'confirm_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $credential = $request->all();
        $credential['password'] = Hash::make($credential['password']);
        $credential['refreshToken'] = "";
        $credential['originalPass'] = "";
        $user = User::create($credential);

        $success['token'] = $user->createToken('auth_token')->plainTextToken;
        $success['id'] = $user->id;
        $success['name'] = $user->nama;
                
        Mail::to($credential['email'])->send(new verify($user));
        
        return response()->json([
            'success' => true,
            "data" => $success
        ], 200);
    }

    

    public function forgotPasswordSend(Request $request){
        $user = User::where("email", $request->email)->first();
        if($user){
            Mail::to($user->email)->send(new forgotPassword($user));
            return response()->json(['success' => false, "message" => "Email berhasil dikirim"]); 
        }
        return response()->json(['success' => false, "message" => "Email tidak ditemukan"]);

    }

    public function forgotPasswordForm($token, Request $request){
        $id = decrypt($token);
        $user = User::find($id);
        
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(["success" => true, "message" => "password berhasil diganti"]);
    }

    public function isAvailableEmail(Request $request){
        try{
            $user = User::where('email', $request->search)->first();
            if($user){
                return response()->json(["isAvailable" => false], 200);
            }
            return response()->json(["isAvailable" => true], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function checkEmail(Request $request){
        try{
            $user = User::where('email', $request->search)->first();
            if($user){
                return response()->json(["checkEmailExist" => true], 200);
            }
            return response()->json(["checkEmailExist" => false], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function login(Request $request){
        $credentials = $request->only('credential', 'password');
        $fieldType = filter_var($credentials['credential'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if(Auth::attempt([$fieldType => $request->credential, 'password' => $request->password])){
            $auth = Auth::user();
            $success['token']   = $auth->createToken('auth_token',['*'],now()->addDay() )->plainTextToken;
            $success['id'] = $auth->id;
            $success['name'] = $auth->nama;
            $success['email_verified_at'] = $auth->email_verified_at;

            return response()->json([
                'success' => true,
                'message' => "login sukses",
                'data' => $success
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    public function getAccessToken($user){  
        $payload = [
            'id' => $user->id,
            'exp' => time() + 60 * 60,
        ];
        $token = JWTAuth::fromUser($user, $payload);
        return $token;
    }

    public function getRefreshToken($id){
        try{
            $user = User::find($id);

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error: ' . $e->getMessage() ,
            ], 500);
        }
    }

    public function logout(Request $request){
        Auth::user()->tokens()->delete();
        return response()->json(["status" => true], 200);
    }

    public function authenticationToken(){

    }

    public function testAuthToken(){
        return response()->json(["status" => true], 200);
    }

    public function refreshNewToken(){

    }

    
}
