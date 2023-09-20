<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function create(Request $request){
        $rules= [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:8'
        ];
        $validator = \Validator::make($request->input(), $rules);
        if($validator->fails()){
            return response()->json([
                'status'=> false,
                'errors'=> $validator->errors()->all()
            ], 400);
        }

        try{
            $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=> Hash::make($request->password)
            ]);
            return response()->json([
                'status'=> true,
                'message'=> "User registered successfully",
                'token' => $user->createToken('API TOKEN')->plainTextToken
            ], 200);
        } catch (customException $e) {
            return response()->json([
                'status'=> false,
                'errors'=> $e->errorMessage()
            ], 400);
        }
    }

    public function login(Request $request){
        $rules= [
            'email' => 'required|string|email|max:100',
            'password' => 'required|string'
        ];
        $validator = \Validator::make($request->input(), $rules);
        if($validator->fails()){
            return response()->json([
                'status'=> false,
                'errors'=> $validator->errors()->all()
            ], 400);
        }

        if(!Auth::attempt($request->only('email', 'password'))){
            return response()->json([
                'status'=> false,
                'errors'=> ['Unauthorized']
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        return response()->json([
            'status'=> true,
            'message'=> "User logged in successfully",
            'data' => $user,
            'token' => $user->createToken('API TOKEN')->plainTextToken
        ], 200);
    }

    public function logout(){
        auth()->user()->tokens()->delete();
        return response()->json([
            'status'=> true,
            'message'=> "User logged out successfully"
        ], 200);
    }
}
