<?php

declare(strict_types=1);


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        // if (! $token = auth()->attempt($credentials)) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }

	    if (! $token = auth()->attempt($credentials)) {
        
            if (User::where(["email" => Request()->email])->count() == 0) {
                return response()->json(['error' => 'No such user'], 404);
            }

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if(auth()->user()->deleted_at != null) {
           return response()->json(['error' => 'No such user'], 404);
        }

        return $this->respondWithToken($token);
    }

    public function getMe()
    {
        return [
            "level" => auth()->user()->level,
            "email" => auth()->user()->email,
            "id"    => auth()->user()->id,
            "name"  => auth()->user()->name,
        ];
    }




    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json(array_merge([
            'access_token'  => $token,
            'token_type'    => 'bearer',
            'expires_in'    => auth()->factory()->getTTL() * 60
        ], $this->getMe()));
    }
}