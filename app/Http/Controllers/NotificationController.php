<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pusher\PushNotifications\PushNotifications;
class NotificationController extends Controller
{
    public function sendNoti()
    {
        $beamClient = new PushNotifications([
            'instanceId' => "e6c6a85a-9f57-44ec-8f3d-b9da25165d4f",
            'secretKey' => "63082A19E9BB325C50D7B123E33A08100F818853722167CDF55152CE301E0909",
        ]);

        $publishResponse = $beamClient->publishToInterests(
            ['hello'],
            [
                'fcm' => [
                    "notification" => [
                        'title' => "hello",
                        'body' => "hello, world",
                    ]
                ]
            ]
        );

        return response()->json($publishResponse);
    }
}
