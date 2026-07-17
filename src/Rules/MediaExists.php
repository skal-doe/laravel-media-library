<?php

namespace SkalDoe\MediaLibrary\Rules;

use Illuminate\Validation\Rule;

class MediaExists
{
    public static function make(): \Illuminate\Validation\Rules\Exists
    {
        return Rule::exists('media', 'id')->whereNull('deleted_at');
    }
}
