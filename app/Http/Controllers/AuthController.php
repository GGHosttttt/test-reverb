<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Events\RealTimeMessage;
use Pusher\PushNotifications\PushNotifications;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        Log::info('Register: User Created', ['user' => $user->toArray()]);
        config(['jwt.ttl' => (int) config('jwt.ttl', 60)]);

        try {
            $token = JWTAuth::fromUser($user);
            Log::info('Register: Token Generated', ['token' => $token]);
        } catch (\Exception $e) {
            Log::error('Register: JWT Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json(['token' => $token])
            ->cookie('jwt_token', $token, 60, '/', "localhost", false, true, false, 'Lax');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        Log::info('Login: Attempt', ['email' => $request->email]);
        config(['jwt.ttl' => (int) config('jwt.ttl', 60)]);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                Log::error('Login: Unauthorized', ['email' => $request->email]);
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            Log::info('Login: Token Generated', ['token' => $token]);
        } catch (\Exception $e) {
            Log::error('Login: JWT Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Could not create token'], 500);
        }
        return response()->json([
            'token' => $token,
        ])
            ->cookie('jwt_token', $token, 60, '/', null, false, false, false); // Omit Domain
        //  ->cookie('jwt_token', $token, 60, '/', 'jwt.test', true, true, false, 'None')
    }
    public function refresh()
    {
        config(['jwt.ttl' => (int) config('jwt.ttl', 60)]);

        try {
            $token = JWTAuth::refresh();
            Log::info('Refresh: Token Generated', ['token' => $token]);
            return response()->json(['token' => $token])
                ->cookie('jwt_token', $token, 60, '/', null, false, true, false, 'Lax');
        } catch (\Exception $e) {
            Log::error('Refresh: JWT Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Could not refresh token'], 500);
        }
    }

    public function me()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                Log::error('Me: User not found');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Log::info('Me: User Retrieved', ['user_id' => $user->id, 'email' => $user->email]);
            // Log::info('Me: Attempting to broadcast event', ['channel' => 'user.' . $user->id, 'event' => 'real-time.message']);
            try {
                event(new RealTimeMessage($user->id, 'User profile accessed via /me'));

                $beamsClient = new PushNotifications([
                    'instanceId' => config('services.pusher.beams_instance_id'),
                    'secretKey' => config('services.pusher.beams_secret_key'),
                ]);

                $publishResponse = $beamsClient->publishToUsers(
                    ['user-' . $user->id],
                    [
                        'web' => [
                            'notification' => [
                                'title' => 'Login Successful',
                                'body' => 'Welcome back, ' . $user->name . '! You logged in at ' . now()->toDateTimeString() . '.',
                                'deep_link' => 'http://jwt.test',
                            ]
                        ]
                    ]
                );
                Log::info(' Notification dispatched successfully', ['data' => $publishResponse]);
            } catch (\Exception $e) {
                Log::error('Me: Event dispatch failed', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            return response()->json($user);
        } catch (\Exception $e) {
            Log::error('Me: JWT Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function logout()
    {
        try {
            auth('api')->logout();
            Log::info('Logout: Success');
            return response()->json(['message' => 'Successfully logged out'])
                ->cookie('jwt_token', null, -1, '/', null, false, true, false, 'None');
        } catch (\Exception $e) {
            Log::error('Logout: JWT Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Could not log out'], 500);
        }
    }
}
