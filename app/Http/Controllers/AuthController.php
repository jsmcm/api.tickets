<?php

declare(strict_types=1);


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Support\Facades\Log;


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
        Log::write("debug", "doing login");
        $credentials = request(['email', 'password']);

        Log::write("debug", "credentials: ".print_r($credentials, true));

        // if (! $token = auth()->attempt($credentials)) {
        //     Log::debug("auth failed at 1...");
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }

	    if (! $token = auth()->attempt($credentials)) {
            
            Log::debug("auth failed at 2...");

            if (User::where(["email" => Request()->email])->count() == 0) {
                Log::write("debug", "user is empty");
                return response()->json(['error' => 'No such user'], 404);
            }

            Log::write("debug", "Unauthorized");
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if(auth()->user()->deleted_at != null) {
            Log::write("debug", "No such user");
            return response()->json(['error' => 'No such user'], 404);
        }

        Log::debug("authed, calling respondWithToken: ".$token);
        return $this->respondWithToken($token);
    }

    public function getMe()
    {
        Log::debug("in getMe");
        Log::debug("return: ".print_r(auth()->user(), true));
        
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
        // Log::debug("in me");
        // Log::debug("return: ".print_r(auth()->user(), true));

        // Log::debug("headers...");
        // Log::debug( print_r(Request()->headers->all(), true) );
        
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Log::debug("auth:".print_r(auth()->user(), true));
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
        Log::debug("in respondWithToken: ".$token);
        
        return response()->json(array_merge([
            'access_token'  => $token,
            'token_type'    => 'bearer',
            'expires_in'    => auth()->factory()->getTTL() * 60
        ], $this->getMe()));
    }
}