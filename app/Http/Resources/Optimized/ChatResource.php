<?php

namespace App\Http\Resources\Optimized;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Optimized to include only essential data
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'other_user' => new MinimalUserResource($this->whenLoaded('otherUser')),
            'last_message' => $this->when($this->last_message, function () {
                return [
                    'content' => $this->getMessagePreview(),
                    'sent_at' => $this->last_message_sent_at,
                    'is_mine' => $this->last_message_sender_id === auth()->id()
                ];
            }),
            'unread_count' => (int) ($this->unread_count ?? 0),
            'updated_at' => $this->updated_at
        ];
    }

    /**
     * Get message preview based on type
     */
    private function getMessagePreview(): string
    {
        if (!$this->last_message_content) {
            return '';
        }

        switch ($this->last_message_type) {
            case 'image':
                return '📷 Photo';
            case 'video':
                return '📹 Video';
            case 'voice':
                return '🎤 Voice message';
            case 'audio':
                return '🎵 Audio';
            case 'file':
                return '📎 File';
            case 'location':
                return '📍 Location';
            case 'call':
                return '📞 Call';
            default:
                return mb_strlen($this->last_message_content) > 50 
                    ? mb_substr($this->last_message_content, 0, 50) . '...' 
                    : $this->last_message_content;
        }
    }
}