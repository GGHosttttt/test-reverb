<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Pusher\PushNotifications\PushNotifications;

class NotificationController extends Controller
{
    public function sendNoti()
    {
        try {
            $beamClient = new PushNotifications([
                'instanceId' => config('services.pusher.beams_instance_id'),
                'secretKey' => config('services.pusher.beams_secret_key'),
            ]);

            $publishResponse = $beamClient->publishToInterests(
                // kom pas, ot dg ah ng ey te
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
}
