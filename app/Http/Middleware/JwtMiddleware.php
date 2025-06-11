<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class JwtMiddleware
{
    // public function handle(Request $request, Closure $next)
    // {
    //     $token = $request->cookie('jwt_token');

    //     if (!$token) {
    //         Log::error('JwtMiddleware: No token found in cookie');
    //         return response()->json(['error' => 'Token not provided'], 401)
    //             ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
    //             ->header('Access-Control-Allow-Credentials', 'true');
    //     }

    //     try {
    //         $user = JWTAuth::setToken($token)->authenticate();
    //         if (!$user) {
    //             Log::error('JwtMiddleware: User not found');
    //             return response()->json(['error' => 'User not found'], 401)
    //                 ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
    //                 ->header('Access-Control-Allow-Credentials', 'true');
    //         }
    //         Log::info('JwtMiddleware: User authenticated', ['user_id' => $user->id]);
    //         auth('api')->setUser($user); // Set user for the guard
    //     } catch (JWTException $e) {
    //         Log::error('JwtMiddleware Error', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //         ]);
    //         return response()->json(['error' => 'Token not valid'], 401)
    //             ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
    //             ->header('Access-Control-Allow-Credentials', 'true');
    //     }

    //     return $next($request);
    // }
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');
        $token = $authHeader ? str_replace('Bearer ', '', $authHeader) : $request->cookie('jwt_token');

        if (!$token) {
            Log::error('JwtMiddleware: No token found in header or cookie');
            return response()->json(['error' => 'Token not provided'], 401)
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Headers', 'Authorization, Content-Type');
        }

        try {
            $user = JWTAuth::setToken($token)->authenticate();
            if (!$user) {
                Log::error('JwtMiddleware: User not found', ['token' => $token]);
                return response()->json(['error' => 'User not found'], 401)
                    ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                    ->header('Access-Control-Allow-Credentials', 'true');
            }
            Log::info('JwtMiddleware: User authenticated', ['user_id' => $user->id, 'token' => $token]);
            auth('api')->setUser($user);
        } catch (JWTException $e) {
            Log::error('JwtMiddleware Error', [
                'message' => $e->getMessage(),
                'token' => $token,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Token not valid'], 401)
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $next($request);
    }
}
