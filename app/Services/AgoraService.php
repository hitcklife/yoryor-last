<?php

namespace App\Services;

use App\Models\Call;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use App\Services\AgoraTokenBuilder;

class AgoraService
{
    /**
     * Generate a token for Agora RTC
     *
     * @param string $channelName
     * @param string $uid User ID as string
     * @param int $role Role: 1 for attendee, 2 for publisher/host
     * @param int $expireTimeInSeconds Token expire time in seconds
     * @return string Generated token
     * @throws Exception
     */
    public function generateRtcToken(string $channelName, string $uid, int $role = 2, int $expireTimeInSeconds = 3600): string
    {
        $appId = config('services.agora.app_id');
        $appCertificate = config('services.agora.app_certificate');

        if (empty($appId) || empty($appCertificate)) {
            throw new Exception('Agora credentials not configured');
        }

        // Set the privilege expire time
        $currentTimestamp = time();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        // Use our custom AgoraTokenBuilder to generate a token
        $token = AgoraTokenBuilder::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channelName,
            intval($uid),
            $role,
            $privilegeExpiredTs
        );

        return $token;
    }

    /**
     * Create a new call
     *
     * @param User $caller
     * @param User $receiver
     * @param string $type
     * @return Call
     */
    public function createCall(User $caller, User $receiver, string $type = 'video'): Call
    {
        $channelName = Str::uuid()->toString();

        return Call::create([
            'channel_name' => $channelName,
            'caller_id' => $caller->id,
            'receiver_id' => $receiver->id,
            'type' => $type,
            'status' => 'initiated',
        ]);
    }

    /**
     * Update call status
     *
     * @param Call $call
     * @param string $status
     * @return Call
     */
    public function updateCallStatus(Call $call, string $status): Call
    {
        if ($status === 'ongoing' && is_null($call->started_at)) {
            $call->started_at = now();
        }

        if (in_array($status, ['completed', 'missed', 'rejected']) && is_null($call->ended_at)) {
            $call->ended_at = now();
        }

        $call->status = $status;
        $call->save();

        return $call;
    }
}
