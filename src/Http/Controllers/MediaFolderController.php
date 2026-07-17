<?php

namespace SkalDoe\MediaLibrary\Http\Controllers;

use App\Http\Controllers\Controller;
use SkalDoe\MediaLibrary\Http\Requests\MediaFolderRequest;
use SkalDoe\MediaLibrary\Models\MediaFolder;
use Illuminate\Http\Request;

class MediaFolderController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(MediaFolderRequest $request)
    {
        $folder = MediaFolder::create($request->validated());

        return response()->json($folder);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MediaFolderRequest $request, MediaFolder $folder)
    {
        $folder->update($request->validated());

        return response()->json($folder);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MediaFolder $folder)
    {
        $folder->delete();

        return response()->noContent();
    }
}
