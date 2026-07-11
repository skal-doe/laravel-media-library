<?php

namespace SkalDoe\MediaLibrary\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaFolderResource extends JsonResource
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
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', fn() => $this->parent),
            'children' => $this->whenLoaded('subfolders', fn() => MediaFolderResource::collection($this->subfolders)),
            'folder_count' => $this->subfolders_count,
            'medias_count' => $this->whenCounted('medias', fn() => $this->medias_count),
            'total_items' => $this->when(
                isset($this->subfolders_count) && isset($this->medias_count),
                fn() => $this->subfolders_count + $this->medias_count
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
