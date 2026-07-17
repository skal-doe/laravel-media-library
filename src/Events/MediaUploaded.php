<?php

namespace SkalDoe\MediaLibrary\Events;

use Illuminate\Foundation\Events\Dispatchable;
use SkalDoe\MediaLibrary\Models\Media;

class MediaUploaded
{
    use Dispatchable;

    public function __construct(public Media $media) {}
}
