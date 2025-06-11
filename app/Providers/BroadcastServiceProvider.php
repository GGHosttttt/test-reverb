<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
class BroadcastServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Broadcast::routes(['middleware' => 'jwt']); // Adjust middleware as needed
        require base_path('routes/channels.php');
    }
}
