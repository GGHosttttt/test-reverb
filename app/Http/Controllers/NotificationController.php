<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Pusher\PushNotifications\PushNotifications;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
class NotificationController extends Controller
{

    // pusher beam
    public function beamsAuth(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $beamsClient = new PushNotifications([
            'instanceId' => config('services.pusher.beams_instance_id'),
            'secretKey' => config('services.pusher.beams_secret_key'),
        ]);

        $beamsToken = $beamsClient->generateToken('user-' . $user->id);
        return response()->json($beamsToken);
    }
    public function sendNoti()
    {
        try {
            $beamClient = new PushNotifications([
                'instanceId' => config('services.pusher.beams_instance_id'),
                'secretKey' => config('services.pusher.beams_secret_key'),
            ]);

            $publishResponse = $beamClient->publishToInterests(
                ['your-interest-name'],
                [
                    'web' => [
                        'notification' => [
                            'title' => 'Push tv hz',
                            'body' => 'yayyyy',
                            'deep_link' => 'https://youtu.be/dQw4w9WgXcQ',
                        ]
                    ]
                ]
            );

            // Convert stdClass to array for logging
            Log::info('Notification sent:', (array) $publishResponse);
            return response()->json(['success' => true, 'response' => $publishResponse]);
        } catch (\Exception $e) {
            Log::error('Pusher Beams Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // firebase
    // public notification
    public function sendPushNotification()
    {

        $credentials = [
            'type' => config('services.firebase.type'),
            'project_id' => config('services.firebase.project_id'),
            'private_key_id' => config('services.firebase.private_key_id'),
            'private_key' => config('services.firebase.private_key'),
            'client_email' => config('services.firebase.client_email'),
            'client_id' => config('services.firebase.client_id'),
            'auth_uri' => config('services.firebase.auth_uri'),
            'token_uri' => config('services.firebase.token_uri'),
            'auth_provider_x509_cert_url' => config('services.firebase.auth_provider_x509_cert_url'),
            'client_x509_cert_url' => config('services.firebase.client_x509_cert_url')
        ];

        $firebase = (new Factory)->withServiceAccount($credentials);
        $messaging = $firebase->createMessaging();
        $message = CloudMessage::fromArray([
            'notification' => [
                'title' => 'Hello from Firebase!',
                'body' => 'This is a test notification.'
            ],
            'topic' => 'global'
        ]);

        $messaging->send($message);

        return response()->json(['message' => 'Push notification sent successfully']);
    }
    // public function subscribeToTopic(Request $request)
    // {
    //     $validate = $request->validate([
    //         'token' => 'required|string',
    //         'topic' => 'required|string'
    //     ]);
    //     // return response()->json(['data' => $validate]);

    //     try {

    //         $credentials = [
    //             'type' => config('services.firebase.type'),
    //             'project_id' => config('services.firebase.project_id'),
    //             'private_key_id' => config('services.firebase.private_key_id'),
    //             'private_key' => config('services.firebase.private_key'),
    //             'client_email' => config('services.firebase.client_email'),
    //             'client_id' => config('services.firebase.client_id'),
    //             'auth_uri' => config('services.firebase.auth_uri'),
    //             'token_uri' => config('services.firebase.token_uri'),
    //             'auth_provider_x509_cert_url' => config('services.firebase.auth_provider_x509_cert_url'),
    //             'client_x509_cert_url' => config('services.firebase.client_x509_cert_url')
    //         ];

    //         $firebase = (new Factory)->withServiceAccount($credentials);
    //         $messaging = $firebase->createMessaging();
    //         $messaging->subscribeToTopic($request->topic, [$request->token]);
    //         return response()->json(['message' => 'Subscribed to topic successfully']);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Subscription failed: ' . $e->getMessage()], 500);
    //     }
    // }


    // private notification
    public function sendPushNotificationPrivate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string'
        ]);

        try {
            $firebase = (new Factory)->withServiceAccount(config('services.firebase'));
            $messaging = $firebase->createMessaging();

            $user = User::find($request->user_id);
            if (!$user->fcm_token) {
                throw new \Exception('No FCM token for user');
            }

            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification([
                    'title' => $request->title,
                    'body' => $request->body
                ])
                ->withData(['user_id' => (string) $request->user_id]);

            $messaging->send($message);

            Log::info('Notification sent to user', [
                'user_id' => $user->id,
                'title' => $request->title,
                'body' => $request->body,
                'token' => $user->fcm_token
            ]);

            return response()->json(['message' => 'Notification sent successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to send notification', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to send notification: ' . $e->getMessage()], 500);
        }
    }
    public function subscribeToTopic(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);
        Log::info($request->token);
        try {
            $user = Auth::user(); // Assumes JWT or Sanctum authentication
            Log::info($user);
            if (!$user) {
                throw new \Exception('User not authenticated.');
            }

            $user->update(['fcm_token' => $request->token]);

            Log::info('Stored FCM token for user', [
                'user_id' => $user->id,
                'token' => $request->token
            ]);

            return response()->json(['message' => 'Token stored successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to store token', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to store token: ' . $e->getMessage()], 500);
        }
    }

}
