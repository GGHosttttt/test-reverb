<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Pusher\PushNotifications\PushNotifications;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use function Termwind\renderUsing;
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
    public function sendPushNotification()
    {
        $firebase = (new Factory)->withServiceAccount(__DIR__ . '/firebase_credentials.json');

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
    public function subscribeToTopic(Request $request)
    {
        $validate = $request->validate([
            'token' => 'required|string',
            'topic' => 'required|string'
        ]);

        // return response()->json(['data' => $validate]);

        try {
            $firebase = (new Factory)->withServiceAccount(__DIR__ . '/firebase_credentials.json');
            $messaging = $firebase->createMessaging();
            $messaging->subscribeToTopic($request->topic, [$request->token]);

            return response()->json(['message' => 'Subscribed to topic successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Subscription failed: ' . $e->getMessage()], 500);
        }
    }
}
