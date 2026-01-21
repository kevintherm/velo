<?php

use App\Models\RealtimeConnection;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('{channelName}', function ($user, $channelName) {
    return RealtimeConnection::where('channel_name', $channelName)
        ->where('record_id', $user->meta?->_id)
        ->exists();
});