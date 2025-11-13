<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $readStatus = $user ? $this->getReadStatusFor($user) : ['is_read' => false, 'read_at' => null, 'is_mine' => false];

        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'sender_id' => $this->sender_id,
            'content' => $this->content,
            'message_type' => $this->message_type,
            'media_url' => $this->media_url,
            'thumbnail_url' => $this->thumbnail_url,
            'media_data' => $this->media_data,
            'reply_to_message_id' => $this->reply_to_message_id,
            'is_edited' => $this->is_edited,
            'edited_at' => $this->edited_at,
            'sent_at' => $this->sent_at,
            'status' => $this->status,

            // Read status for current user
            'is_read' => $readStatus['is_read'],
            'read_at' => $readStatus['read_at'],
            'is_mine' => $readStatus['is_mine'],

            // Sender information
            'sender' => $this->when(
                $this->relationLoaded('sender'),
                function () {
                    return new UserResource($this->sender);
                }
            ),

            // Reply-to message
            'reply_to' => $this->when(
                $this->relationLoaded('replyTo') && $this->replyTo,
                function () {
                    return new MessageResource($this->replyTo);
                }
            ),

            // Enhanced call data for call messages
            'call_details' => $this->when(
                $this->isCallMessage() && $this->relationLoaded('call') && $this->call,
                function () use ($user) {
                    return [
                        'call_id' => $this->call->id,
                        'type' => $this->call->type,
                        'status' => $this->call->status,
                        'duration_seconds' => $this->call->getDurationInSeconds(),
                        'formatted_duration' => $this->call->getFormattedDuration(),
                        'started_at' => $this->call->started_at,
                        'ended_at' => $this->call->ended_at,
                        'is_active' => $this->call->isActive(),
                        'other_participant' => $user ? $this->call->getOtherParticipant($user) : null,
                    ];
                }
            ),

            // Media attachments
            'media' => $this->when(
                $this->relationLoaded('media') && $this->media->isNotEmpty(),
                function () {
                    return $this->media->map(function ($media) {
                        return [
                            'id' => $media->id,
                            'type' => $media->type,
                            'url' => $media->url,
                            'thumbnail_url' => $media->thumbnail_url,
                            'file_name' => $media->file_name,
                            'file_size' => $media->file_size,
                            'mime_type' => $media->mime_type,
                        ];
                    });
                }
            ),
        ];
    }
}
