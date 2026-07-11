<?php

namespace SkalDoe\MediaLibrary\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use SkalDoe\MediaLibrary\Http\Resources\MediaFolderResource;
use SkalDoe\MediaLibrary\Http\Resources\MediaResource;
use SkalDoe\MediaLibrary\Models\Media;
use SkalDoe\MediaLibrary\Models\MediaFolder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $folderId = $request->query('folder_id');

        $folders = MediaFolder::query()
            ->where('parent_id', $folderId)
            ->withCount(['subfolders', 'medias'])
            ->orderBy('name')
            ->get();

        $medias = Media::query()
            ->with(['uploader', 'attachments.mediable'])
            ->when($request->query('collection'), fn($query, $collection) => $query->collection($collection))
            ->when($request->query('search'), fn($query, $search) => $query->where('file_name', 'like', "%{$search}%"))
            ->where('folder_id', $folderId)
            ->latest()
            ->paginate($request->integer('per_page', 24));

        return response()->json([
            'folders' => MediaFolderResource::collection($folders)->resolve(),
            'data' => $medias->getCollection()->map(fn($media) => (new MediaResource($media))->resolve()),
            'meta' => [
                'current_page' => $medias->currentPage(),
                'last_page' => $medias->lastPage(),
                'total' => $medias->total(),
            ],
            'breadcrumb' => $folderId ? MediaFolder::findOrFail($folderId)->breadcrumb() : [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array'],
            'folder_id' => ['nullable', 'uuid', 'exists:media_folders,id'],
        ]);

        $rules = [
            'file' => [
                'file',
                'mimes:' . config('media-library.accepted_mimes'),
                'max:' . config('media-library.max_file_size'),
            ],
        ];

        $newMedias = [];
        $failures = [];

        foreach ($request->file('files') as $file) {
            $validator = Validator::make(['file' => $file], $rules);

            if ($validator->fails()) {
                $failures[] = [
                    'file' => $file->getClientOriginalName(),
                    'errors' => $validator->errors()->get('file'),
                ];
                continue;
            }

            $fileName = str($file->getClientOriginalName())
                ->beforeLast('.')
                ->lower()
                ->slug()
                ->append('-' . now()->format('Y-m-d-H-i-s') . '-' . str()->random(6))
                ->append('.' . $file->guessExtension());

            $path = $file->storeAs('medias', $fileName, config('media-library.disk'));

            try {
                $newMedias[] = Media::create([
                    'disk' => config('media-library.disk'),
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'folder_id' => $request->filled('folder_id') ? $request->input('folder_id') : null,
                    'uploaded_by' => $request->user()->id,
                ]);
            } catch (\Throwable $e) {
                Storage::disk(config('media-library.disk'))->delete($path);

                $failures[] = [
                    'file' => $file->getClientOriginalName(),
                    'errors' => ['Erreur lors de l\'enregistrement en base de données.'],
                ];
            }
        }

        return response()->json([
            'data' => MediaResource::collection(collect($newMedias)),
            'failures' => $failures,
        ], $newMedias ? 201 : 422);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Media $media)
    {
        $request->validate([
            'folder_id' => ['nullable', 'uuid', 'exists:media_folders,id'],
        ]);

        $media->update([
            'folder_id' => $request->input('folder_id'),
        ]);

        return response()->json(new MediaResource($media));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Media $media)
    {
        if ($media->attachments()->exists()) {
            return response()->json([
                'message' => 'Ce média est actuellement utilisé et ne peut pas être supprimé.',
            ], 422);
        }

        $media->delete();

        return response()->noContent();
    }
}
