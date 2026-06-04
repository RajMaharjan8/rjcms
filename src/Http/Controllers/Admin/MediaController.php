<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Rjcodes\Rjcms\Actions\StoreMedia;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Http\Requests\Admin\MediaRequest;
use Rjcodes\Rjcms\Models\Media;

class MediaController extends Controller implements HasMiddleware
{
    /**
     * Permission gates applied per BREAD action.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:browse_medias', only: ['index']),
            new Middleware('can:view_medias', only: ['show']),
            new Middleware('can:create_medias', only: ['create', 'store', 'upload']),
            new Middleware('can:edit_medias', only: ['edit', 'update']),
            new Middleware('can:delete_medias', only: ['destroy']),
        ];
    }

    /**
     * Browse the media library.
     */
    public function index(): View
    {
        return view('rjcms::admin.media.index');
    }

    /**
     * Show the upload form.
     */
    public function create(): View
    {
        return view('rjcms::admin.media.create');
    }

    /**
     * Store an uploaded file in the media library.
     */
    public function store(MediaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        (new StoreMedia)($request->file('file'), array_filter([
            'name' => $data['name'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
        ], fn ($value) => $value !== null));

        return redirect()
            ->route('admin.media.index')
            ->with('status', 'File uploaded.');
    }

    /**
     * Store an XHR-uploaded file and return its id as JSON.
     *
     * Used by the media picker so inline uploads run through the same
     * {@see StoreMedia} path as the library form, without relying on
     * Livewire's temporary-file-upload mechanism.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
        ]);

        $media = (new StoreMedia)($request->file('file'));

        return response()->json([
            'id' => $media->id,
            'thumbnail_url' => $media->thumbnail_url,
        ]);
    }

    /**
     * Read a single media item.
     */
    public function show(Media $medium): View
    {
        $medium->load('uploader');

        return view('rjcms::admin.media.show', ['medium' => $medium]);
    }

    /**
     * Show the form to edit a media item's metadata.
     */
    public function edit(Media $medium): View
    {
        return view('rjcms::admin.media.edit', ['medium' => $medium]);
    }

    /**
     * Update a media item's metadata.
     */
    public function update(MediaRequest $request, Media $medium): RedirectResponse
    {
        $medium->update($request->validated());

        return redirect()
            ->route('admin.media.index')
            ->with('status', 'Media updated.');
    }

    /**
     * Delete a media item and its underlying file.
     */
    public function destroy(Media $medium): RedirectResponse
    {
        Storage::disk($medium->disk)->delete($medium->path);
        $medium->delete();

        return redirect()
            ->route('admin.media.index')
            ->with('status', 'Media deleted.');
    }
}
