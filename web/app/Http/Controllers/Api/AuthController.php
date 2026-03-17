<?php

namespace App\Http\Controllers\Api;

use App\DTO\RegisterUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Auth;
use Dotenv\Repository\RepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class AuthController extends Controller
{
    public function login(LoginUserRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Identifiants invalides.',
            ], 401);
        }

        return response()->json([
            'message' => 'Logged successfully',
            'token' => $token,
            'type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl') * 60,
            'user' => Auth::guard('api')->user(),
        ]);
    }

    public function register(RegisterUserRequest $request)
    {
        $dto = RegisterUserDTO::fromRequest($request);
        $user = User::create($dto->toArray());
        $token = Auth::guard('api')->login($user);
        return response()->json([
            'message' => 'Registered successfully',
            'token' => $token,
            'type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl') *  60,
            'user' => $user,
        ], 201);
    }

    public function logout()
    {
        Auth::guard('api')-> logout();
        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }
}
