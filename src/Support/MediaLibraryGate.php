<?php

namespace SkalDoe\MediaLibrary\Support;

use Illuminate\Support\Facades\Gate;

class MediaLibraryGate
{
    /**
     * Don't really authorize unless the host project has defined this ability.
     * If the ability does not exist, let it pass (permissive default behavior).
     */
    public static function check(string $ability, mixed $arguments = []): void
    {
        if (Gate::has($ability)) {
            Gate::authorize($ability, $arguments);
        }
    }
}
