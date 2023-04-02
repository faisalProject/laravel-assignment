<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Log;
use App\Models\User;
use Firebase\JWT\JWT;
use Helper\messageError;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthController extends Controller
{
    //
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:user,email',
            'password' => 'required|min:8',
            'confirmation_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return messageError::message($validator->errors()->messages());
        }

        $user = $validator->validated();

        User::create($user);

        $payload = [
            'name' => $user['name'],
            'role' => 'user',
            'iat' => now()->timestamp,
            'exp' => now()->timestamp + 7200
        ];

        $token = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');

        Log::create([
            'module' => 'login',
            'action' => 'Login akun',
            'useraccess' => $user['email']
        ]);

        return response()->json([
            "data" => [
                'msg' => "berhasil login",
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => 'user',
            ],
            "token" => "Bearer {$token}"
        ], 200);
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return messageError::message($validator->errors()->messages());
        }

        if (Auth::attempt($validator->validated())) {
            $payload = [
                'name' => Auth::user()->name,
                'role' => Auth::user()->role,
                'iat' => now()->timestamp,
                'exp' => now()->timestamp + 7200
            ];

            $token = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');

            Log::create([
                'module' => 'login',
                'action' => 'login akun',
                'useraccess' => Auth::user()->email
            ]);

            return response()->json([
                "data" => [
                    'msg'=> "berhasil login",
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'role' => Auth::user()->role
                ],
                "token" => "Bearer {$token}"
            ], 200);
        }
        return response()->json("email atau password salah", 422);

    }
}
