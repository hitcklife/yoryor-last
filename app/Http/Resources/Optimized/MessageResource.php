<?php

namespace App\Http\Resources\Optimized;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Optimized for minimal payload
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'type' => $this->message_type,
            'sender_id' => $this->sender_id,
            'is_mine' => $this->sender_id === auth()->id(),
            'is_read' => (bool) $this->is_read,
            'sent_at' => $this->sent_at->toISOString(),
            $this->mergeWhen($this->edited_at, [
                'edited_at' => $this->edited_at?->toISOString()
            ]),
            $this->mergeWhen($this->media_data && $this->message_type !== 'text', [
                'media' => $this->getMediaData()
            ])
        ];
    }

    /**
     * Get optimized media data
     */
    private function getMediaData(): array
    {
        $mediaData = is_string($this->media_data) 
            ? json_decode($this->media_data, true) 
            : $this->media_data;

        if (!$mediaData) {
            return [];
        }

        // Return only essential media fields
        switch ($this->message_type) {
            case 'image':
                return [
                    'url' => $mediaData['url'] ?? null,
                    'thumbnail' => $mediaData['thumbnail_url'] ?? null,
                    'width' => $mediaData['width'] ?? null,
                    'height' => $mediaData['height'] ?? null
                ];
            
            case 'video':
                return [
                    'url' => $mediaData['url'] ?? null,
                    'thumbnail' => $mediaData['thumbnail_url'] ?? null,
                    'duration' => $mediaData['duration'] ?? null
                ];
            
            case 'voice':
            case 'audio':
                return [
                    'url' => $mediaData['url'] ?? null,
                    'duration' => $mediaData['duration'] ?? null
                ];
            
            case 'file':
                return [
                    'url' => $mediaData['url'] ?? null,
                    'name' => $mediaData['original_name'] ?? null,
                    'size' => $mediaData['size'] ?? null
                ];
            
            case 'location':
                return [
                    'latitude' => $mediaData['latitude'] ?? null,
                    'longitude' => $mediaData['longitude'] ?? null,
                    'address' => $mediaData['address'] ?? null
                ];
            
            case 'call':
                return [
                    'call_type' => $mediaData['call_type'] ?? null,
                    'duration' => $mediaData['duration'] ?? null,
                    'status' => $mediaData['call_status'] ?? null
                ];
            
            default:
                return $mediaData;
        }
    }
}