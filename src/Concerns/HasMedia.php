<?php

namespace SkalDoe\MediaLibrary\Models;

use SkalDoe\MediaLibrary\Models\Media;
use SkalDoe\MediaLibrary\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasMedia
{
    public function mediaAttachments(): MorphMany
    {
        return $this->morphMany(MediaAttachment::class, 'mediable');
    }

    public function medias(): MorphToMany
    {
        // TODO: retirer pivot pour voir un truc
        return $this->morphToMany(Media::class, 'mediable', 'media_attachments')
            ->withPivot('collection_name')
            ->withTimestamps();
    }

    protected function singleMedia(string $collection)
    {
        return $this->hasOneThrough(
            Media::class,
            MediaAttachment::class,
            'mediable_id',
            'id',
            'id',
            'media_id'
        )
            ->where('media_attachments.mediable_type', static::class)
            ->where('media_attachments.collection_name', $collection)
            ->latest('created_at');
    }

    public function syncMedia(?string $mediaId, string $collection): void
    {
        $this->mediaAttachments()->where('collection_name', $collection)->delete();

        if (!$mediaId)
            return;

        $this->mediaAttachments()->create([
            'media_id' => $mediaId,
            'collection_name' => $collection,
        ]);
    }
}
