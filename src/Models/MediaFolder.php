<?php

namespace SkalDoe\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MediaFolder extends Model
{
    use HasUuids;

    protected $table = 'media_folders';

    protected $fillable = [
        'name',
        'parent_id'
    ];

    public function parent()
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    public function subfolders()
    {
        return $this->hasMany(MediaFolder::class, 'parent_id');
    }

    public function medias()
    {
        return $this->hasMany(Media::class, 'folder_id');
    }

    public function breadcrumb(): array
    {
        $trail = [];
        $folder = $this;

        while ($folder) {
            array_unshift($trail, ['id' => $folder->id, 'name' => $folder->name]);
            $folder = $folder->parent;
        }

        return $trail;
    }
}
