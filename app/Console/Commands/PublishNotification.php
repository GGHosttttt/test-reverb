<?php

namespace App\Console\Commands;

use App\Events\PublicMessage;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishNotification extends Command
{
    protected $signature = 'publish-notification';
    protected $description = 'Command description';

    public function handle()
    {
        Log::info('Notification posting task started at ' . now()->toDateTimeString());
        try {
            $notifications = Notification::
                whereNotNull('scheduled_at')
                ->where('scheduled_at', '<=', now())
                ->get();

            foreach ($notifications as $notification) {
                event(new PublicMessage($notification->message));
                Log::info($notification->message);
            }
            $this->info('Scheduled notification published successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to process article posting: ' . $e->getMessage());
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}
