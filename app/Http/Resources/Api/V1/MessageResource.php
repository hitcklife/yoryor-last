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
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'sender_id' => $this->sender_id,

            // Message Content
            'content' => $this->content,
            'message_type' => $this->message_type,

            // Status Flags
            'is_mine' => $this->sender_id === auth()->id(),
            'is_edited' => (bool) $this->is_edited,
            'status' => $this->status,

            // Media Data (when applicable)
            'media_url' => $this->media_url,
            'thumbnail_url' => $this->thumbnail_url,
            'media_data' => $this->when($this->media_data, fn() => $this->getFormattedMediaData()),

            // Reply Information
            'reply_to_message_id' => $this->reply_to_message_id,
            'reply_to' => new MessageResource($this->whenLoaded('replyTo')),

            // Call Information (when message is a call)
            'call_id' => $this->call_id,
            'call' => $this->whenLoaded('call'),
            'is_call_message' => $this->isCallMessage(),
            'call_data' => $this->when($this->isCallMessage(), fn() => $this->getCallData()),

            // Timestamps
            'sent_at' => $this->sent_at?->toIso8601String(),
            'edited_at' => $this->edited_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Relationships (when loaded)
            'sender' => new UserResource($this->whenLoaded('sender')),
            'chat' => $this->whenLoaded('chat'),
            'reads' => $this->whenLoaded('reads'),

            // Read Status (when requested for specific user)
            'is_read' => $this->when(
                $request->has('check_read_status'),
                fn() => $this->isReadBy(auth()->user())
            ),
            'read_at' => $this->when(
                $request->has('check_read_status'),
                fn() => $this->getReadStatusFor(auth()->user())?->read_at?->toIso8601String()
            ),
        ];
    }

    /**
     * Get formatted media data based on message type.
     */
    private function getFormattedMediaData(): array
    {
        $mediaData = is_string($this->media_data)
            ? json_decode($this->media_data, true)
            : $this->media_data;

        if (!$mediaData) {
            return [];
        }

        // Return type-specific media fields
        return match ($this->message_type) {
            'image' => [
                'url' => $mediaData['url'] ?? $this->media_url,
                'thumbnail_url' => $mediaData['thumbnail_url'] ?? $this->thumbnail_url,
                'width' => $mediaData['width'] ?? null,
                'height' => $mediaData['height'] ?? null,
                'size' => $mediaData['size'] ?? null,
                'mime_type' => $mediaData['mime_type'] ?? null,
            ],
            'video' => [
                'url' => $mediaData['url'] ?? $this->media_url,
                'thumbnail_url' => $mediaData['thumbnail_url'] ?? $this->thumbnail_url,
                'duration' => $mediaData['duration'] ?? null,
                'width' => $mediaData['width'] ?? null,
                'height' => $mediaData['height'] ?? null,
                'size' => $mediaData['size'] ?? null,
                'mime_type' => $mediaData['mime_type'] ?? null,
            ],
            'voice', 'audio' => [
                'url' => $mediaData['url'] ?? $this->media_url,
                'duration' => $mediaData['duration'] ?? null,
                'size' => $mediaData['size'] ?? null,
                'mime_type' => $mediaData['mime_type'] ?? null,
                'waveform' => $mediaData['waveform'] ?? null,
            ],
            'file' => [
                'url' => $mediaData['url'] ?? $this->media_url,
                'original_name' => $mediaData['original_name'] ?? $mediaData['name'] ?? null,
                'size' => $mediaData['size'] ?? null,
                'mime_type' => $mediaData['mime_type'] ?? null,
                'extension' => $mediaData['extension'] ?? null,
            ],
            'location' => [
                'latitude' => $mediaData['latitude'] ?? null,
                'longitude' => $mediaData['longitude'] ?? null,
                'address' => $mediaData['address'] ?? null,
                'name' => $mediaData['name'] ?? null,
            ],
            'call' => [
                'call_type' => $mediaData['call_type'] ?? null,
                'duration' => $mediaData['duration'] ?? null,
                'call_status' => $mediaData['call_status'] ?? $mediaData['status'] ?? null,
                'started_at' => $mediaData['started_at'] ?? null,
                'ended_at' => $mediaData['ended_at'] ?? null,
            ],
            default => $mediaData,
        };
    }
}
