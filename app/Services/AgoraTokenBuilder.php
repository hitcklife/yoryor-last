<?php

namespace App\Services;

class AgoraTokenBuilder
{
    const ROLE_PUBLISHER = 1;
    const ROLE_SUBSCRIBER = 2;

    /**
     * Build token with UID
     */
    public static function buildTokenWithUid(
        string $appId,
        string $appCertificate,
        string $channelName,
        int $uid,
        int $role,
        int $privilegeExpiredTs
    ): string {
        return self::buildToken($appId, $appCertificate, $channelName, (string)$uid, $role, $privilegeExpiredTs);
    }

    /**
     * Build token with account
     */
    public static function buildTokenWithAccount(
        string $appId,
        string $appCertificate,
        string $channelName,
        string $account,
        int $role,
        int $privilegeExpiredTs
    ): string {
        return self::buildToken($appId, $appCertificate, $channelName, $account, $role, $privilegeExpiredTs);
    }

    private static function buildToken(
        string $appId,
        string $appCertificate,
        string $channelName,
        string $userAccount,
        int $role,
        int $privilegeExpiredTs
    ): string {
        $version = '007';
        $randomInt = mt_rand();
        $timestamp = time();

        $message = $appId . $channelName . $userAccount . $timestamp . $randomInt . $privilegeExpiredTs . $role;
        $signature = hash_hmac('sha256', $message, $appCertificate);

        $token = base64_encode(json_encode([
            'version' => $version,
            'appId' => $appId,
            'channelName' => $channelName,
            'userAccount' => $userAccount,
            'timestamp' => $timestamp,
            'randomInt' => $randomInt,
            'privilegeExpiredTs' => $privilegeExpiredTs,
            'role' => $role,
            'signature' => $signature
        ]));

        return $version . $token;
    }
}
