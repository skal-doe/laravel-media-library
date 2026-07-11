<?php

namespace SkalDoe\MediaLibrary\Http\Controllers;

use App\Http\Controllers\Controller;
use SkalDoe\MediaLibrary\Http\Requests\MediaFolderRequest;
use SkalDoe\MediaLibrary\Http\Resources\MediaFolderResource;
use SkalDoe\MediaLibrary\Models\MediaFolder;
use Illuminate\Http\Request;

class MediaFolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $folders = MediaFolder::whereNull('parent_id')
            ->with('subfolders')
            ->withCount('medias')
            ->latest()
            ->get();

        return MediaFolderResource::collection($folders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MediaFolderRequest $request)
    {
        $folder = MediaFolder::create($request->validated());

        return response()->json($folder);
    }

    /**
     * Display the specified resource.
     */
    public function show(MediaFolder $folder)
    {
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
