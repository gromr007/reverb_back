<?php

namespace App\Http\Controllers\Api\Sanctum;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class SanctumController extends Controller
{

    public function register(Request $request)
    {
        // 1
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'device_name' => ['required', 'string']
        ]);

        // 2
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // 3
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        // 4
        $token = $user->createToken($request->device_name)->plainTextToken;


        return response()->json(['token' => $token], 200);
    }

    public function token(Request $request)
    {
        // 1
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'device_name' => ['required', 'string']
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // 2
        $user = User::where('email', $request->email)->first();

        // 3
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'The provided credentials are incorrect.'], 401);
        }

        // 4
        $user->tokens()->delete();
        //$user->currentAccessToken()->delete();
        $token = $user->createToken($request->device_name)->plainTextToken;
        return response()->json(['token' => $token], 200);

    }




}
