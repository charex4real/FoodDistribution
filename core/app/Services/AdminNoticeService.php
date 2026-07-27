<?php

namespace App\Services;

use App\Constants\Status;
use App\Models\NotificationTemplate;
use App\Models\User;

/**
 * AdminNoticeService
 *
 * Single entry point for sending a direct, personal notice from an admin to
 * one specific user. Used by both the per-user "Send Notification" button on
 * a user's detail page and the top-level admin Notices compose page, so the
 * gating/dispatch logic only lives in one place.
 */
class AdminNoticeService
{
    /**
     * @return array{0: bool, 1: string} [success, message]
     */
    public function send(User $user, string $via, ?string $subject, string $message, ?string $imageUrl, int $adminId): array
    {
        $enabled = NotificationTemplate::where('act', 'DEFAULT')
            ->where($via . '_status', Status::ENABLE)
            ->exists();

        if (!$enabled) {
            return [false, 'Default notification template is not enabled for ' . $via . '.'];
        }

        notify($user, 'DEFAULT', [
            'subject' => $subject,
            'message' => $message,
        ], [$via], pushImage: $imageUrl, adminId: $adminId);

        return [true, 'Notice sent to @' . $user->username . '.'];
    }
}
