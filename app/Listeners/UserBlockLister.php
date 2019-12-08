<?php

namespace App\Listeners;

use App\User;
use App\Events\BlockAttempsUsers;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserBlockLister
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(BlockAttempsUsers $event)
    {
        $user = User::where('email', $event->request->email)->first();
        $user->update(['status' => false]);
    }
}
