<?php

namespace SkalDoe\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MediaAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'media_id',
        'mediable_type',
        'mediable_id',
        'collection_name',
    ];

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function mediable()
    {
        return $this->morphTo();
    }
}
