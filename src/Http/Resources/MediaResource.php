<?php

namespace SkalDoe\MediaLibrary\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
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
            'url' => $this->url,
            'name' => $this->file_name,
            'size' => $this->size,
            'mime_type' => $this->mime_type,
            'collection' => $this->collection_name,
            'usages' => $this->whenLoaded('attachments', fn() => $this->attachments
                ->filter(fn($attachment) => $attachment->mediable !== null)
                ->map(fn($attachment) => [
                    'type' => class_basename($attachment->mediable_type),
                    'id' => $attachment->mediable_id,
                    'name' => $attachment->mediable->name ?? $attachment->mediable->title ?? '',
                    'collection' => $attachment->collection_name,
                ])
                ->values()),
            'is_attached' => $this->relationLoaded('attachments')
                ? $this->attachments->isNotEmpty()
                : $this->attachments()->exists(),
            'uploaded_by' => $this->whenLoaded('uploader', fn() => [
                'id' => $this->uploader?->id,
                'name' => $this->uploader?->name,
                'avatar' => $this->uploader?->avatar,
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
