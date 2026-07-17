<?php

namespace SkalDoe\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

class Media extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'disk',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'uploaded_by',
        'folder_id'
    ];

    protected static function booted()
    {
        static::forceDeleted(function (Media $media) {
            if (Storage::disk($media->disk)->exists($media->file_path)) {
                Storage::disk($media->disk)->delete($media->file_path);
            }
        });
    }

    public function attachments()
    {
        return $this->hasMany(MediaAttachment::class);
    }

    public function uploader()
    {
        $model = config('media-library.user_model') ?? config('auth.providers.users.model');

        return $this->belongsTo($model, 'uploaded_by');
    }

    public function getUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    public function getSizeAttribute()
    {
        return $this->file_size ? Number::fileSize($this->file_size) : null;
    }

    public function scopeCollection(Builder $query, string $name)
    {
        return $query->whereHas('attachments', fn($q) => $q->where('collection_name', $name));
    }

    public function scopeUnattached(Builder $query)
    {
        return $query->whereDoesntHave('attachments');
    }
}
